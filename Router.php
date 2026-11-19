<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, callable|array{class-string, string}>> */
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function dispatch(Request $request): void
    {
        $handler = $this->routes[$request->method][$request->path] ?? null;
        if ($handler === null) {
            Response::text('Page introuvable.', 404);
            return;
        }

        if (is_array($handler) && is_string($handler[0])) {
            $handler = [new $handler[0](), $handler[1]];
        }

        $handler($request);
    }

    private function add(string $method, string $path, callable|array $handler): void
    {
        $normalizedPath = '/' . trim($path, '/');
        $normalizedPath = $normalizedPath === '/' ? '/' : rtrim($normalizedPath, '/');
        $this->routes[$method][$normalizedPath] = $handler;
    }
}
