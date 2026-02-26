<?php
/**
 * Operators Solution 2 – Bitwise Permission System
 */

declare(strict_types=1);

const READ   = 0b0001;   // 1
const WRITE  = 0b0010;   // 2
const DELETE = 0b0100;   // 4
const ADMIN  = 0b1000;   // 8

// Roles: combine with bitwise OR
$viewer = READ;
$editor = READ | WRITE;
$admin  = READ | WRITE | DELETE | ADMIN;

function hasPermission(int $role, int $permission): bool
{
    return ($role & $permission) === $permission;
}

function permLabel(int $perm): string
{
    return match($perm) {
        READ   => "READ",
        WRITE  => "WRITE",
        DELETE => "DELETE",
        ADMIN  => "ADMIN",
        default=> "UNKNOWN",
    };
}

echo "=== Permission Checks ===\n";
foreach (
    [
        ['viewer', $viewer, READ],
        ['viewer', $viewer, WRITE],
        ['editor', $editor, READ],
        ['editor', $editor, DELETE],
        ['admin',  $admin,  ADMIN],
    ] as [$roleName, $role, $perm]
) {
    $has = hasPermission($role, $perm) ? "✅" : "❌";
    printf("  %-6s has %-7s? %s\n", $roleName, permLabel($perm), $has);
}

// Add/Remove permission
echo "\n=== Modify Permissions ===\n";
echo "editor before: " . decbin($editor) . " (binary)\n";
$editor |= DELETE;           // grant DELETE
echo "after +DELETE: " . decbin($editor) . "\n";
$editor &= ~WRITE;           // revoke WRITE
echo "after -WRITE : " . decbin($editor) . "\n";
