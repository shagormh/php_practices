<?php
declare(strict_types=1);

function power(int $base, int $exp): float
{
    $result = 1.0;
    for ($i = 0; $i < abs($exp); $i++) $result *= $base;
    return $exp < 0 ? 1 / $result : $result;
}

function average(float ...$nums): float
{
    return array_sum($nums) / count($nums);
}

function median(array $nums): float
{
    sort($nums);
    $count = count($nums);
    $mid   = intdiv($count, 2);
    return $count % 2 === 0
        ? ($nums[$mid - 1] + $nums[$mid]) / 2
        : (float)$nums[$mid];
}

function isEven(int $n): bool { return $n % 2 === 0; }

function clamp(float $val, float $min, float $max): float
{
    return max($min, min($max, $val));
}

echo "power(2,10)           = " . power(2, 10) . "\n";
echo "average(10,20,30,40)  = " . average(10, 20, 30, 40) . "\n";
echo "median([3,1,4,1,5,9]) = " . median([3, 1, 4, 1, 5, 9]) . "\n";
echo "clamp(150, 0, 100)    = " . clamp(150, 0, 100) . "\n";
echo "isEven(7)             = " . var_export(isEven(7), true) . "\n";
