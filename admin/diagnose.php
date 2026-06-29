<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

// Try to write a session value to test persistence
start_secure_session();

$session_ok = false;
if (isset($_GET['step']) && $_GET['step'] === '2') {
    if (($_SESSION['test_val'] ?? '') === 'working') {
        $session_ok = true;
    }
} else {
    $_SESSION['test_val'] = 'working';
}

$save_path = ini_get('session.save_path');
if (empty($save_path)) {
    $save_path = sys_get_temp_dir() . ' (PHP default temp dir)';
}

$is_writable = is_writable(empty(ini_get('session.save_path')) ? sys_get_temp_dir() : ini_get('session.save_path'));
$https_detected = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Session Diagnostics</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #1e293b; padding: 2rem; }
        .card { background: white; border-radius: 8px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); padding: 2rem; max-width: 600px; margin: 0 auto; }
        h1 { font-size: 1.5rem; margin-top: 0; color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 1rem; }
        .status { padding: 1rem; border-radius: 6px; margin: 1rem 0; font-weight: 600; }
        .status.success { background: #dcfce7; color: #15803d; }
        .status.error { background: #fee2e2; color: #b91c1c; }
        .status.warning { background: #fef9c3; color: #a16207; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { text-align: left; padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0; }
        th { color: #64748b; font-weight: 500; }
        a.btn { display: inline-block; background: #2563eb; color: white; padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none; font-weight: 500; margin-top: 1rem; }
        a.btn:hover { background: #1d4ed8; }
        pre { background: #f1f5f9; padding: 1rem; border-radius: 4px; overflow-x: auto; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>PHP Session Diagnostics</h1>
        
        <?php if (!isset($_GET['step'])): ?>
            <div class="status warning">
                Step 1: Session value has been set. Click below to verify if it persists on redirect.
            </div>
            <a href="?step=2" class="btn">Test Session Persistence</a>
        <?php else: ?>
            <?php if ($session_ok): ?>
                <div class="status success">
                    ✔ Success: Sessions are persisting correctly!
                </div>
            <?php else: ?>
                <div class="status error">
                    ✘ Error: Session data was lost! The server cannot persist $_SESSION values across requests.
                </div>
            <?php endif; ?>
            <a href="diagnose.php" class="btn">Restart Test</a>
        <?php endif; ?>

        <table>
            <tr>
                <th>PHP Version</th>
                <td><?= phpversion() ?></td>
            </tr>
            <tr>
                <th>Session Save Path</th>
                <td><code><?= htmlspecialchars($save_path) ?></code></td>
            </tr>
            <tr>
                <th>Save Path Writable?</th>
                <td><?= $is_writable ? '✅ Yes' : '❌ No (This will break sessions!)' ?></td>
            </tr>
            <tr>
                <th>HTTPS Detected?</th>
                <td><?= $https_detected ? '✅ Yes' : '❌ No' ?></td>
            </tr>
            <tr>
                <th>$_SERVER['HTTPS']</th>
                <td><code><?= htmlspecialchars($_SERVER['HTTPS'] ?? 'not set') ?></code></td>
            </tr>
            <tr>
                <th>$_SERVER['HTTP_X_FORWARDED_PROTO']</th>
                <td><code><?= htmlspecialchars($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'not set') ?></code></td>
            </tr>
            <tr>
                <th>Cookie Settings (Secure flag)</th>
                <td><?= ini_get('session.cookie_secure') ? 'Secure only' : 'Flexible' ?></td>
            </tr>
        </table>

        <h3>Active Session Cookie Parameters:</h3>
        <pre><?= print_r(session_get_cookie_params(), true) ?></pre>
    </div>
</body>
</html>
