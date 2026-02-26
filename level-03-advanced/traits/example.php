<?php
/**
 * Level 3 – Traits
 * Traits allow code reuse in multiple classes without inheritance.
 * PHP 8+ features: typed properties in traits, readonly
 *
 * Run: php example.php
 */

declare(strict_types=1);

/* ── Trait 1: SoftDelete ─────────────────────────────────────────────── */
trait SoftDelete
{
    private ?string $deletedAt = null;

    public function softDelete(): static
    {
        $this->deletedAt = date('Y-m-d H:i:s');
        return $this;
    }

    public function restore(): static
    {
        $this->deletedAt = null;
        return $this;
    }

    public function isDeleted(): bool  { return $this->deletedAt !== null; }
    public function getDeletedAt(): ?string { return $this->deletedAt; }
}

/* ── Trait 2: Timestamps ─────────────────────────────────────────────── */
trait Timestamps
{
    private string $createdAt;
    private string $updatedAt;

    public function initTimestamps(): void
    {
        $this->createdAt = date('Y-m-d H:i:s');
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function touchTimestamp(): void
    {
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): string { return $this->updatedAt; }
}

/* ── Trait 3: Logging ────────────────────────────────────────────────── */
trait Loggable
{
    private array $logs = [];

    public function log(string $level, string $message): void
    {
        $this->logs[] = [
            'time'    => date('H:i:s'),
            'level'   => strtoupper($level),
            'message' => $message,
        ];
    }

    public function getLogs(): array  { return $this->logs; }
    public function clearLogs(): void { $this->logs = []; }
    public function printLogs(): void {
        foreach ($this->logs as $entry) {
            printf("[%s] %-6s %s\n", $entry['time'], $entry['level'], $entry['message']);
        }
    }
}

/* ── Trait 4: Validation ─────────────────────────────────────────────── */
trait Validatable
{
    private array $validationErrors = [];

    abstract protected function rules(): array;

    public function validate(array $data): bool
    {
        $this->validationErrors = [];

        foreach ($this->rules() as $field => $rule) {
            $value = $data[$field] ?? null;

            if (str_contains($rule, 'required') && empty($value)) {
                $this->validationErrors[$field] = "$field is required";
            }
            if (str_contains($rule, 'email') && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->validationErrors[$field] = "$field must be a valid email";
            }
            if (preg_match('/min:(\d+)/', $rule, $m) && strlen((string)$value) < (int)$m[1]) {
                $this->validationErrors[$field] = "$field must be at least {$m[1]} characters";
            }
        }

        return empty($this->validationErrors);
    }

    public function getErrors(): array  { return $this->validationErrors; }
    public function hasErrors(): bool   { return !empty($this->validationErrors); }
}

/* ── Trait 5: Observable (EventEmitter-lite) ─────────────────────────── */
trait Observable
{
    private array $listeners = [];

    public function on(string $event, callable $callback): void
    {
        $this->listeners[$event][] = $callback;
    }

    public function emit(string $event, mixed ...$args): void
    {
        foreach ($this->listeners[$event] ?? [] as $cb) {
            $cb(...$args);
        }
    }
}

/* ── Classes using Traits ─────────────────────────────────────────────── */
class Post
{
    use SoftDelete, Timestamps, Loggable, Observable;

    public function __construct(
        private string $title,
        private string $content,
    ) {
        $this->initTimestamps();
        $this->log('info', "Post '{$title}' created");
        $this->emit('created', $this);
    }

    public function update(string $title, string $content): static
    {
        $this->title   = $title;
        $this->content = $content;
        $this->touchTimestamp();
        $this->log('info', "Post updated: '{$title}'");
        $this->emit('updated', $this);
        return $this;
    }

    public function getTitle(): string   { return $this->title; }
    public function getContent(): string { return $this->content; }
}

class Form
{
    use Validatable;

    protected function rules(): array
    {
        return [
            'name'  => 'required|min:2',
            'email' => 'required|email',
        ];
    }
}

/* ── Demo ────────────────────────────────────────────────────────────── */
echo "=== Trait Demo ===\n\n";

$post = new Post("Hello PHP", "Learning traits in PHP 8");

// Observable – react to events
$post->on('created', fn($p) => print("  📢 [Event] Post created: '{$p->getTitle()}'\n"));
$post->on('updated', fn($p) => print("  📢 [Event] Post updated: '{$p->getTitle()}'\n"));

// Re-emit manually (in practice events fire in constructor/update)
$post->emit('created', $post);

$post->update("Hello PHP 8 Traits", "Mastering traits!");

echo "\nLogs:\n";
$post->printLogs();

echo "\nSoftDelete:\n";
echo "  Is deleted  : " . ($post->isDeleted() ? 'Yes' : 'No') . "\n";
$post->softDelete();
echo "  After delete: " . ($post->isDeleted() ? 'Yes' : 'No') . "\n";
$post->restore();
echo "  After restore: " . ($post->isDeleted() ? 'Yes' : 'No') . "\n";

echo "\n=== Validation Trait ===\n";
$form = new Form();

$valid = $form->validate(['name' => 'A', 'email' => 'not-email']);
echo "Valid: " . ($valid ? 'Yes' : 'No') . "\n";
foreach ($form->getErrors() as $field => $error) {
    echo "  ❌ $field: $error\n";
}

$valid2 = $form->validate(['name' => 'Alice', 'email' => 'alice@example.com']);
echo "Valid: " . ($valid2 ? 'Yes ✅' : 'No') . "\n";
