<?php
/**
 * Level 5 – MVC Architecture
 * A clean, minimal MVC framework built from scratch.
 *
 * Structure:
 *   app/
 *     Controllers/ – Handle HTTP logic
 *     Models/      – Data layer
 *     Views/       – Templates
 *   core/
 *     Router.php   – URL dispatcher
 *     Controller.php – Base controller
 *     Model.php    – Base model
 *     View.php     – Template renderer
 *   public/
 *     index.php    – Single entry point
 *
 * Run: php -S localhost:8080 public/index.php
 */

declare(strict_types=1);

/* ═══════════════════════════════════════════════════════════════════
 * CORE FRAMEWORK
 * ═══════════════════════════════════════════════════════════════════ */

class Request2
{
    public readonly string $method;
    public readonly string $uri;
    public readonly array  $query;
    public readonly array  $body;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $rawUri       = $_SERVER['REQUEST_URI'] ?? '/';
        $this->uri    = strtok($rawUri, '?') ?: '/';
        $this->query  = $_GET;
        $this->body   = $_POST;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function isPost(): bool { return $this->method === 'POST'; }
}

class Response2
{
    private int    $statusCode = 200;
    private array  $headers    = ['Content-Type' => 'text/html; charset=UTF-8'];
    private string $body       = '';

    public function status(int $code): static { $this->statusCode = $code; return $this; }

    public function header(string $key, string $value): static
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function json(mixed $data, int $status = 200): static
    {
        $this->statusCode = $status;
        $this->headers['Content-Type'] = 'application/json';
        $this->body = json_encode($data, JSON_PRETTY_PRINT);
        return $this;
    }

    public function html(string $html): static { $this->body = $html; return $this; }

    public function redirect(string $to): static
    {
        $this->statusCode = 302;
        $this->headers['Location'] = $to;
        return $this;
    }

    public function send(): never
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
        echo $this->body;
        exit;
    }
}

class View2
{
    private static string $viewPath = __DIR__ . '/views';

    public static function setPath(string $path): void { self::$viewPath = $path; }

    public static function render(string $name, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        $file = self::$viewPath . '/' . str_replace('.', '/', $name) . '.php';
        if (file_exists($file)) {
            require $file;
        } else {
            // Render inline for demo
            echo $data['__inline'] ?? "View [$name] not found.";
        }
        return ob_get_clean();
    }
}

class Router2
{
    private array $routes = [];

    public function register(string $method, string $path, callable $handler): void
    {
        $this->routes[] = compact('method', 'path', 'handler');
    }

    public function get(string $path, callable $handler): void  { $this->register('GET',  $path, $handler); }
    public function post(string $path, callable $handler): void { $this->register('POST', $path, $handler); }

    public function dispatch(Request2 $req, Response2 $res): mixed
    {
        foreach ($this->routes as $route) {
            $params = $this->match($route['method'], $route['path'], $req->method, $req->uri);
            if ($params !== null) {
                return ($route['handler'])($req, $res, $params);
            }
        }
        return $res->status(404)->html('<h1>404 Not Found</h1>')->send();
    }

    private function match(string $routeMethod, string $routePath, string $reqMethod, string $reqUri): ?array
    {
        if ($routeMethod !== $reqMethod) return null;

        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = "@^{$pattern}$@";

        if (!preg_match($pattern, $reqUri, $m)) return null;

        preg_match_all('/\{([^}]+)\}/', $routePath, $names);
        return array_combine($names[1], array_slice($m, 1));
    }
}

/* ═══════════════════════════════════════════════════════════════════
 * MODELS (In-Memory Data Store)
 * ═══════════════════════════════════════════════════════════════════ */

class TaskModel
{
    private static array $tasks = [
        ['id' => 1, 'title' => 'Learn PHP MVC',     'done' => false, 'priority' => 'high'],
        ['id' => 2, 'title' => 'Build REST API',     'done' => false, 'priority' => 'high'],
        ['id' => 3, 'title' => 'Write Unit Tests',   'done' => true,  'priority' => 'medium'],
        ['id' => 4, 'title' => 'Deploy to Server',   'done' => false, 'priority' => 'low'],
    ];
    private static int $nextId = 5;

    public static function all(): array  { return self::$tasks; }

    public static function find(int $id): ?array
    {
        foreach (self::$tasks as $t) {
            if ($t['id'] === $id) return $t;
        }
        return null;
    }

    public static function create(string $title, string $priority = 'medium'): array
    {
        $task = ['id' => self::$nextId++, 'title' => $title, 'done' => false, 'priority' => $priority];
        self::$tasks[] = $task;
        return $task;
    }

    public static function toggle(int $id): ?array
    {
        foreach (self::$tasks as &$task) {
            if ($task['id'] === $id) {
                $task['done'] = !$task['done'];
                return $task;
            }
        }
        return null;
    }

    public static function delete(int $id): bool
    {
        $before = count(self::$tasks);
        self::$tasks = array_values(array_filter(self::$tasks, fn($t) => $t['id'] !== $id));
        return count(self::$tasks) < $before;
    }
}

/* ═══════════════════════════════════════════════════════════════════
 * CONTROLLERS
 * ═══════════════════════════════════════════════════════════════════ */

function renderPage(string $title, string $content): string
{
    return <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>{$title}</title>
        <style>
            *{box-sizing:border-box} body{font-family:'Segoe UI',sans-serif;background:#f0f4f8;margin:0;padding:20px}
            .wrap{max-width:700px;margin:0 auto} h1{color:#2d3748;border-bottom:3px solid #667eea;padding-bottom:12px}
            .card{background:#fff;border-radius:12px;padding:24px;margin-bottom:16px;box-shadow:0 4px 16px rgba(0,0,0,.08)}
            .task{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid #e2e8f0}
            .done span.title{text-decoration:line-through;color:#a0aec0}
            .badge{padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600}
            .high{background:#fed7d7;color:#742a2a} .medium{background:#fefcbf;color:#744210} .low{background:#c6f6d5;color:#276749}
            form{display:flex;gap:10px;flex-wrap:wrap;margin-top:10px}
            input,select{padding:9px 14px;border:2px solid #e2e8f0;border-radius:8px;font-size:14px;flex:1}
            button,.btn{padding:9px 18px;border:none;border-radius:8px;cursor:pointer;font-size:14px;text-decoration:none;display:inline-block}
            .btn-add{background:#667eea;color:#fff} .btn-del{background:#fed7d7;color:#c53030;padding:4px 10px;font-size:12px}
            .btn-tog{background:#ebf8ff;color:#2b6cb0;padding:4px 10px;font-size:12px}
            .stats{display:flex;gap:12px;margin-bottom:16px}
            .stat{flex:1;background:#667eea;color:#fff;border-radius:10px;padding:16px;text-align:center}
            .stat.g{background:#38a169} .stat.o{background:#e67e22} h3{font-size:28px;margin:4px 0} p{font-size:12px;margin:0}
        </style>
    </head>
    <body><div class="wrap"><h1>📋 Task Manager – PHP MVC</h1>{$content}</div></body>
    </html>
    HTML;
}

$router = new Router2();

// GET / – List tasks
$router->get('/', function (Request2 $req, Response2 $res) {
    $tasks = TaskModel::all();
    $done  = count(array_filter($tasks, fn($t) => $t['done']));
    $total = count($tasks);

    $stats = "<div class='stats'>"
        . "<div class='stat'><h3>$total</h3><p>Total Tasks</p></div>"
        . "<div class='stat g'><h3>$done</h3><p>Completed</p></div>"
        . "<div class='stat o'><h3>" . ($total - $done) . "</h3><p>Pending</p></div>"
        . "</div>";

    $form = "<div class='card'><h3>➕ Add Task</h3>"
        . "<form method='POST' action='/tasks'>"
        . "<input name='title' required placeholder='Task title...'>"
        . "<select name='priority'><option>high</option><option selected>medium</option><option>low</option></select>"
        . "<button class='btn btn-add' type='submit'>Add</button>"
        . "</form></div>";

    $list = "<div class='card'><h3>📋 Tasks</h3>";
    foreach ($tasks as $t) {
        $cls   = $t['done'] ? 'task done' : 'task';
        $check = $t['done'] ? '✅' : '⭕';
        $list .= "<div class='{$cls}'>"
            . "<span style='font-size:20px'>{$check}</span>"
            . "<span class='title' style='flex:1'>{$t['title']}</span>"
            . "<span class='badge {$t['priority']}'>{$t['priority']}</span>"
            . "<form method='POST' action='/tasks/{$t['id']}/toggle' style='margin:0;display:inline'>"
            . "<button class='btn btn-tog'>Toggle</button></form>"
            . "<form method='POST' action='/tasks/{$t['id']}/delete' style='margin:0;display:inline'>"
            . "<button class='btn btn-del'>Delete</button></form>"
            . "</div>";
    }
    $list .= "</div>";

    $html    = renderPage('Task Manager', $stats . $form . $list);
    $res->html($html)->send();
});

// POST /tasks – Create task
$router->post('/tasks', function (Request2 $req, Response2 $res) {
    $title    = trim($req->input('title', ''));
    $priority = $req->input('priority', 'medium');
    if (!empty($title)) TaskModel::create($title, $priority);
    $res->redirect('/')->send();
});

// POST /tasks/{id}/toggle
$router->post('/tasks/{id}/toggle', function (Request2 $req, Response2 $res, array $params) {
    TaskModel::toggle((int)$params['id']);
    $res->redirect('/')->send();
});

// POST /tasks/{id}/delete
$router->post('/tasks/{id}/delete', function (Request2 $req, Response2 $res, array $params) {
    TaskModel::delete((int)$params['id']);
    $res->redirect('/')->send();
});

// GET /api/tasks – JSON API endpoint
$router->get('/api/tasks', function (Request2 $req, Response2 $res) {
    $res->json(['tasks' => TaskModel::all()])->send();
});

/* ═══════════════════════════════════════════════════════════════════
 * BOOTSTRAP
 * ═══════════════════════════════════════════════════════════════════ */
$request  = new Request2();
$response = new Response2();
$router->dispatch($request, $response);
