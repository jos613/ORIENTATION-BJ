<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    private function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $body,
        public readonly array $server
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));

        if ($scriptDirectory !== '/' && $scriptDirectory !== '.' && str_starts_with($uriPath, $scriptDirectory)) {
            $uriPath = substr($uriPath, strlen($scriptDirectory)) ?: '/';
        }

        $normalizedPath = '/' . trim($uriPath, '/');
        if ($normalizedPath !== '/') {
            $normalizedPath = rtrim($normalizedPath, '/');
        }

        return new self($method, $normalizedPath, $_GET, $_POST, $_SERVER);
    }
}
