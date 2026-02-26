<?php
/**
 * Mini Project – Personal Bio Generator (Level 1)
 * Combines: variables, strings, arrays, type casting, constants
 *
 * Run: php mini-project.php
 */

declare(strict_types=1);

/* ── Configuration ─────────────────────────────────────────────────────── */
const SITE_NAME    = "MyBio";
const YEAR_FOUNDED = 2024;

/* ── User Data ─────────────────────────────────────────────────────────── */
$name       = "Shagor Ahmed";
$birthYear  = 2000;
$email      = "shagor@example.com";
$website    = "https://shagor.dev";
$skills     = ["PHP", "MySQL", "JavaScript", "Vue.js", "Laravel"];
$bio        = "  A passionate full-stack developer who loves building clean,
               scalable web apps with modern PHP practices.  ";
$isPublic   = true;
$profilePic = "🧑‍💻";   // emoji avatar for CLI fun

/* ── Computed Values ───────────────────────────────────────────────────── */
$currentYear  = (int) date('Y');
$age          = $currentYear - $birthYear;
$skillList    = implode(" • ", $skills);
$bioClean     = trim(preg_replace('/\s+/', ' ', $bio));
$emailMasked  = substr($email, 0, 3) . str_repeat("*", 6) . "@" .
                explode("@", $email)[1];

/* ── Display Bio Card ──────────────────────────────────────────────────── */
$border = str_repeat("═", 50);

echo "\n╔{$border}╗\n";
echo "║" . str_pad("  " . SITE_NAME . " – Profile Card  ", 50) . "║\n";
echo "╠{$border}╣\n";
echo "║" . str_pad("  {$profilePic}  {$name}", 50) . "║\n";
echo "╠{$border}╣\n";

$rows = [
    ["Age",     "{$age} years old ({$birthYear})"],
    ["Email",   $isPublic ? $email : $emailMasked],
    ["Website", $website],
    ["Skills",  count($skills) . " skills"],
];

foreach ($rows as [$label, $value]) {
    printf("║  %-10s : %-35s ║\n", $label, $value);
}

echo "╠{$border}╣\n";
echo "║" . str_pad("  Skills:", 50) . "║\n";

// Print skills 2-per-row
$chunks = array_chunk($skills, 2);
foreach ($chunks as $chunk) {
    $line = "  • " . implode("     • ", $chunk);
    echo "║" . str_pad($line, 50) . "║\n";
}

echo "╠{$border}╣\n";
echo "║  About Me:                                       ║\n";

// Wrap bio text at 46 chars
$words    = explode(' ', $bioClean);
$line     = "  ";
foreach ($words as $word) {
    if (strlen($line) + strlen($word) + 1 > 48) {
        echo "║" . str_pad($line, 50) . "║\n";
        $line = "  " . $word . " ";
    } else {
        $line .= $word . " ";
    }
}
if (trim($line) !== '') {
    echo "║" . str_pad(rtrim($line), 50) . "║\n";
}

echo "╠{$border}╣\n";
printf("║  %-48s ║\n", "Visibility: " . ($isPublic ? "🌐 Public" : "🔒 Private"));
printf("║  %-48s ║\n", "© " . YEAR_FOUNDED . "-{$currentYear} " . SITE_NAME);
echo "╚{$border}╝\n\n";
