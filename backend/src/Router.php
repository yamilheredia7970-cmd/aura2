<?php

declare(strict_types=1);

namespace Backend;

use Backend\Support\Response;

final class Router
{
    /** @var array<int, array{method: string, pattern: string, handler: callable, middleware: array<callable>}> */
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler, array $middleware = []): void
    {
        $this->routes[] = compact('method', 'pattern', 'handler', 'middleware');
    }

    public function get(string $pattern, callable $handler, array $middleware = []): void
    {
        $this->add('GET', $pattern, $handler, $middleware);
    }

    public function post(string $pattern, callable $handler, array $middleware = []): void
    {
        $this->add('POST', $pattern, $handler, $middleware);
    }

    public function patch(string $pattern, callable $handler, array $middleware = []): void
    {
        $this->add('PATCH', $pattern, $handler, $middleware);
    }

    public function delete(string $pattern, callable $handler, array $middleware = []): void
    {
        $this->add('DELETE', $pattern, $handler, $middleware);
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $regex = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $route['pattern']);
            if (preg_match('#^' . $regex . '$#', $path, $matches)) {
                $params = array_filter($matches, fn ($key) => is_string($key), ARRAY_FILTER_USE_KEY);

                foreach ($route['middleware'] as $middleware) {
                    $middleware();
                }

                ($route['handler'])($params);
                return;
            }
        }

        Response::error('Recurso no encontrado.', 404);
    }
}
