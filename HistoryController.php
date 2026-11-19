<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\TestHistory;

final class HistoryController
{
    public function index(Request $request): void
    {
        $user = Session::user();
        if ($user === null) {
            Response::redirect($this->path('/connexion'));
            return;
        }
        if (($user['role'] ?? '') !== 'student') {
            Response::text('L’historique est réservé aux espaces élèves.', 403);
            return;
        }

        View::render('history/index', [
            'pageTitle' => 'Historique de mes tests',
            'currentUser' => $user,
            'items' => TestHistory::forUser((int) $user['id']),
        ]);
    }

    private function path(string $path): string
    {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        return ($base === '.' ? '' : $base) . $path;
    }
}
