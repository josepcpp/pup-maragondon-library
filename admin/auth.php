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

// Flash message helpers
function flash_set(string $key, string $msg): void {
    $_SESSION['flash_' . $key] = $msg;
}
function flash_get(string $key): string {
    $msg = $_SESSION['flash_' . $key] ?? '';
    unset($_SESSION['flash_' . $key]);
    return $msg;
}

// Rate limiting for login
function check_rate_limit(): bool {
    $attempts  = $_SESSION['login_attempts']  ?? 0;
    $last_fail = $_SESSION['login_last_fail'] ?? 0;
    if ($attempts >= MAX_LOGIN_ATTEMPTS) {
        if (time() - $last_fail < LOCKOUT_TIME) {
            return false; // Still locked out
        }
        // Lockout expired, reset
        $_SESSION['login_attempts']  = 0;
        $_SESSION['login_last_fail'] = 0;
    }
    return true;
}
function record_failed_login(): void {
    $_SESSION['login_attempts']  = ($_SESSION['login_attempts'] ?? 0) + 1;
    $_SESSION['login_last_fail'] = time();
}
function reset_login_attempts(): void {
    $_SESSION['login_attempts']  = 0;
    $_SESSION['login_last_fail'] = 0;
}
function lockout_seconds_remaining(): int {
    $last = $_SESSION['login_last_fail'] ?? 0;
    return max(0, LOCKOUT_TIME - time() + $last);
}
