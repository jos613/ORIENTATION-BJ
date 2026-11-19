<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\AuditLog;

final class AdminSecurityController
{
    public function index(Request $request): void
    {
        $user = Session::user();
        if ($user === null) {
            Response::redirect($this->path('/connexion'));
            return;
        }
        if (($user['role'] ?? '') !== 'admin') {
            Response::text('Accès réservé à l’administration.', 403);
            return;
        }

        View::render('admin/security', [
            'pageTitle' => 'Journal des actions sensibles',
            'currentUser' => $user,
            'logs' => AuditLog::recent(100),
        ]);
    }

    private function path(string $path): string
    {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        return ($base === '.' ? '' : $base) . $path;
    }
}
