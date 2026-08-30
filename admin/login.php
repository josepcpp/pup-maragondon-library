<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Already logged in
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$warning = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the username first: the rate limit is scoped to it, so it has to
    // be known before the check runs.
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!csrf_verify()) {
        $error = 'Invalid request. Please try again.';
    } elseif ($username === '') {
        // Nothing to rate-limit against, and a blank name can never match.
        $error = 'Invalid username or password.';
    } elseif (login_is_locked($attempt = login_register_attempt($username))) {
        $remaining = max(1, (int)ceil(lockout_seconds_remaining($username) / 60));
        $error = "Too many failed attempts. Please wait {$remaining} minute(s) before trying again.";
    } else {
        $cred = cms_get_credentials();

        $valid_user = hash_equals($cred['username'], $username);
        $valid_pass = password_verify($password, $cred['password_hash']);

        if ($valid_user && $valid_pass) {
            reset_login_attempts($username);
            session_regenerate_id(true);
            $_SESSION['admin_logged_in']  = true;
            $_SESSION['admin_user']       = $username;
            $_SESSION['admin_last_active'] = time();
            $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        } else {
            $left  = max(0, MAX_LOGIN_ATTEMPTS - $attempt);
            $error = 'Invalid username or password.'
                   . ($left > 0 ? " {$left} attempt(s) remaining." : ' This account is now temporarily locked.');
        }
    }
}

$token = csrf_token();
$flash_error = flash_get('error');
if ($flash_error) $error = $flash_error;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — PUP Maragondon Library</title>
  <link rel="icon" type="image/png" href="../assets/images/logo.png">
  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&amp;family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="login-page" style="display:flex;">
<div class="login-card">
  <div class="login-header">
    <div class="login-logo"><i class="fa-solid fa-book-open"></i></div>
    <h1>Library CMS</h1>
    <p>PUP Maragondon Campus</p>
  </div>

  <div class="login-body">
    <p style="text-align:center;font-size:0.85rem;color:var(--ink-light);margin-bottom:24px;margin-top:12px;">
      Sign in to manage library content
    </p>

    <?php if ($error): ?>
      <div class="flash flash-error" style="margin-bottom:18px;">
        <i class="fa-solid fa-circle-xmark"></i><?= h($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php" autocomplete="on">
      <input type="hidden" name="csrf_token" value="<?= h($token) ?>">

      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <div style="position:relative;">
          <i class="fa-solid fa-user" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--ink-light);font-size:0.8rem;"></i>
          <input type="text" id="username" name="username" class="form-control"
                 style="padding-left:36px;"
                 value="<?= h($_POST['username'] ?? '') ?>"
                 placeholder="admin" autocomplete="username" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div style="position:relative;">
          <i class="fa-solid fa-lock" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--ink-light);font-size:0.8rem;"></i>
          <input type="password" id="password" name="password" class="form-control"
                 style="padding-left:36px;padding-right:42px;"
                 placeholder="••••••••" autocomplete="current-password" required>
          <button type="button" id="toggle-pw" tabindex="-1"
                  style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--ink-light);font-size:0.8rem;padding:4px;">
            <i class="fa-solid fa-eye"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:11px;">
        <i class="fa-solid fa-right-to-bracket"></i> Sign In
      </button>
    </form>

    <p style="text-align:center;margin-top:20px;font-size:0.74rem;color:var(--ink-light);">
      <i class="fa-solid fa-shield-halved" style="color:var(--maroon);"></i>
      Change your password in Settings after first login.
    </p>
  </div>
</div>

<script>
document.getElementById('toggle-pw').addEventListener('click', function() {
  const pw = document.getElementById('password');
  const icon = this.querySelector('i');
  if (pw.type === 'password') {
    pw.type = 'text';
    icon.className = 'fa-solid fa-eye-slash';
  } else {
    pw.type = 'password';
    icon.className = 'fa-solid fa-eye';
  }
});
</script>
</body>
</html>
