<?php
declare(strict_types=1);
for ($i = 1; $i <= 50; $i++) {
    echo match(true) {
        $i % 15 === 0 => "FizzBuzz",
        $i % 3  === 0 => "Fizz",
        $i % 5  === 0 => "Buzz",
        default       => (string)$i,
    } . "\n";
}
