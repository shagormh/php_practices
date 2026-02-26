<?php
/**
 * Solution 1 – Student Profile Card
 */

declare(strict_types=1);

$studentName = "Alice Rahman";
$studentId   = 220101;
$cgpa        = 3.75;
$isActive    = true;
$department  = "CSE";      // change to null to see N/A

$line = str_repeat("─", 31);

echo "┌{$line}┐\n";
echo "│       Student Profile Card      │\n";
echo "├{$line}┤\n";
printf("│ %-8s : %-19s │\n", "Name",   $studentName);
printf("│ %-8s : %-19s │\n", "ID",     $studentId);
printf("│ %-8s : %-19s │\n", "CGPA",   number_format($cgpa, 2));
printf("│ %-8s : %-19s │\n", "Active", $isActive ? 'Yes' : 'No');
printf("│ %-8s : %-19s │\n", "Dept",   $department ?? 'N/A');
echo "└{$line}┘\n";

/* Expected Output:
┌───────────────────────────────┐
│       Student Profile Card      │
├───────────────────────────────┤
│ Name     : Alice Rahman        │
│ ID       : 220101              │
│ CGPA     : 3.75                │
│ Active   : Yes                 │
│ Dept     : CSE                 │
└───────────────────────────────┘
*/
