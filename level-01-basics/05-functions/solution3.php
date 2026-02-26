<?php
declare(strict_types=1);

function myMap(array $arr, callable $fn): array
{
    $result = [];
    foreach ($arr as $key => $val) $result[$key] = $fn($val);
    return $result;
}

function myFilter(array $arr, callable $fn): array
{
    $result = [];
    foreach ($arr as $key => $val) {
        if ($fn($val)) $result[$key] = $val;
    }
    return $result;
}

function myReduce(array $arr, callable $fn, mixed $initial = null): mixed
{
    $carry = $initial;
    foreach ($arr as $val) $carry = $fn($carry, $val);
    return $carry;
}

function flatten(array $arr): array
{
    $flat = [];
    foreach ($arr as $item) {
        if (is_array($item)) {
            foreach ($item as $v) $flat[] = $v;
        } else {
            $flat[] = $item;
        }
    }
    return $flat;
}

function groupBy(array $arr, string $key): array
{
    $groups = [];
    foreach ($arr as $item) {
        $groups[$item[$key]][] = $item;
    }
    return $groups;
}

// Tests
$nums = [1, 2, 3, 4, 5, 6];
print_r(myMap($nums, fn($n) => $n * $n));
print_r(myFilter($nums, fn($n) => $n % 2 === 0));
echo myReduce($nums, fn($carry, $n) => $carry + $n, 0) . "\n";   // 21
print_r(flatten([[1, 2], [3, 4], 5]));

$students = [
    ['name' => 'Alice', 'dept' => 'CSE'],
    ['name' => 'Bob',   'dept' => 'EEE'],
    ['name' => 'Carol', 'dept' => 'CSE'],
];
print_r(groupBy($students, 'dept'));
