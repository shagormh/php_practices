<?php
/**
 * Solution 2 – Product Pricing Display
 */

declare(strict_types=1);

$productName = "Wireless Headphones";
$price       = 3500.50;
$discount    = 15;
$inStock     = true;

$discountAmount = $price * $discount / 100;
$finalPrice     = $price - $discountAmount;
$line           = str_repeat("─", 40);

echo $line . "\n";
printf(" %-9s : %s\n",     "Product",  $productName);
printf(" %-9s : BDT %s\n", "Price",    number_format($price, 2));
printf(" %-9s : %d%%  (-BDT %s)\n",
    "Discount", $discount, number_format($discountAmount, 2));
printf(" %-9s : BDT %s\n", "Final",    number_format($finalPrice, 2));
printf(" %-9s : %s\n",     "Status",   $inStock ? 'In Stock' : 'Out of Stock');
echo $line . "\n";

/* Expected Output:
────────────────────────────────────────
 Product   : Wireless Headphones
 Price     : BDT 3,500.50
 Discount  : 15%  (-BDT 525.08)
 Final     : BDT 2,975.43
 Status    : In Stock
────────────────────────────────────────
*/
