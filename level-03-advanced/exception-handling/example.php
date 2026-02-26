<?php
/**
 * Level 3 – Exception Handling
 * PHP 8+ features: match, union types, named args, Fiber (8.1)
 * Custom exception hierarchy, try/catch/finally, exception chaining
 *
 * Run: php example.php
 */

declare(strict_types=1);

/* ── Custom Exception Hierarchy ──────────────────────────────────────── */
class AppException extends \RuntimeException {}

class ValidationException extends AppException
{
    public function __construct(
        private readonly array $errors,
        string $message = "Validation failed",
        int $code = 422,
    ) {
        parent::__construct($message, $code);
    }

    public function getErrors(): array { return $this->errors; }
}

class DatabaseException extends AppException
{
    public function __construct(
        string $message,
        int $code = 500,
        ?\Throwable $previous = null,
    ) {
        parent::__construct("DB Error: $message", $code, $previous);
    }
}

class NotFoundException extends AppException
{
    public function __construct(string $resource, int|string $id)
    {
        parent::__construct("$resource with ID '$id' not found.", 404);
    }
}

class UnauthorizedException extends AppException
{
    public function __construct(string $action = "perform this action")
    {
        parent::__construct("You are not authorized to $action.", 403);
    }
}

/* ── Simulated Service Layer ─────────────────────────────────────────── */
class UserService
{
    private array $users = [
        1 => ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com', 'role' => 'admin'],
        2 => ['id' => 2, 'name' => 'Bob',   'email' => 'bob@example.com',   'role' => 'user'],
    ];

    public function find(int $id): array
    {
        if (!isset($this->users[$id])) {
            throw new NotFoundException('User', $id);
        }
        return $this->users[$id];
    }

    public function create(array $data): array
    {
        $errors = [];
        if (empty($data['name']) || strlen($data['name']) < 2) {
            $errors['name'] = "Name must be at least 2 characters.";
        }
        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Invalid email address.";
        }
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        // Simulate DB error
        if (($data['email'] ?? '') === 'fail@example.com') {
            try {
                throw new \PDOException("Duplicate entry for key 'email'");
            } catch (\PDOException $e) {
                throw new DatabaseException("Could not create user", 500, $e);   // chaining
            }
        }

        $id = count($this->users) + 1;
        $this->users[$id] = array_merge($data, ['id' => $id]);
        return $this->users[$id];
    }

    public function delete(int $id, string $actorRole): bool
    {
        if ($actorRole !== 'admin') {
            throw new UnauthorizedException("delete users");
        }
        $this->find($id);   // throws NotFoundException if not found
        unset($this->users[$id]);
        return true;
    }
}

/* ── Handler Function ────────────────────────────────────────────────── */
function handleException(\Throwable $e): void
{
    $type = match(true) {
        $e instanceof ValidationException  => "⚠️  ValidationException",
        $e instanceof NotFoundException    => "🔍 NotFoundException",
        $e instanceof UnauthorizedException=> "🚫 UnauthorizedException",
        $e instanceof DatabaseException    => "💾 DatabaseException",
        $e instanceof AppException         => "🔥 AppException",
        default                            => "💥 " . get_class($e),
    };

    echo "  $type [{$e->getCode()}]: {$e->getMessage()}\n";

    if ($e instanceof ValidationException) {
        foreach ($e->getErrors() as $field => $msg) {
            echo "    • $field: $msg\n";
        }
    }
    if ($prev = $e->getPrevious()) {
        echo "  Caused by: " . get_class($prev) . ": " . $prev->getMessage() . "\n";
    }
}

/* ── Demo ────────────────────────────────────────────────────────────── */
$svc = new UserService();

echo "=== Exception Handling Demo ===\n\n";

// 1. NotFoundException
echo "1. Find user ID 99:\n";
try {
    $user = $svc->find(99);
} catch (NotFoundException $e) {
    handleException($e);
}

// 2. ValidationException
echo "\n2. Create with invalid data:\n";
try {
    $svc->create(['name' => 'A', 'email' => 'not-an-email']);
} catch (ValidationException $e) {
    handleException($e);
}

// 3. DatabaseException (with chaining)
echo "\n3. Create with DB failure email:\n";
try {
    $svc->create(['name' => 'Carol', 'email' => 'fail@example.com']);
} catch (DatabaseException $e) {
    handleException($e);
}

// 4. UnauthorizedException
echo "\n4. Delete as non-admin:\n";
try {
    $svc->delete(1, 'user');
} catch (UnauthorizedException $e) {
    handleException($e);
}

// 5. Success + finally
echo "\n5. Successful create + finally:\n";
try {
    $user = $svc->create(['name' => 'Diana', 'email' => 'diana@example.com']);
    echo "  ✅ Created: {$user['name']} (ID: {$user['id']})\n";
} catch (AppException $e) {
    handleException($e);
} finally {
    echo "  🏁 Operation complete (finally always runs)\n";
}

// 6. Catch multiple types
echo "\n6. Multiple catch types:\n";
try {
    if (rand(0, 1)) throw new NotFoundException('Product', 42);
    throw new UnauthorizedException();
} catch (NotFoundException | UnauthorizedException $e) {
    echo "  Caught: " . get_class($e) . " → " . $e->getMessage() . "\n";
}

// 7. set_exception_handler (global handler)
set_exception_handler(function (\Throwable $e): never {
    echo "  [GLOBAL] Unhandled: " . $e->getMessage() . "\n";
    exit(1);
});
