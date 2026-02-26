<?php
/**
 * Mini Project – Expense Tracker CLI App
 * Combines all 7 levels:
 *   - OOP with traits and enums (Level 3, 4)
 *   - File storage as JSON (Level 2)
 *   - Exception handling (Level 3)
 *   - Input validation (Level 7)
 *   - Functional utilities (Level 1)
 *
 * Run: php expense-tracker.php
 */

declare(strict_types=1);

/* ── Enums ─────────────────────────────────────────────────────────── */
enum Category: string
{
    case Food       = 'food';
    case Transport  = 'transport';
    case Utilities  = 'utilities';
    case Housing    = 'housing';
    case Health     = 'health';
    case Education  = 'education';
    case Shopping   = 'shopping';
    case Other      = 'other';

    public function emoji(): string {
        return match($this) {
            self::Food       => '🍔',
            self::Transport  => '🚌',
            self::Utilities  => '💡',
            self::Housing    => '🏠',
            self::Health     => '🏥',
            self::Education  => '📚',
            self::Shopping   => '🛍️',
            self::Other      => '📦',
        };
    }

    public static function fromString(string $val): self
    {
        foreach (self::cases() as $case) {
            if ($case->value === strtolower($val)) return $case;
        }
        throw new \ValueError("Invalid category: $val");
    }
}

/* ── Expense Model ─────────────────────────────────────────────────── */
class Expense
{
    public function __construct(
        public readonly int      $id,
        public readonly string   $description,
        public readonly float    $amount,
        public readonly Category $category,
        public readonly string   $date,
        public readonly string   $note = '',
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id:          $data['id'],
            description: $data['description'],
            amount:      (float)$data['amount'],
            category:    Category::fromString($data['category']),
            date:        $data['date'],
            note:        $data['note'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'description' => $this->description,
            'amount'      => $this->amount,
            'category'    => $this->category->value,
            'date'        => $this->date,
            'note'        => $this->note,
        ];
    }
}

/* ── Expense Repository ────────────────────────────────────────────── */
class ExpenseRepository
{
    private array $data;
    private int   $nextId;

    public function __construct(private readonly string $filePath)
    {
        $this->load();
    }

    private function load(): void
    {
        if (!file_exists($this->filePath)) {
            $this->data   = [];
            $this->nextId = 1;
            return;
        }
        $raw = json_decode(file_get_contents($this->filePath), true) ?? [];
        $this->data   = $raw['expenses'] ?? [];
        $this->nextId = $raw['next_id']  ?? (count($this->data) + 1);
    }

    private function persist(): void
    {
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($this->filePath, json_encode([
            'next_id'  => $this->nextId,
            'expenses' => $this->data,
        ], JSON_PRETTY_PRINT));
    }

    public function add(string $desc, float $amount, Category $cat, string $date, string $note = ''): Expense
    {
        $expense = new Expense($this->nextId++, $desc, $amount, $cat, $date, $note);
        $this->data[] = $expense->toArray();
        $this->persist();
        return $expense;
    }

    /** @return Expense[] */
    public function getAll(?string $month = null): array
    {
        $rows = $this->data;
        if ($month) {
            $rows = array_filter($rows, fn($r) => str_starts_with($r['date'], $month));
        }
        return array_map(fn($r) => Expense::fromArray($r), array_values($rows));
    }

    public function deleteById(int $id): bool
    {
        $before     = count($this->data);
        $this->data = array_values(array_filter($this->data, fn($r) => $r['id'] !== $id));
        if (count($this->data) < $before) { $this->persist(); return true; }
        return false;
    }

    public function clear(): void
    {
        $this->data   = [];
        $this->nextId = 1;
        $this->persist();
    }
}

/* ── Report Engine ─────────────────────────────────────────────────── */
class ExpenseReport
{
    /** @param Expense[] $expenses */
    public static function summary(array $expenses): void
    {
        $total = array_sum(array_map(fn($e) => $e->amount, $expenses));
        $byCategory = [];
        foreach ($expenses as $e) {
            $byCategory[$e->category->value] = ($byCategory[$e->category->value] ?? 0) + $e->amount;
        }
        arsort($byCategory);

        $border = str_repeat("═", 52);
        echo "\n╔{$border}╗\n";
        echo "║" . str_pad("  Expense Report", 52) . "║\n";
        echo "╠{$border}╣\n";
        echo "║" . str_pad("  Total Expenses: BDT " . number_format($total, 2), 52) . "║\n";
        echo "║" . str_pad("  No. of Entries: " . count($expenses), 52) . "║\n";
        echo "╠{$border}╣\n";
        echo "║  By Category:                                      ║\n";

        foreach ($byCategory as $cat => $amount) {
            $catEnum = Category::fromString($cat);
            $percent = $total > 0 ? round($amount / $total * 100) : 0;
            $bar     = str_repeat("█", (int)($percent / 5));
            $line    = sprintf("  %s %-12s BDT %8.2f  %s %d%%",
                $catEnum->emoji(), $cat, $amount, str_pad($bar, 14), $percent);
            echo "║" . str_pad($line, 52) . "║\n";
        }
        echo "╚{$border}╝\n";
    }

    /** @param Expense[] $expenses */
    public static function table(array $expenses): void
    {
        echo "\n";
        printf("%-4s %-12s %-22s %-12s %-10s\n",
            "ID", "Date", "Description", "Category", "Amount");
        echo str_repeat("─", 64) . "\n";
        foreach ($expenses as $e) {
            printf("%-4d %-12s %-22s %s %-10s %-10s\n",
                $e->id,
                $e->date,
                substr($e->description, 0, 22),
                $e->category->emoji(),
                $e->category->value,
                "BDT " . number_format($e->amount, 2)
            );
        }
        echo str_repeat("─", 64) . "\n";
    }
}

/* ── Demo / Test Run ─────────────────────────────────────────────────── */
$repo = new ExpenseRepository(__DIR__ . '/storage/expenses.json');
$repo->clear();   // fresh start for demo

echo "╔══════════════════════════════════╗\n";
echo "║     💰 PHP Expense Tracker       ║\n";
echo "╚══════════════════════════════════╝\n\n";

// Add sample data
$entries = [
    ["Lunch at office",     350.00,  Category::Food,       "2026-02-01"],
    ["Bus fare",            40.00,   Category::Transport,  "2026-02-01"],
    ["Electric bill",       1500.00, Category::Utilities,  "2026-02-03"],
    ["PHP book",            450.00,  Category::Education,  "2026-02-05"],
    ["Doctor visit",        800.00,  Category::Health,     "2026-02-07"],
    ["Dinner with family",  1200.00, Category::Food,       "2026-02-10"],
    ["Uber ride",           350.00,  Category::Transport,  "2026-02-12"],
    ["Grocery shopping",    2500.00, Category::Shopping,   "2026-02-15"],
    ["Rent",                15000.00,Category::Housing,    "2026-02-01"],
    ["Online course",       3000.00, Category::Education,  "2026-02-20"],
    ["Snacks",              180.00,  Category::Food,       "2026-02-22"],
    ["Medicine",            450.00,  Category::Health,     "2026-02-24"],
];

echo "Adding expenses...\n";
foreach ($entries as [$desc, $amount, $cat, $date]) {
    $e = $repo->add($desc, $amount, $cat, $date);
    printf("  ✅ Added [#%d] %s – BDT %s\n", $e->id, $e->description, number_format($e->amount, 2));
}

// Display all
$all = $repo->getAll();
echo "\n=== All Expenses ===";
ExpenseReport::table($all);

// February report
$feb = $repo->getAll('2026-02');
ExpenseReport::summary($feb);

// Delete one
echo "\nDeleting expense #6...\n";
$deleted = $repo->deleteById(6);
echo $deleted ? "✅ Deleted!\n" : "❌ Not found.\n";

// Category breakdown (food only)
echo "\n=== Food Expenses Only ===";
$foodOnly = array_filter($all, fn($e) => $e->category === Category::Food);
ExpenseReport::table(array_values($foodOnly));

echo "\n📁 Data saved to: " . realpath(__DIR__ . '/storage/expenses.json') . "\n";
