<?php
require_once dirname(__DIR__) . '/includes/auth.php';
start_secure_session();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid session token. Please try again.';
    } elseif (attempt_login(clean_string($_POST['username'] ?? '', 80), (string) ($_POST['password'] ?? ''))) {
        redirect_to('dashboard.php');
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login | TeamSource</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <main class="login-wrap">
    <section class="card login-card">
      <h1>TeamSource Admin</h1>
      <p>Sign in to manage scholarship applications.</p>
      <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Username<input type="text" name="username" autocomplete="username" required></label>
        <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
        <button type="submit">Login</button>
      </form>
    </section>
  </main>
</body>
</html>
