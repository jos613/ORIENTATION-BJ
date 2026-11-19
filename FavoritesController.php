<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Favorites;
use DomainException;

final class FavoritesController
{
    public function index(Request $request): void
    {
        $user = $this->student();
        if ($user === null) return;

        View::render('favorites/index', [
            'pageTitle' => 'Mes favoris',
            'currentUser' => $user,
            'items' => Favorites::forUser((int) $user['id']),
            'success' => Session::consumeFlash('favorite_success'),
            'errors' => Session::consumeFlash('favorite_errors', []),
            'csrfToken' => Session::csrfToken(),
        ]);
    }

    public function handle(Request $request): void
    {
        $user = $this->student();
        if ($user === null) return;
        if (!Session::verifyCsrfToken($request->body['_csrf_token'] ?? null)) {
            Session::flash('favorite_errors', ['Ta session a expiré. Recharge la page puis réessaie.']);
            Response::redirect($this->path('/favoris'));
            return;
        }

        $action = (string) ($request->body['action'] ?? '');
        $domainId = filter_var($request->body['domain_id'] ?? null, FILTER_VALIDATE_INT);
        $testId = filter_var($request->body['test_id'] ?? null, FILTER_VALIDATE_INT);
        $returnToResults = ($request->body['return_to'] ?? '') === 'results';

        try {
            if (!$domainId || $domainId < 1) {
                throw new DomainException('Le domaine sélectionné est invalide.');
            }
            if ($action === 'add' && $testId && $testId > 0) {
                Favorites::add((int) $user['id'], (int) $domainId, (int) $testId);
                Session::flash('favorite_success', 'Domaine ajouté à tes favoris.');
            } elseif ($action === 'remove') {
                Favorites::remove((int) $user['id'], (int) $domainId);
                Session::flash('favorite_success', 'Domaine retiré de tes favoris.');
            } else {
                throw new DomainException('Cette action sur les favoris n’est pas reconnue.');
            }
        } catch (DomainException $exception) {
            Session::flash('favorite_errors', [$exception->getMessage()]);
        }

        $destination = $returnToResults
            ? '/resultats' . ($testId && $testId > 0 ? '?test=' . (int) $testId : '')
            : '/favoris';
        Response::redirect($this->path($destination));
    }

    private function student(): ?array
    {
        $user = Session::user();
        if ($user === null) {
            Response::redirect($this->path('/connexion'));
            return null;
        }
        if (($user['role'] ?? '') !== 'student') {
            Response::text('Les favoris sont réservés aux espaces élèves.', 403);
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
