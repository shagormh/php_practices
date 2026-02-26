<?php
/**
 * Operators Practice 2 – Bitwise Permissions
 * Task: Implement a simple role-based permission system using bitwise operators.
 */

// Permission constants using powers of 2
// const READ    = 0b0001;   // 1
// const WRITE   = 0b0010;   // 2
// const DELETE  = 0b0100;   // 4
// const ADMIN   = 0b1000;   // 8

// TODO:
// 1. Define 4 permission constants
// 2. Create roles: viewer=READ, editor=READ|WRITE, admin=READ|WRITE|DELETE|ADMIN
// 3. Create a function hasPermission(int $role, int $permission): bool
// 4. Test:
//    editor has READ?   → true
//    editor has DELETE? → false
//    admin  has all?    → true
// 5. Add/remove a permission using |= and &= ~
