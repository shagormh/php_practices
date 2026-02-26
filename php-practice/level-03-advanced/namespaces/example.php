<?php
/**
 * Level 3 – Namespaces
 * Namespaces prevent name collisions and organize code like directories.
 * PHP 8+ features: named arguments, match, enums
 *
 * Run: php example.php
 */

declare(strict_types=1);

/* ── Namespace declarations ──────────────────────────────────────────── */
namespace App\Core {
    class Config
    {
        private array $data;

        public function __construct(array $data = [])
        {
            $this->data = $data;
        }

        public function get(string $key, mixed $default = null): mixed
        {
            return $this->resolve($this->data, explode('.', $key)) ?? $default;
        }

        private function resolve(array $data, array $keys): mixed
        {
            $key = array_shift($keys);
            if (!isset($data[$key])) return null;
            return empty($keys) ? $data[$key] : $this->resolve($data[$key], $keys);
        }
    }

    class Logger
    {
        private string $prefix;
        private array  $records = [];

        public function __construct(string $prefix = 'APP')
        {
            $this->prefix = $prefix;
        }

        public function log(string $level, string $message): void
        {
            $entry = sprintf("[%s][%s] %s", date('H:i:s'), strtoupper($level), $message);
            $this->records[] = $entry;
            echo "  " . $entry . "\n";
        }

        public function info(string $msg): void  { $this->log('INFO',  $msg); }
        public function error(string $msg): void { $this->log('ERROR', $msg); }
        public function warn(string $msg): void  { $this->log('WARN',  $msg); }

        public function getRecords(): array { return $this->records; }
    }
}

namespace App\Http {
    use App\Core\Logger;

    class Request
    {
        public function __construct(
            private string $method,
            private string $uri,
            private array  $body = [],
            private array  $headers = [],
        ) {}

        public function getMethod(): string { return strtoupper($this->method); }
        public function getUri(): string    { return $this->uri; }
        public function getBody(): array    { return $this->body; }
        public function input(string $key, mixed $default = null): mixed {
            return $this->body[$key] ?? $default;
        }
    }

    class Response
    {
        public function __construct(
            private mixed $data,
            private int   $status = 200,
        ) {}

        public function json(): string
        {
            http_response_code($this->status);
            return json_encode([
                'status' => $this->status,
                'data'   => $this->data,
            ], JSON_PRETTY_PRINT);
        }

        public function getStatus(): int  { return $this->status; }
        public function getData(): mixed  { return $this->data; }
    }

    class Router
    {
        private array $routes = [];
        private Logger $logger;

        public function __construct(Logger $logger)
        {
            $this->logger = $logger;
        }

        public function get(string $uri, callable $handler): void
        {
            $this->routes['GET'][$uri] = $handler;
        }

        public function post(string $uri, callable $handler): void
        {
            $this->routes['POST'][$uri] = $handler;
        }

        public function dispatch(Request $request): Response
        {
            $method  = $request->getMethod();
            $uri     = $request->getUri();
            $handler = $this->routes[$method][$uri] ?? null;

            $this->logger->info("$method $uri");

            if ($handler === null) {
                $this->logger->warn("Route not found: $method $uri");
                return new Response(['error' => 'Not Found'], 404);
            }

            return $handler($request);
        }
    }
}

namespace App\Models {
    class Product
    {
        public function __construct(
            private int    $id,
            private string $name,
            private float  $price,
            private int    $stock,
        ) {}

        public function toArray(): array
        {
            return [
                'id'    => $this->id,
                'name'  => $this->name,
                'price' => $this->price,
                'stock' => $this->stock,
            ];
        }
    }
}

/* ── Main script using all namespaces ────────────────────────────────── */
namespace {
    use App\Core\{Config, Logger};
    use App\Http\{Request, Response, Router};
    use App\Models\Product;

    echo "=== Namespace Demo ===\n\n";

    // Config
    $config = new Config([
        'app'  => ['name' => 'PHPPractice', 'version' => '1.0'],
        'db'   => ['host' => 'localhost', 'port' => 3306],
        'mail' => ['from' => 'no-reply@example.com'],
    ]);

    echo "App Name    : " . $config->get('app.name') . "\n";
    echo "DB Port     : " . $config->get('db.port') . "\n";
    echo "Missing key : " . ($config->get('cache.driver', 'file')) . "\n\n";

    // Router + Logger
    $logger = new Logger('HTTP');
    $router = new Router($logger);

    // Register routes
    $router->get('/products', function (Request $req): Response {
        $products = [
            new Product(1, 'PHP Book', 450.00, 50),
            new Product(2, 'Keyboard', 1200.00, 20),
        ];
        return new Response(array_map(fn($p) => $p->toArray(), $products));
    });

    $router->post('/products', function (Request $req): Response {
        $name = $req->input('name', 'Unknown');
        return new Response(['created' => true, 'name' => $name], 201);
    });

    // Simulate requests
    echo "=== Router Dispatch ===\n";
    $res1 = $router->dispatch(new Request('GET', '/products'));
    $res2 = $router->dispatch(new Request('POST', '/products', ['name' => 'Mouse']));
    $res3 = $router->dispatch(new Request('GET', '/unknown'));

    echo "\n=== Responses ===\n";
    foreach ([$res1, $res2, $res3] as $res) {
        echo "Status: " . $res->getStatus() . "\n";
        echo $res->json() . "\n\n";
    }
}
