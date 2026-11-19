<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\BacSeries;
use App\Models\AuditLog;
use PDOException;

final class BacSeriesController
{
    public function index(Request $request): void
    {
        if (!$this->authorizeAdmin()) {
            return;
        }

        $editId = filter_var($request->query['edit'] ?? null, FILTER_VALIDATE_INT);
        $editSeries = $editId ? BacSeries::find((int) $editId) : null;
        $values = Session::consumeFlash('values', $editSeries ?? ['code' => '', 'name' => '', 'description' => '', 'is_active' => 1]);
        View::render('admin/series', [
            'pageTitle' => 'Gestion des séries du BAC',
            'seriesList' => BacSeries::all(),
            'editSeries' => $editSeries,
            'values' => $values,
            'errors' => Session::consumeFlash('errors', []),
            'success' => Session::consumeFlash('success'),
            'csrfToken' => Session::csrfToken(),
            'currentUser' => Session::user(),
        ]);
    }

    public function handle(Request $request): void
    {
        if (!$this->authorizeAdmin()) {
            return;
        }
        if (!Session::verifyCsrfToken($request->body['_csrf_token'] ?? null)) {
            Response::text('La session a expiré. Recharge la page puis réessaie.', 403);
            return;
        }

        $action = $this->stringInput($request->body['action'] ?? null);
        $seriesId = filter_var($request->body['series_id'] ?? null, FILTER_VALIDATE_INT);
        if (in_array($action, ['toggle', 'delete'], true)) {
            $before = $seriesId ? BacSeries::find((int) $seriesId) : null;
            if (!$seriesId || $before === null) {
                Session::flash('errors', ['La série sélectionnée est introuvable.']);
            } elseif ($action === 'toggle') {
                BacSeries::toggle((int) $seriesId);
                $actor = Session::user();
                AuditLog::record((int) $actor['id'], 'series.status_changed', 'bac_series', (int) $seriesId, ['from' => (int) $before['is_active'], 'to' => (int) !(bool) $before['is_active']], $request->server['REMOTE_ADDR'] ?? null);
                Session::flash('success', 'Le statut de la série a été mis à jour.');
            } else {
                $usageCount = BacSeries::usageCount((int) $seriesId);
                if ($usageCount > 0) {
                    Session::flash('errors', ['Cette série est utilisée par ' . $usageCount . ' profil(s). Désactive-la plutôt que de la supprimer.']);
                } else {
                    try {
                        BacSeries::delete((int) $seriesId);
                        $actor = Session::user();
                        AuditLog::record((int) $actor['id'], 'series.deleted', 'bac_series', (int) $seriesId, ['code' => $before['code']], $request->server['REMOTE_ADDR'] ?? null);
                        Session::flash('success', 'La série a été supprimée.');
                    } catch (PDOException $exception) {
                        if ($exception->getCode() === '23000') {
                            Session::flash('errors', ['Cette série est utilisée et ne peut pas être supprimée.']);
                        } else {
                            throw $exception;
                        }
                    }
                }
            }
            Response::redirect($this->path('/admin/series'));
        }

        if (!in_array($action, ['create', 'update'], true)) {
            Response::text('Action non reconnue.', 400);
            return;
        }

        $values = [
            'code' => mb_strtoupper($this->stringInput($request->body['code'] ?? null)),
            'name' => $this->stringInput($request->body['name'] ?? null),
            'description' => $this->stringInput($request->body['description'] ?? null),
            'is_active' => isset($request->body['is_active']) ? 1 : 0,
        ];
        $errors = [];
        if (!preg_match('/^[A-Z0-9_-]{1,20}$/', $values['code'])) {
            $errors[] = 'Le code doit contenir au maximum 20 lettres, chiffres, tirets ou soulignements.';
        }
        if ($values['name'] === '' || mb_strlen($values['name']) > 120) {
            $errors[] = 'Le nom est obligatoire (120 caractères maximum).';
        }
        if ($values['description'] !== '' && mb_strlen($values['description']) > 10000) {
            $errors[] = 'La description est trop longue.';
        }
        if ($action === 'update' && (!$seriesId || BacSeries::find((int) $seriesId) === null)) {
            $errors[] = 'La série à modifier est introuvable.';
        }
        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('values', $values);
            $query = $action === 'update' && $seriesId ? '?edit=' . (int) $seriesId : '';
            Response::redirect($this->path('/admin/series') . $query);
        }

        try {
            $actor = Session::user();
            if ($action === 'create') {
                $newId = BacSeries::create($values);
                AuditLog::record((int) $actor['id'], 'series.created', 'bac_series', $newId, ['code' => $values['code']], $request->server['REMOTE_ADDR'] ?? null);
                Session::flash('success', 'La série a été créée.');
            } else {
                BacSeries::update((int) $seriesId, $values);
                AuditLog::record((int) $actor['id'], 'series.updated', 'bac_series', (int) $seriesId, ['code' => $values['code']], $request->server['REMOTE_ADDR'] ?? null);
                Session::flash('success', 'La série a été modifiée.');
            }
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                Session::flash('errors', ['Ce code de série est déjà utilisé.']);
                Session::flash('values', $values);
                $query = $action === 'update' && $seriesId ? '?edit=' . (int) $seriesId : '';
                Response::redirect($this->path('/admin/series') . $query);
            }
            throw $exception;
        }

        Response::redirect($this->path('/admin/series'));
    }

    private function authorizeAdmin(): bool
    {
        $user = Session::user();
        if ($user === null) {
            Response::redirect($this->path('/connexion'));
            return false;
        }
        if ($user['role'] !== 'admin') {
            Response::text('Accès réservé à l’administration.', 403);
            return false;
        }

        return true;
    }

    private function stringInput(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    private function path(string $path): string
    {
        $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if ($basePath === '.') {
            $basePath = '';
        }

        return $basePath . $path;
    }
}
