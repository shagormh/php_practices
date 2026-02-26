<?php
/**
 * Level 1 – Operators
 * PHP 8+ features: match expression, intdiv(), fdiv(), nullsafe
 *
 * Operators: Arithmetic, Assignment, Comparison, Logical, Bitwise,
 *            String, Ternary, Null-coalescing, Spaceship (<=>)
 * Run: php example.php
 */

declare(strict_types=1);

echo "=== Arithmetic Operators ===\n";
$a = 17;
$b = 5;
echo "$a + $b = " . ($a + $b) . "\n";  // 22
echo "$a - $b = " . ($a - $b) . "\n";  // 12
echo "$a * $b = " . ($a * $b) . "\n";  // 85
echo "$a / $b = " . ($a / $b) . "\n";  // 3.4
echo "$a % $b = " . ($a % $b) . "\n";  // 2  (modulo)
echo "$a ** $b = " . ($a ** $b) . "\n"; // 1419857 (power)
echo "intdiv($a,$b) = " . intdiv($a, $b) . "\n"; // 3 (integer division)
echo "\n";

echo "=== Assignment Operators ===\n";
$x = 10;
$x += 5;  echo "\$x += 5  → $x\n";  // 15
$x -= 3;  echo "\$x -= 3  → $x\n";  // 12
$x *= 2;  echo "\$x *= 2  → $x\n";  // 24
$x /= 4;  echo "\$x /= 4  → $x\n";  // 6
$x **= 2; echo "\$x **= 2 → $x\n";  // 36
$x %= 10; echo "\$x %= 10 → $x\n";  // 6
$s = "Hello";
$s .= " World"; echo "\$s .= ' World' → $s\n\n";

echo "=== Comparison Operators ===\n";
var_dump(5 == "5");    // true  (loose)
var_dump(5 === "5");   // false (strict – different types)
var_dump(5 != "5");    // false
var_dump(5 !== "5");   // true
var_dump(10 > 5);      // true
var_dump(10 <= 10);    // true

echo "\n=== Spaceship Operator <=> ===\n";
echo (1 <=> 2) . " (1 vs 2)\n";   // -1 (left smaller)
echo (2 <=> 2) . " (2 vs 2)\n";   //  0 (equal)
echo (3 <=> 2) . " (3 vs 2)\n";   //  1 (left bigger)

// Useful in usort
$numbers = [3, 1, 4, 1, 5, 9, 2, 6];
usort($numbers, fn($a, $b) => $a <=> $b);
echo "Sorted: " . implode(', ', $numbers) . "\n\n";

echo "=== Logical Operators ===\n";
$logged   = true;
$isAdmin  = false;
var_dump($logged && $isAdmin);   // false (AND)
var_dump($logged || $isAdmin);   // true  (OR)
var_dump(!$logged);              // false (NOT)
var_dump($logged xor $isAdmin);  // true  (XOR)

echo "\n=== Ternary & Null-coalescing ===\n";
$score  = 75;
$result = $score >= 50 ? "Pass" : "Fail";
echo "Result: $result\n";

// Short ternary (Elvis)
$username = "" ?: "Guest";
echo "User: $username\n";  // Guest

// Null coalescing
$data = ['color' => 'blue'];
echo "Color : " . ($data['color'] ?? 'none') . "\n";   // blue
echo "Size  : " . ($data['size']  ?? 'none') . "\n";   // none

// Null coalescing assignment
$data['count'] ??= 0;
echo "Count : {$data['count']}\n\n";

echo "=== Bitwise Operators ===\n";
$p = 0b1010;   // 10
$q = 0b1100;   // 12
printf("AND  : %04b (%d)\n", $p & $q, $p & $q);   // 1000 (8)
printf("OR   : %04b (%d)\n", $p | $q, $p | $q);   // 1110 (14)
printf("XOR  : %04b (%d)\n", $p ^ $q, $p ^ $q);   // 0110 (6)
printf("NOT  : %d\n", ~$p);                         // -11
printf("Left : %04b (%d)\n", $p << 1, $p << 1);    // 10100 (20)
printf("Right: %04b (%d)\n", $q >> 1, $q >> 1);    // 0110  (6)

echo "\n=== String Operators ===\n";
$first = "PHP";
$last  = "8";
$full  = $first . " " . $last;       // concatenation
echo "$full\n";

/*
 * Expected Output (partial):
 * === Arithmetic Operators ===
 * 17 + 5 = 22
 * 17 - 5 = 12
 * ...
 */
