<?php
/**
 * Level 4 – OOP Design Patterns
 * PHP 8+ features: constructor promotion, readonly, enums, match
 *
 * Patterns: Singleton, Factory, Observer, Strategy, Repository
 * Run: php example.php
 */

declare(strict_types=1);

/* ══════════════════════════════════════════════════════════════════════
 * 1. SINGLETON – Ensure only ONE instance of a class exists
 * ══════════════════════════════════════════════════════════════════════ */
final class Database
{
    private static ?Database $instance = null;
    private array $queryLog = [];

    // Prevent direct instantiation
    private function __construct(
        private readonly string $dsn = 'mysql:host=localhost;dbname=app'
    ) {
        echo "  [DB] Connection established to: {$this->dsn}\n";
    }

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function query(string $sql): array
    {
        // Simulate query result
        $this->queryLog[] = $sql;
        return ['result' => "data from: $sql"];
    }

    public function getQueryCount(): int { return count($this->queryLog); }
}

echo "=== 1. SINGLETON ===\n";
$db1 = Database::getInstance();
$db2 = Database::getInstance();
echo "  Same instance: " . ($db1 === $db2 ? 'Yes ✅' : 'No ❌') . "\n";
$db1->query("SELECT * FROM users");
$db2->query("SELECT * FROM posts");
echo "  Total queries: " . $db1->getQueryCount() . "\n\n";

/* ══════════════════════════════════════════════════════════════════════
 * 2. FACTORY – Create objects without specifying exact class
 * ══════════════════════════════════════════════════════════════════════ */
interface Notification
{
    public function send(string $to, string $message): string;
}

class EmailNotification implements Notification
{
    public function send(string $to, string $message): string {
        return "📧 Email to {$to}: {$message}";
    }
}

class SmsNotification implements Notification
{
    public function send(string $to, string $message): string {
        return "📱 SMS to {$to}: {$message}";
    }
}

class PushNotification implements Notification
{
    public function send(string $to, string $message): string {
        return "🔔 Push to {$to}: {$message}";
    }
}

class NotificationFactory
{
    public static function make(string $type): Notification
    {
        return match(strtolower($type)) {
            'email' => new EmailNotification(),
            'sms'   => new SmsNotification(),
            'push'  => new PushNotification(),
            default => throw new \InvalidArgumentException("Unknown type: $type"),
        };
    }
}

echo "=== 2. FACTORY ===\n";
foreach (['email', 'sms', 'push'] as $type) {
    $notifier = NotificationFactory::make($type);
    echo "  " . $notifier->send("alice@example.com", "Your order is shipped!") . "\n";
}
echo "\n";

/* ══════════════════════════════════════════════════════════════════════
 * 3. OBSERVER – Subscribe to and react to events
 * ══════════════════════════════════════════════════════════════════════ */
interface Observer
{
    public function update(string $event, mixed $data): void;
}

trait Observable2
{
    private array $observers = [];

    public function subscribe(string $event, Observer $observer): void
    {
        $this->observers[$event][] = $observer;
    }

    public function notify(string $event, mixed $data = null): void
    {
        foreach ($this->observers[$event] ?? [] as $obs) {
            $obs->update($event, $data);
        }
    }
}

class OrderPlacedEvent { public function __construct(public readonly array $order) {} }

class EmailObserver implements Observer
{
    public function update(string $event, mixed $data): void {
        echo "  📧 Email: Order #{$data->order['id']} confirmed to {$data->order['customer']}\n";
    }
}

class InventoryObserver implements Observer
{
    public function update(string $event, mixed $data): void {
        echo "  📦 Inventory: Reducing stock for items in order #{$data->order['id']}\n";
    }
}

class AnalyticsObserver implements Observer
{
    public function update(string $event, mixed $data): void {
        echo "  📊 Analytics: Logged event '{$event}' for order #{$data->order['id']}\n";
    }
}

class Order
{
    use Observable2;

    private static int $sequence = 1000;
    private int $id;

    public function __construct(
        private string $customer,
        private array  $items,
        private float  $total,
    ) {
        $this->id = ++self::$sequence;
    }

    public function place(): static
    {
        $event = new OrderPlacedEvent([
            'id'       => $this->id,
            'customer' => $this->customer,
            'total'    => $this->total,
        ]);
        $this->notify('order.placed', $event);
        return $this;
    }
}

echo "=== 3. OBSERVER ===\n";
$order = new Order("Alice", ["PHP Book", "Keyboard"], 1650.00);
$order->subscribe('order.placed', new EmailObserver());
$order->subscribe('order.placed', new InventoryObserver());
$order->subscribe('order.placed', new AnalyticsObserver());
$order->place();
echo "\n";

/* ══════════════════════════════════════════════════════════════════════
 * 4. STRATEGY – Swap algorithms at runtime
 * ══════════════════════════════════════════════════════════════════════ */
interface SortStrategy
{
    public function sort(array $data): array;
    public function name(): string;
}

class BubbleSort implements SortStrategy
{
    public function sort(array $data): array {
        $n = count($data);
        for ($i = 0; $i < $n - 1; $i++) {
            for ($j = 0; $j < $n - $i - 1; $j++) {
                if ($data[$j] > $data[$j + 1]) {
                    [$data[$j], $data[$j + 1]] = [$data[$j + 1], $data[$j]];
                }
            }
        }
        return $data;
    }
    public function name(): string { return "Bubble Sort"; }
}

class QuickSortStrategy implements SortStrategy
{
    public function sort(array $data): array {
        if (count($data) <= 1) return $data;
        $pivot  = array_shift($data);
        $left   = array_filter($data, fn($x) => $x <= $pivot);
        $right  = array_filter($data, fn($x) => $x  > $pivot);
        return [...$this->sort(array_values($left)), $pivot, ...$this->sort(array_values($right))];
    }
    public function name(): string { return "Quick Sort"; }
}

class Sorter
{
    public function __construct(private SortStrategy $strategy) {}

    public function setStrategy(SortStrategy $strategy): void {
        $this->strategy = $strategy;
    }

    public function sort(array $data): array {
        echo "  Using {$this->strategy->name()}\n";
        return $this->strategy->sort($data);
    }
}

echo "=== 4. STRATEGY ===\n";
$data   = [64, 25, 12, 22, 11];
$sorter = new Sorter(new BubbleSort());
$result = $sorter->sort($data);
echo "  Result: " . implode(", ", $result) . "\n";

$sorter->setStrategy(new QuickSortStrategy());
$result = $sorter->sort($data);
echo "  Result: " . implode(", ", $result) . "\n\n";

/* ══════════════════════════════════════════════════════════════════════
 * 5. REPOSITORY – Abstract data layer from business logic
 * ══════════════════════════════════════════════════════════════════════ */
interface UserRepositoryInterface
{
    public function findById(int $id): ?array;
    public function findAll(): array;
    public function save(array $user): array;
    public function delete(int $id): bool;
}

class InMemoryUserRepository implements UserRepositoryInterface
{
    private array $store = [
        1 => ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
        2 => ['id' => 2, 'name' => 'Bob',   'email' => 'bob@example.com'],
    ];

    public function findById(int $id): ?array   { return $this->store[$id] ?? null; }
    public function findAll(): array             { return array_values($this->store); }
    public function save(array $user): array {
        $id = $user['id'] ?? (max(array_keys($this->store)) + 1);
        $user['id'] = $id;
        $this->store[$id] = $user;
        return $user;
    }
    public function delete(int $id): bool {
        if (!isset($this->store[$id])) return false;
        unset($this->store[$id]);
        return true;
    }
}

class UserService2
{
    public function __construct(private UserRepositoryInterface $repo) {}

    public function getUser(int $id): array
    {
        $user = $this->repo->findById($id);
        if (!$user) throw new \RuntimeException("User $id not found");
        return $user;
    }

    public function createUser(string $name, string $email): array
    {
        return $this->repo->save(compact('name', 'email'));
    }
}

echo "=== 5. REPOSITORY ===\n";
$repo    = new InMemoryUserRepository();
$service = new UserService2($repo);
$carol   = $service->createUser("Carol", "carol@example.com");
echo "  Created: {$carol['name']} (ID: {$carol['id']})\n";
foreach ($repo->findAll() as $u) {
    printf("  [%d] %-10s %s\n", $u['id'], $u['name'], $u['email']);
}
