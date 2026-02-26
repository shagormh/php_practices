<?php
/**
 * Operators Practice 3 – Loan EMI Calculator
 * Task: Calculate monthly EMI for a loan.
 * Formula: EMI = [P × r × (1+r)^n] / [(1+r)^n - 1]
 * Where: P = Principal, r = monthly interest rate, n = months
 */

// TODO:
// $principal   = 500000   (BDT)
// $annualRate   = 12      (%)
// $years        = 5
//
// Calculate:
// $monthlyRate = $annualRate / 12 / 100
// $months      = $years * 12
// $emi         = formula above
// $totalPaid   = $emi * $months
// $interest    = $totalPaid - $principal
//
// Output:
// ─────────────────────────────────
//  Loan Amount    : BDT 5,00,000
//  Interest Rate  : 12% per year
//  Duration       : 5 years (60 months)
//  Monthly EMI    : BDT 11,122.22
//  Total Payment  : BDT 6,67,333.20
//  Total Interest : BDT 1,67,333.20
// ─────────────────────────────────
