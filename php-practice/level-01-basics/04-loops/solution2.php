<?php
declare(strict_types=1);

function isPrime(int $n): bool
{
    if ($n < 2) return false;
    for ($i = 2; $i <= (int)sqrt($n); $i++) {
        if ($n % $i === 0) return false;
    }
    return true;
}

$primes = array_filter(range(2, 100), 'isPrime');
echo "Primes between 1-100:\n";
echo implode(', ', $primes) . "\n";
echo "Count: " . count($primes) . "\n";
