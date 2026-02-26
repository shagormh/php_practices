<?php
/**
 * Level 6 – REST API Development
 * A complete REST API for a "Books" resource.
 * Features: JWT-like auth, JSON responses, HTTP methods, status codes
 *
 * Run: php -S localhost:8888 api.php
 * Test with curl or Postman
 *
 * ENDPOINTS:
 *   POST   /api/login           – Get access token
 *   GET    /api/books           – List all books (auth required)
 *   GET    /api/books/{id}      – Get single book
 *   POST   /api/books           – Create book
 *   PUT    /api/books/{id}      – Update book
 *   DELETE /api/books/{id}      – Delete book
 *   GET    /api/books/search?q= – Search books
 */

declare(strict_types=1);

/* ── Bootstrap ───────────────────────────────────────────────────────── */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/* ── Helpers ─────────────────────────────────────────────────────────── */
function respond(mixed $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode([
        'status'  => $status,
        'success' => $status < 400,
        'data'    => $data,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function respondError(string $message, int $status = 400, array $errors = []): never
{
    http_response_code($status);
    echo json_encode([
        'status'  => $status,
        'success' => false,
        'message' => $message,
        'errors'  => $errors,
    ], JSON_PRETTY_PRINT);
    exit;
}

function getBody(): array
{
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

/* ── Simplified Token Auth ───────────────────────────────────────────── */
const SECRET_KEY = 'php-rest-secret-2024';

function generateToken(array $payload): string
{
    $header  = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload['exp'] = time() + 3600;   // 1 hour
    $claims  = base64_encode(json_encode($payload));
    $sig     = base64_encode(hash_hmac('sha256', "$header.$claims", SECRET_KEY, true));
    return "$header.$claims.$sig";
}

function verifyToken(string $token): ?array
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    [$header, $claims, $sig] = $parts;
    $expected = base64_encode(hash_hmac('sha256', "$header.$claims", SECRET_KEY, true));
    if (!hash_equals($expected, $sig)) return null;
    $payload = json_decode(base64_decode($claims), true);
    if ($payload['exp'] < time()) return null;
    return $payload;
}

function requireAuth(): array
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s(.+)$/', $header, $m)) {
        respondError('Missing or invalid Authorization header.', 401);
    }
    $payload = verifyToken($m[1]);
    if (!$payload) respondError('Token expired or invalid.', 401);
    return $payload;
}

/* ── In-Memory Book Store ────────────────────────────────────────────── */
$booksStore = [
    1 => ['id' => 1, 'title' => 'Clean Code',           'author' => 'Robert C. Martin', 'year' => 2008, 'genre' => 'Programming',  'price' => 45.99, 'stock' => 12],
    2 => ['id' => 2, 'title' => 'The Pragmatic Programmer','author'=>'David Thomas',      'year' => 1999, 'genre' => 'Programming',  'price' => 42.00, 'stock' => 8 ],
    3 => ['id' => 3, 'title' => 'Design Patterns',       'author' => 'Gang of Four',     'year' => 1994, 'genre' => 'Programming',  'price' => 55.00, 'stock' => 5 ],
    4 => ['id' => 4, 'title' => 'PHP 8 Objects',         'author' => 'Matt Zandstra',    'year' => 2021, 'genre' => 'PHP',          'price' => 38.50, 'stock' => 15],
    5 => ['id' => 5, 'title' => '1984',                  'author' => 'George Orwell',    'year' => 1949, 'genre' => 'Fiction',      'price' => 12.99, 'stock' => 30],
];
$nextId = 6;

function validateBook(array $data, bool $required = true): array
{
    $errors = [];
    if ($required && empty($data['title']))  $errors['title']  = 'Title is required';
    if ($required && empty($data['author'])) $errors['author'] = 'Author is required';
    if ($required && empty($data['year']))   $errors['year']   = 'Year is required';
    if (isset($data['year']) && (int)$data['year'] < 1000) $errors['year'] = 'Invalid year';
    if (isset($data['price']) && (float)$data['price'] < 0) $errors['price'] = 'Price cannot be negative';
    return $errors;
}

/* ── Router ──────────────────────────────────────────────────────────── */
$method = $_SERVER['REQUEST_METHOD'];
$raw    = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$uri    = rtrim($raw, '/') ?: '/';

// Match /api/books/{id}
preg_match('#^/api/books(?:/(\d+))?(?:/([a-z]+))?$#', $uri, $parts);
$id     = isset($parts[1]) ? (int)$parts[1] : null;
$sub    = $parts[2] ?? null;

/* ── Routes ──────────────────────────────────────────────────────────── */

// POST /api/login
if ($method === 'POST' && $uri === '/api/login') {
    $body = getBody();
    $users = [
        'admin'  => ['pass' => 'secret123', 'role' => 'admin'],
        'editor' => ['pass' => 'editor456', 'role' => 'editor'],
    ];
    $username = $body['username'] ?? '';
    $password = $body['password'] ?? '';
    if (!isset($users[$username]) || $users[$username]['pass'] !== $password) {
        respondError('Invalid credentials.', 401);
    }
    $token = generateToken(['user' => $username, 'role' => $users[$username]['role']]);
    respond(['token' => $token, 'type' => 'Bearer', 'expires_in' => 3600]);
}

// GET /api/books – list + search
if ($method === 'GET' && $uri === '/api/books') {
    requireAuth();
    $q      = strtolower($_GET['q']      ?? '');
    $genre  = strtolower($_GET['genre']  ?? '');
    $sort   = $_GET['sort']  ?? 'id';
    $order  = strtolower($_GET['order']  ?? 'asc') === 'desc' ? 'desc' : 'asc';
    $page   = max(1, (int)($_GET['page'] ?? 1));
    $limit  = min(50, max(1, (int)($_GET['limit'] ?? 10)));

    $books = array_values($booksStore);

    if ($q)     $books = array_filter($books, fn($b) => str_contains(strtolower($b['title']), $q) || str_contains(strtolower($b['author']), $q));
    if ($genre) $books = array_filter($books, fn($b) => strtolower($b['genre']) === $genre);

    usort($books, fn($a, $b) => $order === 'asc'
        ? ($a[$sort] ?? '') <=> ($b[$sort] ?? '')
        : ($b[$sort] ?? '') <=> ($a[$sort] ?? ''));

    $books     = array_values($books);
    $total     = count($books);
    $paginated = array_slice($books, ($page - 1) * $limit, $limit);

    respond([
        'books'      => $paginated,
        'pagination' => ['total' => $total, 'page' => $page, 'limit' => $limit, 'pages' => (int)ceil($total / $limit)],
    ]);
}

// GET /api/books/{id}
if ($method === 'GET' && $id !== null) {
    requireAuth();
    if (!isset($booksStore[$id])) respondError("Book #$id not found.", 404);
    respond($booksStore[$id]);
}

// POST /api/books – create
if ($method === 'POST' && $uri === '/api/books') {
    $auth = requireAuth();
    if ($auth['role'] !== 'admin') respondError('Admins only.', 403);
    $body   = getBody();
    $errors = validateBook($body);
    if (!empty($errors)) respondError('Validation failed.', 422, $errors);
    $book = [
        'id'     => $nextId++,
        'title'  => $body['title'],
        'author' => $body['author'],
        'year'   => (int)$body['year'],
        'genre'  => $body['genre']  ?? 'General',
        'price'  => (float)($body['price'] ?? 0),
        'stock'  => (int)($body['stock']   ?? 0),
    ];
    $booksStore[$book['id']] = $book;
    respond($book, 201);
}

// PUT /api/books/{id} – full update
if ($method === 'PUT' && $id !== null) {
    requireAuth();
    if (!isset($booksStore[$id])) respondError("Book #$id not found.", 404);
    $body   = getBody();
    $errors = validateBook($body);
    if (!empty($errors)) respondError('Validation failed.', 422, $errors);
    $booksStore[$id] = array_merge($booksStore[$id], [
        'title'  => $body['title'],
        'author' => $body['author'],
        'year'   => (int)$body['year'],
        'genre'  => $body['genre']  ?? $booksStore[$id]['genre'],
        'price'  => (float)($body['price'] ?? $booksStore[$id]['price']),
        'stock'  => (int)($body['stock']   ?? $booksStore[$id]['stock']),
    ]);
    respond($booksStore[$id]);
}

// PATCH /api/books/{id} – partial update
if ($method === 'PATCH' && $id !== null) {
    requireAuth();
    if (!isset($booksStore[$id])) respondError("Book #$id not found.", 404);
    $body = getBody();
    $booksStore[$id] = array_merge($booksStore[$id], array_intersect_key($body,
        array_flip(['title', 'author', 'year', 'genre', 'price', 'stock'])));
    respond($booksStore[$id]);
}

// DELETE /api/books/{id}
if ($method === 'DELETE' && $id !== null) {
    $auth = requireAuth();
    if ($auth['role'] !== 'admin') respondError('Admins only.', 403);
    if (!isset($booksStore[$id])) respondError("Book #$id not found.", 404);
    $book = $booksStore[$id];
    unset($booksStore[$id]);
    respond(['deleted' => true, 'book' => $book]);
}

// GET /api/health
if ($method === 'GET' && $uri === '/api/health') {
    respond(['status' => 'ok', 'version' => '1.0', 'time' => date('Y-m-d H:i:s'), 'php' => PHP_VERSION]);
}

respondError('Route not found.', 404);
