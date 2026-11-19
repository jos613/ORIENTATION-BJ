<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Session
{
    private static bool $userLoaded = false;
    private static ?array $currentUser = null;
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name('orientation_bj_session');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    public static function verifyCsrfToken(mixed $token): bool
    {
        return is_string($token)
            && isset($_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $token);
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
        unset($_SESSION['_csrf_token']);
    }

    public static function setUser(array $user): void
    {
        $_SESSION['user'] = self::$currentUser = [
            'id' => (int) $user['id'],
            'email' => (string) $user['email'],
            'role' => (string) $user['role'],
            'first_name' => (string) $user['first_name'],
            'last_name' => (string) $user['last_name'],
        ];
        self::$userLoaded = true;
    }

    public static function user(): ?array
    {
        if (self::$userLoaded) return self::$currentUser;
        self::$userLoaded = true;
        $stored = $_SESSION['user'] ?? null;
        if (!is_array($stored) || !isset($stored['id'])) return null;

        $user = User::findForSession((int) $stored['id']);
        if ($user === null || $user['status'] !== 'active') {
            self::clearUser();
            return null;
        }

        self::$currentUser = [
            'id' => (int) $user['id'],
            'email' => (string) $user['email'],
            'role' => (string) $user['role'],
            'first_name' => (string) ($user['first_name'] ?? ''),
            'last_name' => (string) ($user['last_name'] ?? ''),
        ];
        $_SESSION['user'] = self::$currentUser;
        return self::$currentUser;
    }

    public static function clearUser(): void
    {
        unset($_SESSION['user']);
        self::$currentUser = null;
        self::$userLoaded = true;
    }

    public static function updateProfileName(string $firstName, string $lastName): void
    {
        if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
            $_SESSION['user']['first_name'] = $firstName;
            $_SESSION['user']['last_name'] = $lastName;
        }
        if (self::$currentUser !== null) {
            self::$currentUser['first_name'] = $firstName;
            self::$currentUser['last_name'] = $lastName;
        }
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function consumeFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        if (empty($_SESSION['_flash'])) {
            unset($_SESSION['_flash']);
        }

        return $value;
    }
}
