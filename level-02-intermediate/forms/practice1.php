<?php
/**
 * Level 2 – Forms Practice: Multi-Step Registration Form
 * Run: php -S localhost:8000 and visit /practice1.php
 */

declare(strict_types=1);
session_start();

// Step 1: Personal Info (name, email, phone)
// Step 2: Account Info (username, password, confirm)
// Step 3: Review & Submit

$step   = (int) ($_POST['step'] ?? $_SESSION['reg_step'] ?? 1);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save step data to session
    if ($step === 1) {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        if (strlen($name) < 2) $errors['name'] = "Name too short";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email";
        if (!preg_match('/^01[3-9]\d{8}$/', $phone)) $errors['phone'] = "Invalid BD phone (e.g. 01712345678)";
        if (empty($errors)) {
            $_SESSION['reg']['personal'] = compact('name','email','phone');
            $_SESSION['reg_step'] = 2;
            $step = 2;
        }
    } elseif ($step === 2) {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm']  ?? '';
        if (strlen($username) < 4) $errors['username'] = "Min 4 characters";
        if (strlen($password) < 8) $errors['password'] = "Min 8 characters";
        if ($password !== $confirm)  $errors['confirm']  = "Passwords don't match";
        if (empty($errors)) {
            $_SESSION['reg']['account'] = [
                'username' => $username,
                'password' => password_hash($password, PASSWORD_BCRYPT),
            ];
            $_SESSION['reg_step'] = 3;
            $step = 3;
        }
    } elseif ($step === 3) {
        // Final submit
        $data = $_SESSION['reg'] ?? [];
        // In real app: INSERT into database here
        session_unset();
        echo "<h2 style='font-family:sans-serif;color:green;text-align:center'>✅ Registration Complete!</h2>";
        echo "<pre style='margin:20px auto;max-width:400px'>" . print_r($data['personal'], true) . "</pre>";
        exit;
    }
}

$stepTitles = [1 => "Personal Info", 2 => "Account Setup", 3 => "Review & Submit"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Multi-Step Registration</title>
    <style>
        body   { font-family:'Segoe UI',sans-serif; background:#f0f4f8; display:flex; justify-content:center; padding:40px 20px; }
        .wrap  { width:100%; max-width:520px; }
        .steps { display:flex; margin-bottom:24px; gap:8px; }
        .step  { flex:1; padding:10px; text-align:center; border-radius:8px; font-size:13px; font-weight:600;
                 background:#e2e8f0; color:#718096; }
        .step.active { background:#667eea; color:#fff; }
        .step.done   { background:#38a169; color:#fff; }
        .card  { background:#fff; border-radius:12px; padding:28px; box-shadow:0 4px 20px rgba(0,0,0,.08); }
        label  { display:block; font-weight:600; margin-bottom:4px; color:#4a5568; margin-top:14px; }
        input  { width:100%; padding:10px 14px; border:2px solid #e2e8f0; border-radius:8px; box-sizing:border-box; font-size:14px; }
        .err   { color:#e53e3e; font-size:13px; margin-top:3px; }
        button { margin-top:20px; width:100%; padding:12px; background:#667eea; color:#fff; border:none; border-radius:8px; font-size:16px; cursor:pointer; }
        table  { width:100%; border-collapse:collapse; margin-bottom:16px; }
        td,th  { padding:10px 14px; border-bottom:1px solid #e2e8f0; }
        th     { text-align:left; color:#4a5568; background:#f7fafc; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="steps">
        <?php for ($i = 1; $i <= 3; $i++): ?>
            <div class="step <?= $i === $step ? 'active' : ($i < $step ? 'done' : '') ?>">
                <?= $i < $step ? '✓ ' : '' ?><?= $stepTitles[$i] ?>
            </div>
        <?php endfor; ?>
    </div>
    <div class="card">
        <h2 style="margin-top:0;color:#2d3748">Step <?= $step ?>: <?= $stepTitles[$step] ?></h2>
        <form method="POST">
            <input type="hidden" name="step" value="<?= $step ?>">
            <?php if ($step === 1): ?>
                <label>Full Name</label>
                <input name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                <?php if (isset($errors['name'])): ?><p class="err"><?= $errors['name'] ?></p><?php endif; ?>
                <label>Email</label>
                <input name="email" type="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <?php if (isset($errors['email'])): ?><p class="err"><?= $errors['email'] ?></p><?php endif; ?>
                <label>Phone (BD)</label>
                <input name="phone" placeholder="01712345678" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                <?php if (isset($errors['phone'])): ?><p class="err"><?= $errors['phone'] ?></p><?php endif; ?>
            <?php elseif ($step === 2): ?>
                <label>Username</label>
                <input name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                <?php if (isset($errors['username'])): ?><p class="err"><?= $errors['username'] ?></p><?php endif; ?>
                <label>Password</label>
                <input name="password" type="password">
                <?php if (isset($errors['password'])): ?><p class="err"><?= $errors['password'] ?></p><?php endif; ?>
                <label>Confirm Password</label>
                <input name="confirm" type="password">
                <?php if (isset($errors['confirm'])): ?><p class="err"><?= $errors['confirm'] ?></p><?php endif; ?>
            <?php elseif ($step === 3): ?>
                <p style="color:#4a5568;">Please review your details before submitting.</p>
                <table>
                    <?php $p = $_SESSION['reg']['personal'] ?? []; ?>
                    <tr><th>Name</th><td><?= htmlspecialchars($p['name']  ?? '') ?></td></tr>
                    <tr><th>Email</th><td><?= htmlspecialchars($p['email'] ?? '') ?></td></tr>
                    <tr><th>Phone</th><td><?= htmlspecialchars($p['phone'] ?? '') ?></td></tr>
                    <tr><th>Username</th><td><?= htmlspecialchars($_SESSION['reg']['account']['username'] ?? '') ?></td></tr>
                    <tr><th>Password</th><td>•••••••••</td></tr>
                </table>
            <?php endif; ?>
            <button type="submit"><?= $step === 3 ? '✅ Submit Registration' : 'Next Step →' ?></button>
        </form>
    </div>
</div>
</body>
</html>
