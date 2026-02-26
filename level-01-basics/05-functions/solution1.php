<?php
declare(strict_types=1);

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    return preg_replace('/[\s-]+/', '-', $text);
}

function truncate(string $text, int $limit = 50, string $suffix = '...'): string
{
    return strlen($text) > $limit
        ? rtrim(substr($text, 0, $limit)) . $suffix
        : $text;
}

function titleCase(string $text): string
{
    $minorWords = ['a','an','the','and','but','or','for','in','on','at','to','by'];
    $words = explode(' ', strtolower($text));
    return implode(' ', array_map(
        fn($w, $i) => ($i === 0 || !in_array($w, $minorWords))
            ? ucfirst($w)
            : $w,
        $words,
        array_keys($words)
    ));
}

function wordCount(string $text): array
{
    $words = str_word_count($text, 1);
    return [
        'words'        => count($words),
        'chars'        => strlen(str_replace(' ', '', $text)),
        'unique_words' => count(array_unique(array_map('strtolower', $words))),
    ];
}

// Tests
echo slugify("Hello World PHP!") . "\n";          // hello-world-php
echo truncate("PHP is fantastic for web development.", 25) . "\n"; // PHP is fantastic for web...
echo titleCase("the quick brown fox jumps over the lazy dog") . "\n";
print_r(wordCount("The quick brown fox. The fox is quick."));
