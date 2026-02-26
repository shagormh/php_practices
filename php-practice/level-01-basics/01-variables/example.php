<?php
/**
 * Level 1 – Variables & Data Types
 * PHP 8+ features used: typed declarations (in the functions below), mixed type
 *
 * PHP variables start with $ and are dynamically typed.
 * PHP 8 supports 8 primitive types: bool, int, float, string, array, object, null, resource.
 * Run: php example.php
 */

declare(strict_types=1);

// ─── Scalar Types ───────────────────────────────────────────────────────────
$name      = "Alice";          // string
$age       = 25;               // int
$height    = 1.68;             // float
$isStudent = true;             // bool
$grade     = null;             // null

echo "=== Scalar Types ===\n";
echo "Name    : $name\n";
echo "Age     : $age\n";
echo "Height  : {$height}m\n";
echo "Student : " . ($isStudent ? 'Yes' : 'No') . "\n";
echo "Grade   : " . ($grade ?? 'Not assigned') . "\n\n";

// ─── Type Juggling & Casting ─────────────────────────────────────────────────
$numStr = "42";
echo "=== Type Juggling ===\n";
echo "String '42' + 8 = " . ($numStr + 8) . "\n";  // PHP auto-converts → 50
echo "(int)'42abc' = " . (int)"42abc" . "\n";        // 42
echo "(bool)0 = " . var_export((bool)0, true) . "\n"; // false

echo "\n";

// ─── Constants ───────────────────────────────────────────────────────────────
define('APP_NAME', 'PHPPractice');          // Traditional constant
const VERSION = '8.3';                     // Class-scope or file-scope constant

echo "=== Constants ===\n";
echo "App     : " . APP_NAME . "\n";
echo "Version : " . VERSION . "\n\n";

// ─── String Functions ─────────────────────────────────────────────────────────
$sentence = "  Hello, PHP World!  ";
echo "=== String Functions ===\n";
echo "Original      : '$sentence'\n";
echo "Trimmed       : '" . trim($sentence) . "'\n";
echo "Uppercase     : " . strtoupper(trim($sentence)) . "\n";
echo "Length        : " . strlen(trim($sentence)) . "\n";
echo "Replace       : " . str_replace("PHP", "Modern PHP", trim($sentence)) . "\n";
echo "Substring     : " . substr(trim($sentence), 7, 3) . "\n";    // PHP
echo "Contains PHP? : " . (str_contains($sentence, 'PHP') ? 'Yes' : 'No') . "\n\n";

// ─── Array Types ─────────────────────────────────────────────────────────────
$fruits  = ["apple", "banana", "cherry"];                            // indexed
$person  = ["name" => "Bob", "age" => 30, "city" => "Dhaka"];      // associative
$matrix  = [[1, 2], [3, 4]];                                         // nested

echo "=== Arrays ===\n";
echo "First fruit : $fruits[0]\n";
echo "Person name : {$person['name']}\n";
echo "Matrix[1][0]: {$matrix[1][0]}\n";
echo "All fruits  : " . implode(", ", $fruits) . "\n\n";

// ─── Variable Variables (advanced trick) ─────────────────────────────────────
$varName = "city";
$$varName = "Chittagong";        // creates $city = "Chittagong"
echo "=== Variable Variables ===\n";
echo "City: $city\n\n";

// ─── Type Checking ────────────────────────────────────────────────────────────
echo "=== Type Checking ===\n";
echo "gettype(\$name)   : " . gettype($name) . "\n";
echo "gettype(\$age)    : " . gettype($age) . "\n";
echo "gettype(\$height) : " . gettype($height) . "\n";
echo "is_string(\$name) : " . var_export(is_string($name), true) . "\n";
echo "is_int(\$age)     : " . var_export(is_int($age), true) . "\n";

/*
 * Expected Output:
 * === Scalar Types ===
 * Name    : Alice
 * Age     : 25
 * Height  : 1.68m
 * Student : Yes
 * Grade   : Not assigned
 *
 * === Type Juggling ===
 * String '42' + 8 = 50
 * (int)'42abc' = 42
 * (bool)0 = false
 * ...
 */
