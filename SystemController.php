<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\AdminDashboard;
use Throwable;

final class SystemController
{
    public function home(Request $request): void
    {
        if (Session::user() !== null) {
            Response::redirect($this->path('/dashboard'));
        }
        View::render('system/home', [
            'pageTitle' => 'Ton BAC. Ta voie.',
            'pageDescription' => 'Découvre des pistes d’orientation adaptées à ton profil, à tes intérêts et à ton projet professionnel.',
            'currentUser' => Session::user(),
            'authMessage' => Session::consumeFlash('auth_success'),
            'notice' => Session::consumeFlash('success'),
        ]);
    }

    public function dashboard(Request $request): void
    {
        $user = Session::user();
        if ($user === null) {
            Response::redirect($this->path('/connexion'));
        }

        View::render('system/dashboard', [
            'pageTitle' => 'Mon espace',
            'currentUser' => $user,
            'authMessage' => Session::consumeFlash('auth_success'),
            'adminStats' => ($user['role'] ?? '') === 'admin' ? AdminDashboard::overview() : null,
        ]);
    }

    private function path(string $path): string
    {
        $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        return ($basePath === '.' ? '' : $basePath) . $path;
    }

    public function health(Request $request): void
    {
        try {
            $database = Database::connection();
            $database->query('SELECT 1')->fetchColumn();
            Response::json(['status' => 'ok', 'database' => 'connected']);
        } catch (Throwable) {
            Response::json(['status' => 'error', 'database' => 'unavailable'], 503);
        }
    }
}
