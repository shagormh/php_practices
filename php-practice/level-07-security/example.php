<?php
/**
 * Level 7 – Security & Best Practices
 * PHP 8+ features: enums, match, readonly, typed properties
 *
 * Covers: SQL Injection, XSS, CSRF, Password Hashing,
 *         Rate Limiting, Input Sanitization, Secure Headers
 * Run: php example.php  (for CLI demos)
 *      php -S localhost:8000 example.php  (for web demos)
 */

declare(strict_types=1);

echo "╔══════════════════════════════════════════════╗\n";
echo "║   PHP Security & Best Practices Demo         ║\n";
echo "╚══════════════════════════════════════════════╝\n\n";

/* ══════════════════════════════════════════════════════════════
 * 1. SQL INJECTION PREVENTION – Always use prepared statements
 * ══════════════════════════════════════════════════════════════ */

echo "=== 1. SQL Injection Prevention ===\n";

// ❌ VULNERABLE (never do this!)
function vulnerableLogin(string $username, string $password): string
{
    // An attacker can input: admin' -- (bypasses password check!)
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    echo "  ❌ Vulnerable SQL:\n  $sql\n";
    return $sql;
}

// ✅ SAFE – Prepared Statements with PDO
function safeLogin(\PDO $pdo, string $username, string $password): ?array
{
    // Password handled separately via password_verify()
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return null;
    }
    return $user;
}

vulnerableLogin("admin", "' OR '1'='1");
echo "  ✅ Safe: Prepared statement – input is always parameterized.\n\n";

/* ══════════════════════════════════════════════════════════════
 * 2. XSS PREVENTION – Escape all user output
 * ══════════════════════════════════════════════════════════════ */

echo "=== 2. XSS (Cross-Site Scripting) Prevention ===\n";

$maliciousInput = '<script>alert("XSS Attack!")</script>';

// ❌ VULNERABLE
$vulnerable = "  ❌ Vulnerable: Hello, $maliciousInput";
echo "  If rendered in HTML, runs the script!\n";

// ✅ SAFE
$safe = htmlspecialchars($maliciousInput, ENT_QUOTES | ENT_HTML5, 'UTF-8');
echo "  ✅ Escaped: $safe\n";

// Helper function
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

$userInput = '<img src=x onerror=alert(1)>';
echo "  ✅ Safe output: " . e($userInput) . "\n\n";

/* ══════════════════════════════════════════════════════════════
 * 3. CSRF PROTECTION – Random tokens in forms
 * ══════════════════════════════════════════════════════════════ */

echo "=== 3. CSRF Protection ===\n";

function generateCsrfToken(): string
{
    return bin2hex(random_bytes(32));   // 64-char hex token
}

function validateCsrfToken(string $formToken, string $sessionToken): bool
{
    return hash_equals($sessionToken, $formToken);   // timing-safe comparison
}

// Simulate: generate token and store in session
$csrfToken = generateCsrfToken();
echo "  CSRF Token: " . substr($csrfToken, 0, 16) . "...\n";

// Simulate verification
$formToken = $csrfToken;    // In real app: $_POST['_csrf_token']
$valid     = validateCsrfToken($formToken, $csrfToken);
echo "  Token valid: " . ($valid ? '✅ Yes' : '❌ No - Reject request!') . "\n\n";

/* ══════════════════════════════════════════════════════════════
 * 4. PASSWORD HASHING – bcrypt / argon2
 * ══════════════════════════════════════════════════════════════ */

echo "=== 4. Password Hashing ===\n";

$plainPassword = "MySecureP@ss123";

// ❌ NEVER store plain text or MD5/SHA1!
echo "  ❌ Plain: $plainPassword\n";
echo "  ❌ MD5  : " . md5($plainPassword) . " (crackable!)\n";

// ✅ Use password_hash() with bcrypt (default)
$hash = password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 12]);
echo "  ✅ Bcrypt: " . substr($hash, 0, 30) . "...\n";

// ✅ Verify
$verified = password_verify($plainPassword, $hash);
$wrong    = password_verify("WrongPassword", $hash);
echo "  Verify correct: " . ($verified ? '✅' : '❌') . "\n";
echo "  Verify wrong  : " . ($wrong ? '✅' : '❌') . "\n";

// ✅ Argon2id (even better, PHP 7.3+)
$argon2 = password_hash($plainPassword, PASSWORD_ARGON2ID);
echo "  ✅ Argon2id: " . substr($argon2, 0, 30) . "...\n";

// ✅ Check if rehash needed (e.g. cost changed)
$needsRehash = password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 14]);
echo "  Needs rehash (cost 12→14)? " . ($needsRehash ? 'Yes' : 'No') . "\n\n";

/* ══════════════════════════════════════════════════════════════
 * 5. INPUT VALIDATION & SANITIZATION
 * ══════════════════════════════════════════════════════════════ */

echo "=== 5. Input Validation & Sanitization ===\n";

class InputValidator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            foreach (explode('|', $ruleSet) as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }
        return empty($this->errors);
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        [$name, $param] = explode(':', $rule) + [1 => null];
        match($name) {
            'required' => empty($value) ? $this->addError($field, "required")          : null,
            'email'    => !filter_var($value, FILTER_VALIDATE_EMAIL) ? $this->addError($field, "invalid email") : null,
            'min'      => strlen((string)$value) < (int)$param ? $this->addError($field, "min $param chars") : null,
            'max'      => strlen((string)$value) > (int)$param ? $this->addError($field, "max $param chars") : null,
            'numeric'  => !is_numeric($value) ? $this->addError($field, "must be numeric") : null,
            'url'      => !filter_var($value, FILTER_VALIDATE_URL) ? $this->addError($field, "invalid URL") : null,
            'alpha'    => !ctype_alpha($value) ? $this->addError($field, "only letters") : null,
            'regex'    => !preg_match($param, (string)$value) ? $this->addError($field, "invalid format") : null,
            default    => null,
        };
    }

    private function addError(string $field, string $msg): void
    {
        $this->errors[$field][] = $msg;
    }

    public function getErrors(): array { return $this->errors; }
    public function passes(): bool     { return empty($this->errors); }
}

// Sanitization helpers
function sanitizeString(string $input, int $maxLen = 255): string
{
    return substr(strip_tags(trim($input)), 0, $maxLen);
}

function sanitizeInt(mixed $input): int
{
    return (int) filter_var($input, FILTER_SANITIZE_NUMBER_INT);
}

function sanitizeEmail(string $input): string|false
{
    return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
}

$v = new InputValidator();
$data = [
    'name'  => 'A',
    'email' => 'not-an-email',
    'age'   => 'abc',
    'phone' => '01812345678',
];
$valid = $v->validate($data, [
    'name'  => 'required|min:2|max:50',
    'email' => 'required|email',
    'age'   => 'required|numeric',
    'phone' => 'required|regex:/^01[3-9]\d{8}$/',
]);

echo "  Valid: " . ($valid ? 'Yes' : 'No') . "\n";
foreach ($v->getErrors() as $field => $msgs) {
    foreach ($msgs as $msg) {
        echo "  ❌ $field: $msg\n";
    }
}

echo "\n";

/* ══════════════════════════════════════════════════════════════
 * 6. SECURE HEADERS
 * ══════════════════════════════════════════════════════════════ */

echo "=== 6. Secure HTTP Headers ===\n";

function applySecureHeaders(): void
{
    if (headers_sent()) {
        echo "  [Note: Headers sent – showing what would be set]\n";
        $fn = fn($h) => print("    $h\n");
    } else {
        $fn = 'header';
    }

    $headers = [
        "Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'",
        "X-Content-Type-Options: nosniff",
        "X-Frame-Options: SAMEORIGIN",
        "X-XSS-Protection: 1; mode=block",
        "Referrer-Policy: strict-origin-when-cross-origin",
        "Permissions-Policy: camera=(), microphone=(), geolocation=()",
        "Strict-Transport-Security: max-age=31536000; includeSubDomains",
    ];

    foreach ($headers as $h) {
        $fn($h);
    }
}

applySecureHeaders();
echo "\n";

/* ══════════════════════════════════════════════════════════════
 * 7. RATE LIMITING (File-based)
 * ══════════════════════════════════════════════════════════════ */

echo "=== 7. Rate Limiting ===\n";

class RateLimiter
{
    private string $storePath;

    public function __construct(string $storePath = '/tmp/rate_limits')
    {
        $this->storePath = $storePath;
        if (!is_dir($storePath)) mkdir($storePath, 0755, true);
    }

    public function attempt(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $file = $this->storePath . '/' . md5($key) . '.json';
        $now  = time();
        $data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

        // Remove expired entries
        $data = array_filter($data, fn($t) => $t > $now - $decaySeconds);

        if (count($data) >= $maxAttempts) {
            return false;   // Rate limit exceeded
        }

        $data[] = $now;
        file_put_contents($file, json_encode($data));
        return true;
    }

    public function remaining(string $key, int $maxAttempts, int $decaySeconds): int
    {
        $file = $this->storePath . '/' . md5($key) . '.json';
        if (!file_exists($file)) return $maxAttempts;
        $data = json_decode(file_get_contents($file), true);
        $data = array_filter($data, fn($t) => $t > time() - $decaySeconds);
        return max(0, $maxAttempts - count($data));
    }
}

$limiter = new RateLimiter();
$ip      = '127.0.0.1';
$max     = 5;
$decay   = 60;   // per minute

echo "  Simulating 7 requests (limit: $max/min):\n";
for ($i = 1; $i <= 7; $i++) {
    $allowed   = $limiter->attempt("login:$ip", $max, $decay);
    $remaining = $limiter->remaining("login:$ip", $max, $decay);
    $status    = $allowed ? "✅ Allowed" : "🚫 Blocked";
    echo "  Request $i: $status (remaining: $remaining)\n";
}

echo "\n";

/* ══════════════════════════════════════════════════════════════
 * 8. SECURE FILE UPLOAD VALIDATION
 * ══════════════════════════════════════════════════════════════ */

echo "=== 8. Secure File Upload Validation ===\n";

class FileUploadValidator
{
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const MAX_SIZE_BYTES = 5 * 1024 * 1024;   // 5MB

    public function validate(array $file): array
    {
        $errors = [];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Upload error code: {$file['error']}";
            return $errors;
        }

        if ($file['size'] > self::MAX_SIZE_BYTES) {
            $errors[] = "File too large (max 5MB).";
        }

        // Verify actual MIME type (not just file extension!)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        // $mime = $finfo->file($file['tmp_name']);     // use for real uploads
        $mime = $file['type'];   // simplified for demo

        if (!in_array($mime, self::ALLOWED_TYPES, true)) {
            $errors[] = "Unsupported file type: $mime";
        }

        // Never trust the original filename
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $safeName = bin2hex(random_bytes(16)) . '.' . $ext;
        echo "  Safe filename: $safeName\n";

        return $errors;
    }
}

// Simulate file upload data
$fakeFile = [
    'name'     => '../../../etc/passwd',    // ← path traversal attempt!
    'type'     => 'image/jpeg',
    'tmp_name' => '/tmp/php7f2k9l',
    'error'    => UPLOAD_ERR_OK,
    'size'     => 102400,
];

$upValidator = new FileUploadValidator();
$uploadErrors = $upValidator->validate($fakeFile);
if (empty($uploadErrors)) {
    echo "  ✅ File valid\n";
} else {
    foreach ($uploadErrors as $err) echo "  ❌ $err\n";
}

/*
 * Expected Output:
 * === 1. SQL Injection Prevention ===
 * ❌ Vulnerable SQL: SELECT * FROM users WHERE username = 'admin' AND password = '' OR '1'='1'
 * ✅ Safe: Prepared statement ...
 * ....
 */
