<?php
/**
 * Level 1 – Functions
 * PHP 8+ features: named arguments, union types, typed properties,
 *                  arrow functions, nullsafe, readonly, fibers (8.1)
 *
 * Run: php example.php
 */

declare(strict_types=1);

// ─── Basic Function ───────────────────────────────────────────────────────
function greet(string $name, string $greeting = "Hello"): string
{
    return "$greeting, $name! 👋";
}

echo "=== Basic Function ===\n";
echo greet("Alice") . "\n";
echo greet("Bob", "Hi") . "\n";

// ─── Named Arguments (PHP 8.0+) ───────────────────────────────────────────
echo "\n=== Named Arguments ===\n";
echo greet(greeting: "Salam", name: "Rahim") . "\n";

// Built-in with named args
$arr = [3, 1, 4, 1, 5, 9, 2, 6];
echo implode(separator: ", ", array: $arr) . "\n";

// ─── Union Types (PHP 8.0+) ───────────────────────────────────────────────
echo "\n=== Union Types ===\n";
function formatId(int|string $id): string
{
    return is_int($id) ? sprintf("#%06d", $id) : strtoupper($id);
}
echo formatId(42) . "\n";       // #000042
echo formatId("abc") . "\n";   // ABC

// ─── Nullable Types ───────────────────────────────────────────────────────
function getDiscount(?float $discount): string
{
    return $discount !== null
        ? number_format($discount, 2) . "% off"
        : "No discount";
}

echo "\n=== Nullable Types ===\n";
echo getDiscount(12.5) . "\n";   // 12.50% off
echo getDiscount(null) . "\n";   // No discount

// ─── Variadic Functions ───────────────────────────────────────────────────
function sumAll(int ...$numbers): int
{
    return array_sum($numbers);
}

echo "\n=== Variadic Functions ===\n";
echo "Sum: " . sumAll(1, 2, 3, 4, 5) . "\n";     // 15
echo "Sum: " . sumAll(...[10, 20, 30]) . "\n";    // 60 (spread)

// ─── Arrow Functions (PHP 7.4+) ───────────────────────────────────────────
echo "\n=== Arrow Functions ===\n";
$multiplier = 3;
$multiply   = fn(int $n): int => $n * $multiplier;   // captures $multiplier

$nums    = [1, 2, 3, 4, 5, 6];
$tripled = array_map($multiply, $nums);
echo "Tripled: " . implode(", ", $tripled) . "\n";

$evens = array_filter($nums, fn($n) => $n % 2 === 0);
echo "Evens  : " . implode(", ", $evens) . "\n";

$total = array_reduce($nums, fn($carry, $n) => $carry + $n, 0);
echo "Total  : $total\n";

// ─── Closures ─────────────────────────────────────────────────────────────
echo "\n=== Closure / use() ===\n";
$prefix = "PHP";
$addPrefix = function (string $text) use ($prefix): string {
    return "{$prefix}: {$text}";
};

echo $addPrefix("Is awesome!") . "\n";

// Closure assigned to variable, used as callback
$tags    = ["php", "laravel", "mysql", "javascript"];
$uppercased = array_map(fn($t) => strtoupper($t), $tags);
echo implode(" | ", $uppercased) . "\n";

// ─── Recursion ────────────────────────────────────────────────────────────
echo "\n=== Recursion ===\n";
function factorial(int $n): int
{
    if ($n <= 1) return 1;
    return $n * factorial($n - 1);
}

for ($k = 1; $k <= 8; $k++) {
    echo "  {$k}! = " . factorial($k) . "\n";
}

// ─── Return Types – never (PHP 8.1) ──────────────────────────────────────
// never type = function always throws or exits
function throwError(string $msg): never
{
    throw new \RuntimeException($msg);
}

try {
    throwError("Something went wrong");
} catch (\RuntimeException $e) {
    echo "\n=== never return type ===\n";
    echo "Caught: " . $e->getMessage() . "\n";
}

// ─── First-class Callable Syntax (PHP 8.1) ────────────────────────────────
echo "\n=== First-class Callables (PHP 8.1+) ===\n";
$strlen = strlen(...);    // Closure wrapping strlen
$words  = ["apple", "banana", "kiwi"];
$lengths = array_map($strlen, $words);
echo implode(", ", $lengths) . "\n";   // 5, 6, 4

/*
 * Expected Output (partial):
 * === Basic Function ===
 * Hello, Alice! 👋
 * Hi, Bob! 👋
 * ...
 * === Recursion ===
 *   1! = 1
 *   2! = 2
 *   ...
 *   8! = 40320
 */
