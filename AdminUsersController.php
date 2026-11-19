<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\User;
use DomainException;

final class AdminUsersController
{
    public function index(Request $request): void
    {
        $admin = $this->authorizeAdmin();
        if ($admin === null) return;

        $search = trim((string) ($request->query['q'] ?? ''));
        $role = (string) ($request->query['role'] ?? 'all');
        if (!in_array($role, ['all', 'student', 'admin'], true)) $role = 'all';
        View::render('admin/users', [
            'pageTitle' => 'Gestion des utilisateurs',
            'currentUser' => $admin,
            'users' => User::adminList(mb_substr($search, 0, 100), $role),
            'search' => mb_substr($search, 0, 100),
            'roleFilter' => $role,
            'errors' => Session::consumeFlash('admin_user_errors', []),
            'success' => Session::consumeFlash('admin_user_success'),
            'csrfToken' => Session::csrfToken(),
        ]);
    }

    public function update(Request $request): void
    {
        $admin = $this->authorizeAdmin();
        if ($admin === null) return;
        if (!Session::verifyCsrfToken($request->body['_csrf_token'] ?? null)) {
            Response::text('La session a expiré. Recharge la page puis réessaie.', 403);
            return;
        }

        $userId = filter_var($request->body['user_id'] ?? null, FILTER_VALIDATE_INT);
        $role = is_string($request->body['role'] ?? null) ? $request->body['role'] : '';
        $status = is_string($request->body['status'] ?? null) ? $request->body['status'] : '';
        try {
            if (!$userId || $userId < 1) throw new DomainException('Le compte sélectionné est invalide.');
            $remoteAddress = $request->server['REMOTE_ADDR'] ?? null;
            User::updateAccess(
                (int) $admin['id'],
                (int) $userId,
                $role,
                $status,
                is_string($remoteAddress) ? $remoteAddress : null
            );
            Session::flash('admin_user_success', 'Les accès du compte ont été mis à jour.');
        } catch (DomainException $exception) {
            Session::flash('admin_user_errors', [$exception->getMessage()]);
        }

        $query = http_build_query([
            'q' => (string) ($request->body['return_q'] ?? ''),
            'role' => (string) ($request->body['return_role'] ?? 'all'),
        ]);
        Response::redirect($this->path('/admin/utilisateurs') . ($query !== '' ? '?' . $query : ''));
    }

    private function authorizeAdmin(): ?array
    {
        $user = Session::user();
        if ($user === null) {
            Response::redirect($this->path('/connexion'));
            return null;
        }
        if (($user['role'] ?? '') !== 'admin') {
            Response::text('Accès réservé à l’administration.', 403);
            return null;
        }
        return $user;
    }

    private function path(string $path): string
    {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        return ($base === '.' ? '' : $base) . $path;
    }
}
