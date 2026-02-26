<?php
/**
 * Solution 3 – Temperature Converter Table
 */

declare(strict_types=1);

$temperatures = [0, 20, 37, 100];

$divider = "├────────────┼──────────────┼─────────────┤";

echo "┌────────────┬──────────────┬─────────────┐\n";
echo "│  Celsius   │  Fahrenheit  │   Kelvin    │\n";
echo $divider . "\n";

foreach ($temperatures as $celsius) {
    $fahrenheit = ($celsius * 9 / 5) + 32;
    $kelvin     = $celsius + 273.15;

    printf("│ %6d°C   │ %8.2f°F   │  %7.2fK  │\n",
        $celsius, $fahrenheit, $kelvin);
}

echo "└────────────┴──────────────┴─────────────┘\n";
