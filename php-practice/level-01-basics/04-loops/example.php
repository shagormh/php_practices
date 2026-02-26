<?php
/**
 * Level 1 – Loops
 * PHP 8+ features: array unpacking in match, generators
 *
 * while, do-while, for, foreach, break, continue
 * Run: php example.php
 */

declare(strict_types=1);

// ─── while ────────────────────────────────────────────────────────────────
echo "=== while ===\n";
$i = 1;
while ($i <= 5) {
    echo "  Count: $i\n";
    $i++;
}

// ─── do-while ────────────────────────────────────────────────────────────
echo "\n=== do-while ===\n";
$pin = 0;
$secret = 4242;
$tries  = 0;

// Simulates user input without real input
$guesses = [1111, 2222, 4242];
foreach ($guesses as $guess) {
    do {
        $pin = $guess;   // pretend user typed this
        $tries++;
        break;           // exit inner do-while after one attempt
    } while ($pin !== $secret);

    if ($pin === $secret) break;
}
echo "  Correct PIN found after $tries tries!\n";

// ─── for ─────────────────────────────────────────────────────────────────
echo "\n=== for loop – Multiplication Table ===\n";
$n = 5;
for ($j = 1; $j <= 10; $j++) {
    printf("  %d × %2d = %3d\n", $n, $j, $n * $j);
}

// ─── foreach ─────────────────────────────────────────────────────────────
echo "\n=== foreach – Indexed Array ===\n";
$colors = ["Red", "Green", "Blue", "Yellow"];
foreach ($colors as $index => $color) {
    echo "  [$index] $color\n";
}

echo "\n=== foreach – Associative Array ===\n";
$student = [
    "name"    => "Rahim",
    "age"     => 22,
    "subject" => "CS",
    "gpa"     => 3.85,
];
foreach ($student as $key => $value) {
    printf("  %-10s : %s\n", ucfirst($key), $value);
}

// ─── Nested loops – Pattern ───────────────────────────────────────────────
echo "\n=== Nested for – Star Triangle ===\n";
$rows = 6;
for ($row = 1; $row <= $rows; $row++) {
    echo "  " . str_repeat("★ ", $row) . "\n";
}

// ─── break & continue ─────────────────────────────────────────────────────
echo "\n=== break & continue ===\n";
echo "  Odd numbers 1-20 (skip multiples of 5):\n  ";
for ($k = 1; $k <= 20; $k++) {
    if ($k % 2 === 0)  continue;       // skip evens
    if ($k % 5 === 0)  continue;       // skip 5, 15
    echo "$k ";
}
echo "\n";

// break with level (break 2 = break out of 2 nested loops)
echo "\n=== break 2 (nested search) ===\n";
$matrix = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9],
];
$target = 5;
$found  = false;

foreach ($matrix as $r => $row) {
    foreach ($row as $c => $val) {
        if ($val === $target) {
            echo "  Found $target at [\$matrix[$r][$c]]\n";
            $found = true;
            break 2;   // ← exits both loops at once
        }
    }
}
if (!$found) echo "  Not found.\n";

// ─── Generator (lazy loop) ────────────────────────────────────────────────
echo "\n=== Generator – Fibonacci ===\n";
function fibonacci(int $limit): Generator
{
    [$a, $b] = [0, 1];
    while ($a <= $limit) {
        yield $a;
        [$a, $b] = [$b, $a + $b];
    }
}

echo "  ";
foreach (fibonacci(100) as $fib) {
    echo "$fib ";
}
echo "\n";

/*
 * Expected Output (partial):
 * === while ===
 *   Count: 1 ... Count: 5
 * ...
 * === Generator – Fibonacci ===
 *   0 1 1 2 3 5 8 13 21 34 55 89
 */
