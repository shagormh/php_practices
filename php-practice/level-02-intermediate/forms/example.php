<?php
/**
 * Level 2 – Forms
 * Handles both GET (display form) and POST (process data).
 * PHP 8+ features: match, named args, str_contains
 *
 * Run via PHP dev server: php -S localhost:8000
 * then visit: http://localhost:8000/example.php
 */

declare(strict_types=1);

/* ── Validation Helpers ────────────────────────────────────────────────── */
function sanitize(string $input): string
{
    return htmlspecialchars(strip_tags(trim($input)));
}

function validate(array $data): array
{
    $errors = [];
    if (empty($data['name']) || strlen($data['name']) < 2) {
        $errors['name'] = "Name must be at least 2 characters.";
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email address.";
    }
    if (strlen($data['message']) < 10) {
        $errors['message'] = "Message must be at least 10 characters.";
    }
    return $errors;
}

/* ── Handle POST ──────────────────────────────────────────────────────── */
$success = false;
$errors  = [];
$old     = [];   // repopulate form on error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = array_map('sanitize', [
        'name'    => $_POST['name']    ?? '',
        'email'   => $_POST['email']   ?? '',
        'subject' => $_POST['subject'] ?? '',
        'message' => $_POST['message'] ?? '',
    ]);

    $errors = validate($old);

    if (empty($errors)) {
        // In real app: send email or save to DB
        $success = true;
    }
}

/* ── HTML ─────────────────────────────────────────────────────────────── */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Form – Level 2 Forms</title>
    <style>
        body      { font-family: 'Segoe UI', sans-serif; max-width: 600px; margin: 40px auto; padding: 0 20px; background:#f4f5f7; }
        h1        { color:#2d3748; }
        .card     { background:#fff; border-radius:12px; padding:30px; box-shadow:0 4px 20px rgba(0,0,0,.08); }
        label     { display:block; font-weight:600; margin-bottom:4px; color:#4a5568; }
        input,textarea,select { width:100%; padding:10px 14px; border:2px solid #e2e8f0; border-radius:8px;
                    font-size:14px; box-sizing:border-box; transition:border .2s; }
        input:focus,textarea:focus,select:focus { border-color:#667eea; outline:none; }
        .field    { margin-bottom:18px; }
        .error    { color:#e53e3e; font-size:13px; margin-top:4px; }
        .success  { background:#c6f6d5; border:1px solid #68d391; padding:16px; border-radius:8px; color:#276749; }
        button    { background:linear-gradient(135deg,#667eea,#764ba2); color:#fff; border:none;
                    padding:12px 28px; border-radius:8px; font-size:16px; cursor:pointer; width:100%; }
        button:hover { opacity:.9; }
        .field-err input, .field-err textarea { border-color:#e53e3e; }
    </style>
</head>
<body>
<h1>📬 Contact Form</h1>
<div class="card">

<?php if ($success): ?>
    <div class="success">
        <strong>✅ Message sent!</strong><br>
        Thank you <strong><?= $old['name'] ?></strong>, we'll reply to <strong><?= $old['email'] ?></strong> soon.
    </div>
<?php else: ?>

<form method="POST" action="">

    <div class="field <?= isset($errors['name']) ? 'field-err' : '' ?>">
        <label for="name">Full Name *</label>
        <input type="text" id="name" name="name" value="<?= $old['name'] ?? '' ?>" placeholder="Alice Rahman">
        <?php if (isset($errors['name'])): ?><p class="error"><?= $errors['name'] ?></p><?php endif; ?>
    </div>

    <div class="field <?= isset($errors['email']) ? 'field-err' : '' ?>">
        <label for="email">Email Address *</label>
        <input type="email" id="email" name="email" value="<?= $old['email'] ?? '' ?>" placeholder="alice@example.com">
        <?php if (isset($errors['email'])): ?><p class="error"><?= $errors['email'] ?></p><?php endif; ?>
    </div>

    <div class="field">
        <label for="subject">Subject</label>
        <select id="subject" name="subject">
            <?php foreach (['General Inquiry','Technical Support','Billing','Feedback'] as $opt): ?>
                <option value="<?= $opt ?>" <?= ($old['subject'] ?? '') === $opt ? 'selected' : '' ?>>
                    <?= $opt ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field <?= isset($errors['message']) ? 'field-err' : '' ?>">
        <label for="message">Message *</label>
        <textarea id="message" name="message" rows="5" placeholder="Your message..."><?= $old['message'] ?? '' ?></textarea>
        <?php if (isset($errors['message'])): ?><p class="error"><?= $errors['message'] ?></p><?php endif; ?>
    </div>

    <button type="submit">Send Message 🚀</button>
</form>

<?php endif; ?>
</div>
</body>
</html>
