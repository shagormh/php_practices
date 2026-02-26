# Level 4 – OOP & Design Patterns – Practice Problems

## Practice 1: Implement a Builder Pattern for SQL Queries

Build a fluent `QueryBuilder` class that constructs SQL SELECT queries.

```php
// Expected usage:
$query = (new QueryBuilder('users'))
    ->select('id', 'name', 'email')
    ->where('role', 'admin')
    ->where('active', 1)
    ->orderBy('name', 'ASC')
    ->limit(10)
    ->build();

// Output: SELECT id, name, email FROM users WHERE role = 'admin' AND active = '1' ORDER BY name ASC LIMIT 10
```

Create file: `practice1.php`

---

## Practice 2: Implement a Decorator Pattern

Add behaviour to objects dynamically without modifying the class.

```php
interface Logger { public function log(string $message): void; }

class FileLogger implements Logger { ... }

// Decorators add: timestamps, colours, filtering, etc.
class TimestampDecorator implements Logger { ... }
class FilterDecorator implements Logger { ... }

// Usage:
$logger = new FilterDecorator(
    new TimestampDecorator(new FileLogger('app.log')),
    ['INFO', 'ERROR']
);
$logger->log("[INFO] Application started");
$logger->log("[DEBUG] This will be filtered out");
```

Create file: `practice2.php`

---

## Practice 3: Shopping Cart with State Pattern

Implement a shopping cart that transitions through states: `Draft → Active → CheckedOut → Cancelled`.

- Guard against invalid transitions (can't checkout an empty cart)
- Each state has different allowed operations
- Use match() for clean state transitions

Create file: `practice3.php`
