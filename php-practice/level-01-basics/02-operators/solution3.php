<?php
/**
 * Operators Solution 3 – Loan EMI Calculator
 */

declare(strict_types=1);

$principal  = 500000.0;
$annualRate = 12.0;
$years      = 5;

$monthlyRate = $annualRate / 12 / 100;
$months      = $years * 12;

// EMI formula
$emi = ($principal * $monthlyRate * (1 + $monthlyRate) ** $months)
     / ((1 + $monthlyRate) ** $months - 1);

$totalPaid = $emi * $months;
$interest  = $totalPaid - $principal;

$line = str_repeat("─", 40);
echo $line . "\n";
printf(" %-18s : BDT %s\n",  "Loan Amount",    number_format($principal, 2));
printf(" %-18s : %.0f%% per year\n", "Interest Rate", $annualRate);
printf(" %-18s : %d years (%d months)\n", "Duration", $years, $months);
printf(" %-18s : BDT %s\n",  "Monthly EMI",    number_format($emi, 2));
printf(" %-18s : BDT %s\n",  "Total Payment",  number_format($totalPaid, 2));
printf(" %-18s : BDT %s\n",  "Total Interest", number_format($interest, 2));
echo $line . "\n";
