<?php
/**
 * Level 2 – File Handling
 * Read, write, append, rename, delete files; directory operations.
 *
 * Run: php example.php
 */

declare(strict_types=1);

$baseDir = __DIR__ . '/storage';
if (!is_dir($baseDir)) mkdir($baseDir, 0755, true);

/* ── 1. Write a file ─────────────────────────────────────────────────── */
$logFile = $baseDir . '/app.log';
$lines   = [
    date('Y-m-d H:i:s') . " [INFO]  Application started",
    date('Y-m-d H:i:s') . " [DEBUG] Loading configuration",
    date('Y-m-d H:i:s') . " [INFO]  Database connected",
    date('Y-m-d H:i:s') . " [WARN]  Cache miss for key: user_list",
    date('Y-m-d H:i:s') . " [ERROR] Payment gateway timeout",
];
file_put_contents($logFile, implode("\n", $lines) . "\n");
echo "✅ Written: $logFile\n";

/* ── 2. Read the file ────────────────────────────────────────────────── */
echo "\n=== Full File Content ===\n";
echo file_get_contents($logFile);

/* ── 3. Read line by line ────────────────────────────────────────────── */
echo "\n=== Errors Only (filtered lines) ===\n";
$handle = fopen($logFile, 'r');
while (!feof($handle)) {
    $line = fgets($handle);
    if ($line && str_contains($line, '[ERROR]')) {
        echo "  ❌ " . trim($line) . "\n";
    }
}
fclose($handle);

/* ── 4. Append to file ───────────────────────────────────────────────── */
file_put_contents($logFile, date('Y-m-d H:i:s') . " [INFO]  Request completed\n", FILE_APPEND);
echo "\n✅ Appended new log entry.\n";
echo "File size: " . number_format(filesize($logFile)) . " bytes\n";

/* ── 5. CSV Read/Write ───────────────────────────────────────────────── */
echo "\n=== CSV Operations ===\n";
$csvFile  = $baseDir . '/students.csv';
$students = [
    ['ID', 'Name', 'Email', 'GPA'],
    ['1', 'Alice Rahman',  'alice@example.com',  '3.85'],
    ['2', 'Bob Hossain',   'bob@example.com',    '3.50'],
    ['3', 'Carol Ahmed',   'carol@example.com',  '3.95'],
    ['4', 'David Khan',    'david@example.com',  '3.10'],
];

$fp = fopen($csvFile, 'w');
foreach ($students as $row) fputcsv($fp, $row);
fclose($fp);
echo "Written CSV: $csvFile\n";

$fp   = fopen($csvFile, 'r');
$head = fgetcsv($fp);   // header row
printf("%-4s %-15s %-25s %-5s\n", ...$head);
echo str_repeat("─", 52) . "\n";
while (($row = fgetcsv($fp)) !== false) {
    printf("%-4s %-15s %-25s %-5s\n", ...$row);
}
fclose($fp);

/* ── 6. JSON File ────────────────────────────────────────────────────── */
echo "\n=== JSON File ===\n";
$jsonFile = $baseDir . '/config.json';
$config   = [
    'app'      => ['name' => 'PHPPractice', 'version' => '1.0', 'debug' => true],
    'database' => ['host' => 'localhost',   'name' => 'app_db', 'port' => 3306],
    'cache'    => ['driver' => 'redis',     'ttl' => 3600],
];
file_put_contents($jsonFile, json_encode($config, JSON_PRETTY_PRINT));

$loaded = json_decode(file_get_contents($jsonFile), true);
echo "App name  : " . $loaded['app']['name']         . "\n";
echo "DB host   : " . $loaded['database']['host']    . "\n";
echo "Cache TTL : " . $loaded['cache']['ttl'] . "s\n";

/* ── 7. Directory Operations ─────────────────────────────────────────── */
echo "\n=== Directory Listing ===\n";
foreach (new DirectoryIterator($baseDir) as $item) {
    if ($item->isDot()) continue;
    printf("  %-20s %s  %s\n",
        $item->getFilename(),
        $item->isDir() ? 'DIR ' : 'FILE',
        number_format($item->getSize()) . ' bytes'
    );
}

/* ── 8. File Info & Check ─────────────────────────────────────────────── */
echo "\n=== File Info ===\n";
foreach ([$logFile, $csvFile, $jsonFile] as $file) {
    printf("  %-25s exists: %s  readable: %s\n",
        basename($file),
        file_exists($file) ? 'Yes' : 'No',
        is_readable($file) ? 'Yes' : 'No'
    );
}
