<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    public static function render(string $template, array $data = []): void
    {
        if (!preg_match('/^[a-zA-Z0-9_\/-]+$/', $template)) {
            throw new RuntimeException('Nom de vue invalide.');
        }

        $viewFile = APP_ROOT . '/Views/' . $template . '.php';
        if (!is_file($viewFile)) {
            throw new RuntimeException('Vue introuvable.');
        }

        $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if ($basePath === '.') {
            $basePath = '';
        }
        $data = ['basePath' => $basePath, 'appName' => 'ORIENTATION-BJ'] + $data;
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            require $viewFile;
            $content = ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }

        if (Session::user() !== null && $template !== 'system/dashboard') {
            $currentUser = Session::user();
            $activeMenu = match ($template) {
                'profile/edit' => 'profile',
                'admin/series' => 'series',
                'admin/catalog' => 'catalog',
                'orientation/results' => 'results',
                'favorites/index' => 'favorites',
                'history/index' => 'history',
                'admin/users' => 'users',
                'admin/security' => 'security',
                default => 'dashboard',
            };
            ob_start();
            require APP_ROOT . '/Views/layouts/dashboard-shell.php';
            $content = ob_get_clean();
        }

        $layoutFile = APP_ROOT . '/Views/layouts/base.php';
        if (is_file($layoutFile)) {
            ob_start();
            try {
                require $layoutFile;
                $content = ob_get_clean();
            } catch (\Throwable $exception) {
                ob_end_clean();
                throw $exception;
            }
        }

        header('Content-Type: text/html; charset=utf-8');
        echo $content;
    }

    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
