<?php
/**
 * Level 3 – OOP (Object-Oriented Programming)
 * PHP 8+ features: constructor promotion, readonly, enums, intersection types
 *
 * Covers: classes, interfaces, abstract classes, inheritance,
 *         traits, static methods, constructor promotion
 * Run: php example.php
 */

declare(strict_types=1);

/* ── Enum (PHP 8.1+) ─────────────────────────────────────────────────── */
enum UserRole: string
{
    case Admin  = 'admin';
    case Editor = 'editor';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match($this) {
            self::Admin  => '🛡️ Admin',
            self::Editor => '✏️ Editor',
            self::Viewer => '👁️ Viewer',
        };
    }

    public function canDelete(): bool
    {
        return $this === self::Admin;
    }
}

/* ── Interface ───────────────────────────────────────────────────────── */
interface Timestampable
{
    public function getCreatedAt(): \DateTimeImmutable;
    public function getUpdatedAt(): \DateTimeImmutable;
}

interface Serializable
{
    public function toArray(): array;
    public function toJson(): string;
}

/* ── Abstract Base Class ─────────────────────────────────────────────── */
abstract class BaseModel implements Timestampable, Serializable
{
    // Readonly properties (PHP 8.1+)
    public readonly \DateTimeImmutable $createdAt;
    public \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    protected function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    abstract public function validate(): bool;

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}

/* ── Trait ───────────────────────────────────────────────────────────── */
trait HasSlug
{
    protected string $slug = '';

    public function generateSlug(string $text): static
    {
        $this->slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
        return $this;
    }

    public function getSlug(): string { return $this->slug; }
}

/* ── User Class (Constructor Promotion) ──────────────────────────────── */
class User extends BaseModel
{
    use HasSlug;

    private static int $count = 0;

    public function __construct(
        // Constructor property promotion (PHP 8.0+)
        private string   $name,
        private string   $email,
        private UserRole $role = UserRole::Viewer,
        private bool     $active = true,
    ) {
        parent::__construct();
        self::$count++;
        $this->generateSlug($name);
    }

    /* ── Getters ─────────────────────────────────────────────────────── */
    public function getName(): string    { return $this->name; }
    public function getEmail(): string   { return $this->email; }
    public function getRole(): UserRole  { return $this->role; }
    public function isActive(): bool     { return $this->active; }

    /* ── Methods ─────────────────────────────────────────────────────── */
    public function promote(UserRole $newRole): static
    {
        $this->role = $newRole;
        $this->touch();
        return $this;
    }

    public function deactivate(): static
    {
        $this->active = false;
        $this->touch();
        return $this;
    }

    public function validate(): bool
    {
        return !empty($this->name)
            && filter_var($this->email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function toArray(): array
    {
        return [
            'name'       => $this->name,
            'email'      => $this->email,
            'slug'       => $this->slug,
            'role'       => $this->role->value,
            'role_label' => $this->role->label(),
            'active'     => $this->active,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }

    public static function getCount(): int { return self::$count; }

    public function __toString(): string
    {
        return "[{$this->role->label()}] {$this->name} <{$this->email}>";
    }
}

/* ── Admin (Inheritance) ─────────────────────────────────────────────── */
class AdminUser extends User
{
    private array $permissions;

    public function __construct(string $name, string $email, array $permissions = [])
    {
        parent::__construct($name, $email, UserRole::Admin);
        $this->permissions = $permissions;
    }

    public function hasPermission(string $perm): bool
    {
        return in_array($perm, $this->permissions, true);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), ['permissions' => $this->permissions]);
    }
}

/* ── Demo ────────────────────────────────────────────────────────────── */
echo "=== OOP Demo ===\n\n";

$alice = new User("Alice Rahman", "alice@example.com", UserRole::Editor);
$bob   = new User("Bob Hossain", "bob@example.com");
$admin = new AdminUser("Super Admin", "admin@example.com",
    permissions: ['create', 'read', 'update', 'delete']);

// Method chaining
$bob->promote(UserRole::Editor);

echo "Users:\n";
foreach ([$alice, $bob, $admin] as $user) {
    echo "  $user\n";
    echo "    Valid : " . ($user->validate() ? 'Yes' : 'No') . "\n";
    echo "    Slug  : " . $user->getSlug() . "\n";
}

echo "\nTotal users created: " . User::getCount() . "\n";
echo "\nAdmin has 'delete': " . ($admin->hasPermission('delete') ? 'Yes' : 'No') . "\n";
echo "Admin can delete (enum): " . ($admin->getRole()->canDelete() ? 'Yes' : 'No') . "\n";

echo "\nUser as JSON:\n";
echo $alice->toJson() . "\n";

// Enum features
echo "\nAll Roles:\n";
foreach (UserRole::cases() as $role) {
    echo "  {$role->label()} (value: '{$role->value}')\n";
}
