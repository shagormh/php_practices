<?php
/**
 * Level 4 – Solution 1: QueryBuilder (Builder Pattern)
 */

declare(strict_types=1);

class QueryBuilder
{
    private array  $columns  = ['*'];
    private array  $wheres   = [];
    private array  $orders   = [];
    private ?int   $limitVal = null;
    private ?int   $offsetVal = null;
    private array  $joins    = [];

    public function __construct(private readonly string $table) {}

    public function select(string ...$columns): static
    {
        $this->columns = $columns;
        return $this;
    }

    public function where(string $column, mixed $value, string $op = '='): static
    {
        $escaped = is_numeric($value) ? $value : "'{$value}'";
        $this->wheres[] = "{$column} {$op} {$escaped}";
        return $this;
    }

    public function whereIn(string $column, array $values): static
    {
        $list = implode(', ', array_map(fn($v) => "'{$v}'", $values));
        $this->wheres[] = "{$column} IN ({$list})";
        return $this;
    }

    public function orderBy(string $column, string $dir = 'ASC'): static
    {
        $this->orders[] = "{$column} " . strtoupper($dir);
        return $this;
    }

    public function limit(int $limit): static    { $this->limitVal  = $limit;  return $this; }
    public function offset(int $offset): static  { $this->offsetVal = $offset; return $this; }

    public function join(string $table, string $on): static
    {
        $this->joins[] = "JOIN {$table} ON {$on}";
        return $this;
    }

    public function build(): string
    {
        $sql  = "SELECT " . implode(', ', $this->columns);
        $sql .= " FROM {$this->table}";
        if (!empty($this->joins))  $sql .= " " . implode(' ', $this->joins);
        if (!empty($this->wheres)) $sql .= " WHERE " . implode(' AND ', $this->wheres);
        if (!empty($this->orders)) $sql .= " ORDER BY " . implode(', ', $this->orders);
        if ($this->limitVal  !== null) $sql .= " LIMIT {$this->limitVal}";
        if ($this->offsetVal !== null) $sql .= " OFFSET {$this->offsetVal}";
        return $sql;
    }

    public function __toString(): string { return $this->build(); }
}

// Tests
$q1 = (new QueryBuilder('users'))
    ->select('id', 'name', 'email')
    ->where('role', 'admin')
    ->where('active', 1)
    ->orderBy('name')
    ->limit(10)
    ->build();

$q2 = (new QueryBuilder('orders'))
    ->select('o.id', 'u.name', 'o.total')
    ->join('users u', 'u.id = o.user_id')
    ->where('o.status', 'pending')
    ->whereIn('o.payment_method', ['card', 'bkash'])
    ->orderBy('o.created_at', 'DESC')
    ->limit(20)
    ->offset(40)
    ->build();

$q3 = (new QueryBuilder('products'))
    ->where('price', 1000, '>=')
    ->where('stock', 0, '>')
    ->orderBy('price')
    ->build();

foreach ([$q1, $q2, $q3] as $i => $q) {
    echo "Query " . ($i + 1) . ":\n  $q\n\n";
}
