<?php
/**
 * Level 2 – Cookies
 * Cookies are small files stored in the browser.
 * They persist across browser sessions (unlike $_SESSION).
 *
 * Run: php -S localhost:8002
 */

declare(strict_types=1);

/* ── Cookie Constants ─────────────────────────────────────────────────── */
const COOKIE_THEME    = 'user_theme';
const COOKIE_LANG     = 'user_lang';
const COOKIE_RECENT   = 'recent_pages';
const COOKIE_LIFETIME = 30 * 24 * 3600;   // 30 days

/* ── Actions ─────────────────────────────────────────────────────────── */
$action = $_GET['action'] ?? '';

if ($action === 'save') {
    $theme = in_array($_POST['theme'] ?? '', ['light','dark','blue'])
        ? $_POST['theme'] : 'light';
    $lang  = in_array($_POST['lang'] ?? '', ['en','bn','ar'])
        ? $_POST['lang'] : 'en';

    setcookie(COOKIE_THEME, $theme, time() + COOKIE_LIFETIME, '/');
    setcookie(COOKIE_LANG,  $lang,  time() + COOKIE_LIFETIME, '/');

    header('Location: ' . $_SERVER['PHP_SELF'] . '?saved=1');
    exit;
}

if ($action === 'clear') {
    setcookie(COOKIE_THEME,  '', time() - 3600, '/');
    setcookie(COOKIE_LANG,   '', time() - 3600, '/');
    setcookie(COOKIE_RECENT, '', time() - 3600, '/');
    header('Location: ' . $_SERVER['PHP_SELF'] . '?cleared=1');
    exit;
}

// Track recent pages visited (stored as JSON in cookie)
$recentRaw   = $_COOKIE[COOKIE_RECENT] ?? '[]';
$recentPages = json_decode($recentRaw, true) ?? [];
$currentPage = basename($_SERVER['PHP_SELF']) . '?' . ($_SERVER['QUERY_STRING'] ?? '');

if (!in_array($currentPage, $recentPages, true)) {
    array_unshift($recentPages, $currentPage);
    $recentPages = array_slice($recentPages, 0, 5);   // keep last 5
    setcookie(COOKIE_RECENT, json_encode($recentPages), time() + COOKIE_LIFETIME, '/');
}

/* ── Current Values ───────────────────────────────────────────────────── */
$theme    = $_COOKIE[COOKIE_THEME] ?? 'light';
$lang     = $_COOKIE[COOKIE_LANG]  ?? 'en';
$saved    = isset($_GET['saved']);
$cleared  = isset($_GET['cleared']);

$themeStyles = [
    'light' => ['bg' => '#f0f4f8', 'card' => '#fff',      'text' => '#2d3748'],
    'dark'  => ['bg' => '#1a202c', 'card' => '#2d3748',   'text' => '#e2e8f0'],
    'blue'  => ['bg' => '#ebf4ff', 'card' => '#ebf8ff',   'text' => '#2b6cb0'],
];
$css = $themeStyles[$theme];

$langLabels = ['en' => 'English 🇬🇧', 'bn' => 'বাংলা 🇧🇩', 'ar' => 'العربية 🇸🇦'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cookies – Level 2</title>
    <style>
        body  { font-family: 'Segoe UI', sans-serif; max-width: 640px; margin: 40px auto; padding: 0 20px;
                background:<?= $css['bg'] ?>; color:<?= $css['text'] ?>; }
        .card { background:<?= $css['card'] ?>; border-radius:12px; padding:28px; margin-bottom:20px;
                box-shadow:0 4px 20px rgba(0,0,0,.08); }
        h1    { color:<?= $css['text'] ?>; }
        select, button, a.btn { padding:9px 16px; border-radius:8px; font-size:14px; cursor:pointer; }
        select { border:2px solid #e2e8f0; margin-right:10px; }
        .btn-primary { background:#667eea; color:#fff; border:none; margin-right:8px; text-decoration:none; }
        .btn-danger  { background:#e53e3e; color:#fff; border:none; text-decoration:none; }
        .alert { padding:12px; border-radius:8px; margin-bottom:16px; }
        .alert-success { background:#c6f6d5; color:#276749; }
        code { background:rgba(0,0,0,.1); padding:2px 6px; border-radius:4px; font-size:13px; }
        ol li { padding:4px 0; }
    </style>
</head>
<body>
<h1>🍪 PHP Cookies Demo</h1>

<?php if ($saved): ?>
    <div class="alert alert-success">✅ Preferences saved to cookies!</div>
<?php endif; ?>
<?php if ($cleared): ?>
    <div class="alert" style="background:#bee3f8;color:#2a4365;">🗑️ All cookies cleared!</div>
<?php endif; ?>

<div class="card">
    <h2>🎨 User Preferences</h2>
    <p>Current theme: <strong><?= ucfirst($theme) ?></strong> | Language: <strong><?= $langLabels[$lang] ?></strong></p>

    <form method="POST" action="?action=save" style="display:flex;align-items:center;flex-wrap:wrap;gap:10px;">
        <select name="theme">
            <?php foreach (['light','dark','blue'] as $t): ?>
                <option value="<?= $t ?>" <?= $theme === $t ? 'selected' : '' ?>><?= ucfirst($t) ?> Theme</option>
            <?php endforeach; ?>
        </select>
        <select name="lang">
            <?php foreach ($langLabels as $code => $label): ?>
                <option value="<?= $code ?>" <?= $lang === $code ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-primary">Save Preferences</button>
    </form>
</div>

<div class="card">
    <h2>📄 Recent Pages (Cookie)</h2>
    <?php if (empty($recentPages)): ?>
        <p>No pages visited yet.</p>
    <?php else: ?>
        <ol><?php foreach ($recentPages as $page): ?><li><code><?= htmlspecialchars($page) ?></code></li><?php endforeach; ?></ol>
    <?php endif; ?>
</div>

<div class="card">
    <h2>🍪 Raw Cookie Data</h2>
    <pre><?= htmlspecialchars(print_r($_COOKIE, true)) ?></pre>
    <a href="?action=clear" class="btn btn-danger">Clear All Cookies</a>
</div>
</body>
</html>
