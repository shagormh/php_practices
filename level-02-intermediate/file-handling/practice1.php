<?php
/**
 * Level 2 – File Handling Practice: Simple Notes Manager CLI
 * Stores notes in a JSON file. Run: php practice1.php
 */

declare(strict_types=1);

const NOTES_FILE = __DIR__ . '/storage/notes.json';

function loadNotes(): array
{
    if (!file_exists(NOTES_FILE)) return [];
    return json_decode(file_get_contents(NOTES_FILE), true) ?? [];
}

function saveNotes(array $notes): void
{
    $dir = dirname(NOTES_FILE);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents(NOTES_FILE, json_encode($notes, JSON_PRETTY_PRINT));
}

function addNote(string $title, string $body): array
{
    $notes   = loadNotes();
    $notes[] = [
        'id'         => count($notes) + 1,
        'title'      => $title,
        'body'       => $body,
        'created_at' => date('Y-m-d H:i:s'),
    ];
    saveNotes($notes);
    return end($notes);
}

function deleteNote(int $id): bool
{
    $notes  = loadNotes();
    $before = count($notes);
    $notes  = array_values(array_filter($notes, fn($n) => $n['id'] !== $id));
    if (count($notes) < $before) {
        saveNotes($notes);
        return true;
    }
    return false;
}

function listNotes(): void
{
    $notes = loadNotes();
    if (empty($notes)) {
        echo "  (no notes)\n";
        return;
    }
    foreach ($notes as $note) {
        printf("  [%d] %s\n      %s\n      📅 %s\n\n",
            $note['id'], $note['title'], $note['body'], $note['created_at']);
    }
}

// ── Demo Run ─────────────────────────────────────────────────────────────
echo "=== Adding Notes ===\n";
$n1 = addNote("Buy Groceries",    "Milk, Eggs, Bread, Rice");
$n2 = addNote("PHP Practice",     "Finish Level 2 by Friday");
$n3 = addNote("Call Doctor",      "Appointment at 3pm Thursday");
echo "Added note: [{$n1['id']}] {$n1['title']}\n";
echo "Added note: [{$n2['id']}] {$n2['title']}\n";
echo "Added note: [{$n3['id']}] {$n3['title']}\n";

echo "\n=== All Notes ===\n";
listNotes();

echo "\n=== Deleting Note ID 2 ===\n";
$deleted = deleteNote(2);
echo $deleted ? "Deleted!\n" : "Not found.\n";

echo "\n=== Remaining Notes ===\n";
listNotes();

echo "Notes stored at: " . NOTES_FILE . "\n";
