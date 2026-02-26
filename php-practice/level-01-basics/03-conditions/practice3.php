<?php
/**
 * Conditions Practice 3 – E-commerce Shipping Price
 * Task: Calculate shipping cost based on destination and order amount.
 *
 * Rules:
 *   Zone A (Dhaka)     → Free if order >= 1000, else 50
 *   Zone B (Chittagong)→ Free if order >= 2000, else 100
 *   Zone C (Others)    → Free if order >= 3000, else 150
 *   Express shipping   → +100 BDT on top of base rate
 */

// TODO:
// Create a function: calculateShipping(string $zone, float $order, bool $express): float
// Test for:
//   Zone A, order 800,  express false → 50
//   Zone A, order 1200, express true  → 100
//   Zone B, order 1500, express false → 100
//   Zone C, order 5000, express true  → 100
