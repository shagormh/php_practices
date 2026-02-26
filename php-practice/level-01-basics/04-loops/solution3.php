<?php
declare(strict_types=1);

$n = 5;
for ($row = 1; $row <= $n; $row++) {
    echo str_repeat(" ", $n - $row);   // leading spaces
    for ($col = 1; $col <= $row; $col++) {
        echo $col . ($col < $row ? " " : "");
    }
    echo "\n";
}
// Output:
//     1
//    1 2
//   1 2 3
//  1 2 3 4
// 1 2 3 4 5
