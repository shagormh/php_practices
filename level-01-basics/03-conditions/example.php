<?php
/**
 * Level 1 – Conditions
 * PHP 8+ features: match expression, named arguments
 *
 * if/elseif/else, switch, match, null-safe operator
 * Run: php example.php
 */

declare(strict_types=1);

// ─── if / elseif / else ───────────────────────────────────────────────────
echo "=== if / elseif / else ===\n";
$temperature = 38;

if ($temperature < 0) {
    echo "Freezing ❄️\n";
} elseif ($temperature < 15) {
    echo "Cold 🧊\n";
} elseif ($temperature < 25) {
    echo "Comfortable 😊\n";
} elseif ($temperature < 35) {
    echo "Warm ☀️\n";
} else {
    echo "Hot 🔥 – Stay hydrated!\n";   // ← this fires
}

// ─── Nested if ────────────────────────────────────────────────────────────
echo "\n=== Nested if (login check) ===\n";
$loggedIn = true;
$role     = "admin";

if ($loggedIn) {
    if ($role === "admin") {
        echo "Welcome to Admin Panel 🛡️\n";
    } elseif ($role === "editor") {
        echo "Welcome, Editor ✏️\n";
    } else {
        echo "Welcome, User 👤\n";
    }
} else {
    echo "Please log in first.\n";
}

// ─── switch ───────────────────────────────────────────────────────────────
echo "\n=== switch ===\n";
$day = date('l');   // current day name

switch ($day) {
    case 'Monday':
    case 'Tuesday':
    case 'Wednesday':
    case 'Thursday':
        echo "$day: Regular work day 💼\n";
        break;
    case 'Friday':
        echo "Friday: Almost weekend! 🎉\n";
        break;
    case 'Saturday':
    case 'Sunday':
        echo "$day: Weekend! 🏖️\n";
        break;
    default:
        echo "Unknown day.\n";
}

// ─── match (PHP 8+) ───────────────────────────────────────────────────────
echo "\n=== match expression (PHP 8+) ===\n";
$statusCode = 404;

$message = match($statusCode) {
    200, 201 => "✅ Success",
    301, 302 => "🔀 Redirect",
    400      => "❌ Bad Request",
    401      => "🔐 Unauthorized",
    403      => "🚫 Forbidden",
    404      => "🔍 Not Found",
    500      => "💥 Internal Server Error",
    default  => "❓ Unknown Status",
};

echo "HTTP $statusCode → $message\n";

// match is strict (===), unlike switch
$score = "9";
$grade = match(true) {
    $score >= 90 => 'A+',
    $score >= 80 => 'A',
    $score >= 70 => 'B',
    $score >= 60 => 'C',
    default      => 'F',
};
echo "Grade for score $score: $grade\n";

// ─── Conditional Expressions ──────────────────────────────────────────────
echo "\n=== Conditional Shorthand ===\n";
$age     = 17;
$allowed = ($age >= 18) ? "Allowed ✅" : "Not Allowed ❌";
echo "Cinema entry: $allowed\n";

// match with no expression
$lang  = "PHP";
$emoji = match($lang) {
    "PHP"        => "🐘",
    "JavaScript" => "🟨",
    "Python"     => "🐍",
    "Go"         => "🐹",
    default      => "💻",
};
echo "$lang $emoji\n";

// ─── Null-safe Operator (?->) ─────────────────────────────────────────────
echo "\n=== Null-safe Operator (?->) ===\n";

class User {
    public ?Address $address = null;
}
class Address {
    public string $city = "Dhaka";
    public function getCity(): string { return $this->city; }
}

$user1 = new User();
$user2 = new User();
$user2->address = new Address();

// Without null-safe: would throw error if address is null
echo "User1 city: " . ($user1->address?->getCity() ?? "No address") . "\n";
echo "User2 city: " . ($user2->address?->getCity() ?? "No address") . "\n";

/*
 * Expected Output (partial):
 * Hot 🔥 – Stay hydrated!
 * Welcome to Admin Panel 🛡️
 * HTTP 404 → 🔍 Not Found
 * Cinema entry: Not Allowed ❌
 */
