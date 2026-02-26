<?php
/**
 * Operators Solution 1 – BMI Calculator
 */

declare(strict_types=1);

$weight = 70.0;    // kg
$height = 1.75;    // meters
$bmi    = $weight / ($height ** 2);

$category = match(true) {
    $bmi < 18.5 => "Underweight 🔵",
    $bmi < 25.0 => "Normal ✅",
    $bmi < 30.0 => "Overweight ⚠️",
    default     => "Obese 🔴",
};

$line = str_repeat("─", 32);
echo $line . "\n";
printf(" %-14s : %.1f kg\n",  "Weight",  $weight);
printf(" %-14s : %.2f m\n",   "Height",  $height);
printf(" %-14s : %.2f\n",     "BMI",     $bmi);
printf(" %-14s : %s\n",       "Status",  $category);
echo $line . "\n";
