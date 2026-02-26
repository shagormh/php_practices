<?php
declare(strict_types=1);

function isLeapYear(int $year): bool
{
    return ($year % 400 === 0) || ($year % 4 === 0 && $year % 100 !== 0);
}

foreach ([1900, 2000, 2024, 2025, 2100] as $year) {
    $symbol = isLeapYear($year) ? "✓ Leap Year" : "✗ Not a Leap Year";
    printf("%d: %s\n", $year, $symbol);
}
// Output:
// 1900: ✗ Not a Leap Year
// 2000: ✓ Leap Year
// 2024: ✓ Leap Year
// 2025: ✗ Not a Leap Year
// 2100: ✗ Not a Leap Year
