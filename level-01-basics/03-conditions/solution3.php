<?php
declare(strict_types=1);

function calculateShipping(string $zone, float $order, bool $express): float
{
    $freeThreshold = match(strtoupper($zone)) {
        'A'     => 1000,
        'B'     => 2000,
        default => 3000,
    };
    $baseRate = match(strtoupper($zone)) {
        'A'     => 50,
        'B'     => 100,
        default => 150,
    };

    $shipping = $order >= $freeThreshold ? 0 : $baseRate;
    if ($express) $shipping += 100;

    return $shipping;
}

$tests = [
    ['A', 800,  false, 50],
    ['A', 1200, true,  100],
    ['B', 1500, false, 100],
    ['C', 5000, true,  100],
];

printf("%-5s %-8s %-8s %-10s %-8s\n", "Zone","Order","Express","Shipping","Expected");
echo str_repeat("─",45) . "\n";
foreach ($tests as [$zone, $order, $express, $expected]) {
    $cost   = calculateShipping($zone, $order, $express);
    $ok     = $cost === (float)$expected ? "✅" : "❌";
    printf("  %-4s %-8s %-8s BDT %-6.0f %s\n",
        $zone, $order, $express ? 'Yes':'No', $cost, $ok);
}
