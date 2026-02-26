<?php
declare(strict_types=1);
$marks = 82;

['grade' => $grade, 'gpa' => $gpa] = match(true) {
    $marks >= 90 => ['grade' => 'A+', 'gpa' => 4.00],
    $marks >= 80 => ['grade' => 'A',  'gpa' => 3.75],
    $marks >= 70 => ['grade' => 'B',  'gpa' => 3.25],
    $marks >= 60 => ['grade' => 'C',  'gpa' => 2.75],
    $marks >= 50 => ['grade' => 'D',  'gpa' => 2.25],
    default      => ['grade' => 'F',  'gpa' => 0.00],
};

printf("Marks  : %d\n", $marks);
printf("Grade  : %s\n", $grade);
printf("GPA    : %.2f\n", $gpa);
printf("Status : %s\n", $grade !== 'F' ? 'Passed ✅' : 'Failed ❌');
