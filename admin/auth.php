<?php
// Admin authentication guard — include at top of every admin page
require_once __DIR__ . '/config.php';

// Secure session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
// Enable secure flag when served over HTTPS
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', 1);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session timeout check
if (isset($_SESSION['admin_last_active'])) {
    if (time() - $_SESSION['admin_last_active'] > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['flash_error'] = 'Session expired. Please log in again.';
        header('Location: ' . admin_url('login.php'));
        exit;
    }
}
if (isset($_SESSION['admin_logged_in'])) {
    $_SESSION['admin_last_active'] = time();
}

// Require login
function require_auth(): void {
    if (empty($_SESSION['admin_logged_in'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . admin_url('login.php'));
        exit;
    }
}

// Build admin URL helper
function admin_url(string $page = ''): string {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    // If called from a subdirectory (api/), go up one level
    if (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'api') {
        $base = dirname($base);
    }
    return $base . '/' . ltrim($page, '/');
}

/**
 * Save content and report the real outcome.
 *
 * cms_save() can legitimately fail — unencodable content, a full disk, a
 * permissions problem. Call sites used to ignore the return value and flash
 * "saved" regardless, so a failed write looked identical to a successful one.
 */
function cms_save_flash(string $file, array $data, string $success_msg): bool {
    if (!cms_save_checked($file, $data)) return false;
    flash_set('success', $success_msg);
    return true;
}

/**
 * Same, for the add/update branches that decide their success wording before
 * the save runs. On failure the queued "added"/"updated" message is retracted,
 * so the user never sees a success and an error side by side.
 */
function cms_save_checked(string $file, array $data): bool {
    if (cms_save($file, $data)) return true;
    unset($_SESSION['flash_success']);
    flash_set('error', 'Could not save — your content was left unchanged. Check the server error log for details.');
    return false;
}

// Flash message helpers
function flash_set(string $key, string $msg): void {
    $_SESSION['flash_' . $key] = $msg;
}
function flash_get(string $key): string {
    $msg = $_SESSION['flash_' . $key] ?? '';
    unset($_SESSION['flash_' . $key]);
    return $msg;
}

// ── Login rate limiting ───────────────────────────────────────────
// Counters live in data/login-attempts.json, keyed by client address and
// attempted username. The session is kept as a second layer only; on its
// own it stops nothing, because dropping the cookie starts a fresh count.

/**
 * The client's address. A forwarded header is only believed when the direct
 * peer is a configured proxy — otherwise anyone could set the header and get
 * a fresh bucket per request.
 */
function client_ip(): string {
    $peer = $_SERVER['REMOTE_ADDR'] ?? '';

    if (TRUSTED_PROXIES && in_array($peer, TRUSTED_PROXIES, true)) {
        $fwd = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        foreach (explode(',', $fwd) as $hop) {
            $hop = trim($hop);
            if (filter_var($hop, FILTER_VALIDATE_IP)) return $hop;
        }
    }

    return filter_var($peer, FILTER_VALIDATE_IP) ? $peer : '0.0.0.0';
}

/** Opaque bucket id. The username is hashed rather than stored in the clear. */
function rate_limit_key(string $username): string {
    return substr(hash('sha256', client_ip() . '|' . strtolower(trim($username))), 0, 32);
}

/**
 * Read-modify-write the attempt store under an exclusive lock, so two
 * simultaneous failures cannot both read "2" and both write "3".
 * $mutate receives the store by reference and may return a value.
 */
function attempts_transaction(callable $mutate) {
    $lock = ATTEMPTS_FILE . '.lock';
    $fh   = fopen($lock, 'c');
    if (!$fh) return null;

    flock($fh, LOCK_EX);

    $store = json_decode((string)@file_get_contents(ATTEMPTS_FILE), true);
    if (!is_array($store)) $store = [];

    $before = $store;
    $result = $mutate($store);

    if ($store !== $before) {
        // Drop buckets that can no longer lock anyone out, then cap the file.
        $cutoff = time() - LOCKOUT_TIME;
        $store  = array_filter($store, fn($e) => ($e['last_fail'] ?? 0) > $cutoff);
        if (count($store) > ATTEMPTS_MAX_KEYS) {
            uasort($store, fn($a, $b) => ($b['last_fail'] ?? 0) <=> ($a['last_fail'] ?? 0));
            $store = array_slice($store, 0, ATTEMPTS_MAX_KEYS, true);
        }

        $json = json_encode($store, JSON_PRETTY_PRINT);
        if ($json !== false) {
            $tmp = tempnam(DATA_PATH, 'tmp');
            if ($tmp !== false) {
                if (file_put_contents($tmp, $json) !== false) {
                    @chmod($tmp, 0644);
                    if (!rename($tmp, ATTEMPTS_FILE)) @unlink($tmp);
                } else {
                    @unlink($tmp);
                }
            }
        }
    }

    flock($fh, LOCK_UN);
    fclose($fh);
    return $result;
}

function attempts_entry(string $username): array {
    $key   = rate_limit_key($username);
    $store = json_decode((string)@file_get_contents(ATTEMPTS_FILE), true);
    $e     = is_array($store) ? ($store[$key] ?? null) : null;
    return is_array($e) ? $e + ['count' => 0, 'last_fail' => 0] : ['count' => 0, 'last_fail' => 0];
}

/**
 * Count this attempt and return the new total, atomically.
 *
 * Counting up front rather than checking first and recording after removes a
 * race: parallel requests could otherwise all read the same under-limit count
 * and all proceed, buying an attacker far more tries than the limit allows.
 * A successful login clears the bucket, so counting a good attempt costs the
 * real user nothing.
 */
function login_register_attempt(string $username): int {
    $key = rate_limit_key($username);
    $n = attempts_transaction(function (array &$store) use ($key) {
        $e     = $store[$key] ?? ['count' => 0, 'last_fail' => 0];
        $stale = (time() - ($e['last_fail'] ?? 0)) >= LOCKOUT_TIME;
        $count = ($stale ? 0 : (int)($e['count'] ?? 0)) + 1;
        $store[$key] = ['count' => $count, 'last_fail' => time()];
        return $count;
    });
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;

    // If the store could not be written, fail closed rather than open.
    return $n === null ? PHP_INT_MAX : (int)$n;
}

function login_is_locked(int $count): bool {
    return $count > MAX_LOGIN_ATTEMPTS;
}

function reset_login_attempts(string $username = ''): void {
    $key = rate_limit_key($username);
    attempts_transaction(function (array &$store) use ($key) {
        unset($store[$key]);
    });
    $_SESSION['login_attempts'] = 0;
}

function lockout_seconds_remaining(string $username = ''): int {
    $e = attempts_entry($username);
    if (($e['count'] ?? 0) < MAX_LOGIN_ATTEMPTS) return 0;
    return max(0, LOCKOUT_TIME - (time() - (int)($e['last_fail'] ?? 0)));
}

