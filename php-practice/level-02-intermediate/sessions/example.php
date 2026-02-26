<?php
/**
 * Level 2 – Sessions
 * PHP sessions allow data to persist between page requests.
 * Session data is stored server-side; a session ID cookie is sent to the browser.
 *
 * Run: php -S localhost:8001
 */

declare(strict_types=1);

session_start();

/* ── Flash Messages ───────────────────────────────────────────────────── */
function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

/* ── Actions ─────────────────────────────────────────────────────────── */
$action = $_GET['action'] ?? '';

match($action) {
    'login' => (function () {
        $username = htmlspecialchars($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Hardcoded credentials for demo (use DB + hashing in production!)
        if ($username === 'admin' && $password === 'secret123') {
            $_SESSION['user'] = [
                'id'       => 1,
                'username' => $username,
                'role'     => 'admin',
                'login_at' => date('Y-m-d H:i:s'),
            ];
            $_SESSION['visit_count'] = 0;
            flash('success', "Welcome back, {$username}! 🎉");
        } else {
            flash('error', "Invalid credentials. Try admin / secret123");
        }
    })(),

    'logout' => (function () {
        session_unset();
        session_destroy();
        flash('info', "You have been logged out.");
        // Re-start to store flash
        session_start();
    })(),

    'visit' => (function () {
        if (isset($_SESSION['user'])) {
            $_SESSION['visit_count'] = ($_SESSION['visit_count'] ?? 0) + 1;
        }
    })(),

    default => null,
};

$isLoggedIn  = isset($_SESSION['user']);
$user        = $_SESSION['user'] ?? null;
$visitCount  = $_SESSION['visit_count'] ?? 0;
$sessionId   = session_id();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sessions – Level 2</title>
    <style>
        body  { font-family: 'Segoe UI', sans-serif; max-width: 640px; margin: 40px auto; padding: 0 20px; background:#f0f4f8; }
        .card { background:#fff; border-radius:12px; padding:28px; box-shadow:0 4px 20px rgba(0,0,0,.08); margin-bottom:20px; }
        h1    { color:#2d3748; }
        input { padding:9px 12px; border:2px solid #e2e8f0; border-radius:6px; width:100%; box-sizing:border-box; margin-bottom:12px; }
        button, a.btn { display:inline-block; padding:10px 22px; border:none; border-radius:8px; font-size:14px;
                        cursor:pointer; text-decoration:none; margin-right:8px; }
        .btn-primary  { background:#667eea; color:#fff; }
        .btn-danger   { background:#e53e3e; color:#fff; }
        .btn-success  { background:#38a169; color:#fff; }
        .flash-success{ background:#c6f6d5; color:#276749; padding:12px; border-radius:8px; margin-bottom:16px; }
        .flash-error  { background:#fed7d7; color:#742a2a; padding:12px; border-radius:8px; margin-bottom:16px; }
        .flash-info   { background:#bee3f8; color:#2a4365; padding:12px; border-radius:8px; margin-bottom:16px; }
        table { width:100%; border-collapse:collapse; }
        td,th { padding:10px 14px; border-bottom:1px solid #e2e8f0; text-align:left; }
        th    { font-weight:600; color:#4a5568; background:#f7fafc; }
        code  { background:#edf2f7; padding:2px 6px; border-radius:4px; font-size:13px; }
    </style>
</head>
<body>
<h1>🔐 PHP Sessions Demo</h1>

<?php foreach (['success','error','info'] as $type): ?>
    <?php if ($msg = flash($type)): ?>
        <div class="flash-<?= $type ?>"><?= $msg ?></div>
    <?php endif; ?>
<?php endforeach; ?>

<?php if (!$isLoggedIn): ?>
<div class="card">
    <h2>Login</h2>
    <form method="POST" action="?action=login">
        <input type="text"     name="username" placeholder="Username (try: admin)">
        <input type="password" name="password" placeholder="Password (try: secret123)">
        <button type="submit" class="btn-primary">Login</button>
    </form>
</div>

<?php else: ?>
<div class="card">
    <h2>👤 Session Data</h2>
    <table>
        <tr><th>Key</th><th>Value</th></tr>
        <tr><td>Session ID</td><td><code><?= substr($sessionId, 0, 16) ?>...</code></td></tr>
        <tr><td>Username</td><td><?= $user['username'] ?></td></tr>
        <tr><td>Role</td><td><?= $user['role'] ?></td></tr>
        <tr><td>Login At</td><td><?= $user['login_at'] ?></td></tr>
        <tr><td>Page Visits</td><td><?= $visitCount ?></td></tr>
    </table>
    <br>
    <a href="?action=visit" class="btn btn-success">Track Visit (+1)</a>
    <a href="?action=logout" class="btn btn-danger">Logout</a>
</div>
<?php endif; ?>

<div class="card">
    <h3>📌 Raw <code>$_SESSION</code></h3>
    <pre><?= htmlspecialchars(print_r($_SESSION, true)) ?></pre>
</div>
</body>
</html>
