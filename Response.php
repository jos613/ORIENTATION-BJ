<?php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function text(string $content, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/plain; charset=utf-8');
        echo $content;
    }

    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public static function redirect(string $location, int $status = 302): void
    {
        http_response_code($status);
        header('Location: ' . $location);
        exit;
    }
}
