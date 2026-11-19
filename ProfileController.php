<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\BacSeries;
use App\Models\StudentProfile;

final class ProfileController
{
    public function show(Request $request): void
    {
        $user = Session::user();
        if ($user === null) {
            Response::redirect($this->path('/connexion'));
            return;
        }

        $profile = StudentProfile::findByUserId($user['id']);
        if ($profile === null) {
            Response::text('Profil introuvable.', 404);
            return;
        }

        View::render('profile/edit', [
            'pageTitle' => 'Mon profil',
            'profile' => $profile,
            'series' => BacSeries::availableForProfile($profile['bac_series_id'] === null ? null : (int) $profile['bac_series_id']),
            'success' => Session::consumeFlash('success'),
            'errors' => Session::consumeFlash('errors', []),
            'values' => Session::consumeFlash('values', $profile),
            'csrfToken' => Session::csrfToken(),
            'currentUser' => $user,
        ]);
    }

    public function update(Request $request): void
    {
        $user = Session::user();
        if ($user === null) {
            Response::redirect($this->path('/connexion'));
            return;
        }
        if (!Session::verifyCsrfToken($request->body['_csrf_token'] ?? null)) {
            Response::text('La session a expiré. Recharge le formulaire puis réessaie.', 403);
            return;
        }

        $currentProfile = StudentProfile::findByUserId($user['id']);
        if ($currentProfile === null) {
            Response::text('Profil introuvable.', 404);
            return;
        }

        $values = [
            'first_name' => $this->stringInput($request->body['first_name'] ?? null),
            'last_name' => $this->stringInput($request->body['last_name'] ?? null),
            'bac_series_id' => $this->stringInput($request->body['bac_series_id'] ?? null),
            'bac_year' => $this->stringInput($request->body['bac_year'] ?? null),
            'phone' => $this->stringInput($request->body['phone'] ?? null),
            'city' => $this->stringInput($request->body['city'] ?? null),
        ];
        $errors = [];

        if ($values['first_name'] === '' || mb_strlen($values['first_name']) > 100) {
            $errors[] = 'Le prénom est obligatoire (100 caractères maximum).';
        }
        if ($values['last_name'] === '' || mb_strlen($values['last_name']) > 100) {
            $errors[] = 'Le nom est obligatoire (100 caractères maximum).';
        }
        if ($values['phone'] !== '' && mb_strlen($values['phone']) > 30) {
            $errors[] = 'Le téléphone ne peut pas dépasser 30 caractères.';
        }
        if ($values['city'] !== '' && mb_strlen($values['city']) > 120) {
            $errors[] = 'La ville ne peut pas dépasser 120 caractères.';
        }

        $seriesId = null;
        if ($values['bac_series_id'] !== '') {
            if (!ctype_digit($values['bac_series_id']) || (int) $values['bac_series_id'] < 1) {
                $errors[] = 'Sélectionne une série du BAC valide.';
            } else {
                $seriesId = (int) $values['bac_series_id'];
                $series = BacSeries::find($seriesId);
                $isCurrentSeries = $seriesId === (int) ($currentProfile['bac_series_id'] ?? 0);
                if ($series === null || (!(bool) $series['is_active'] && !$isCurrentSeries)) {
                    $errors[] = 'Cette série du BAC n’est plus disponible.';
                }
            }
        }

        $bacYear = null;
        if ($values['bac_year'] !== '') {
            $currentYear = (int) date('Y');
            if (!ctype_digit($values['bac_year']) || (int) $values['bac_year'] < 1950 || (int) $values['bac_year'] > $currentYear) {
                $errors[] = 'Indique une année du BAC comprise entre 1950 et ' . $currentYear . '.';
            } else {
                $bacYear = (int) $values['bac_year'];
            }
        }

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('values', $values);
            Response::redirect($this->path('/profil'));
            return;
        }

        StudentProfile::update($user['id'], [
            'first_name' => $values['first_name'],
            'last_name' => $values['last_name'],
            'bac_series_id' => $seriesId,
            'bac_year' => $bacYear,
            'phone' => $values['phone'] === '' ? null : $values['phone'],
            'city' => $values['city'] === '' ? null : $values['city'],
        ]);
        Session::updateProfileName($values['first_name'], $values['last_name']);
        Session::flash('success', 'Ton profil a bien été mis à jour.');
        Response::redirect($this->path('/profil'));
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
