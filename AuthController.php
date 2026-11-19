<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\User;
use PDOException;

final class AuthController
{
    public function showRegistration(Request $request): void
    {
        if (Session::user() !== null) {
            Response::redirect($this->path('/'));
        }

        View::render('auth/register', [
            'pageTitle' => 'Créer mon compte',
            'errors' => Session::consumeFlash('errors', []),
            'values' => Session::consumeFlash('values', []),
            'csrfToken' => Session::csrfToken(),
        ]);
    }

    public function register(Request $request): void
    {
        if (!Session::verifyCsrfToken($request->body['_csrf_token'] ?? null)) {
            Response::text('La session a expiré. Recharge le formulaire puis réessaie.', 403);
            return;
        }

        $firstName = trim((string) ($request->body['first_name'] ?? ''));
        $lastName = trim((string) ($request->body['last_name'] ?? ''));
        $email = mb_strtolower(trim((string) ($request->body['email'] ?? '')));
        $password = (string) ($request->body['password'] ?? '');
        $passwordConfirmation = (string) ($request->body['password_confirmation'] ?? '');
        $values = ['first_name' => $firstName, 'last_name' => $lastName, 'email' => $email];
        $errors = [];

        if ($firstName === '' || mb_strlen($firstName) > 100) {
            $errors[] = 'Indique ton prénom (100 caractères maximum).';
        }
        if ($lastName === '' || mb_strlen($lastName) > 100) {
            $errors[] = 'Indique ton nom (100 caractères maximum).';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
            $errors[] = 'Saisis une adresse e-mail valide.';
        } elseif (User::emailExists($email)) {
            $errors[] = 'Cette adresse e-mail est déjà associée à un compte.';
        }
        if (strlen($password) < 10) {
            $errors[] = 'Choisis un mot de passe d’au moins 10 caractères.';
        } elseif (strlen($password) > 200) {
            $errors[] = 'Le mot de passe ne peut pas dépasser 200 caractères.';
        }
        if ($password !== $passwordConfirmation) {
            $errors[] = 'La confirmation du mot de passe ne correspond pas.';
        }

        if ($errors !== []) {
            $this->registrationError($errors, $values);
            return;
        }

        try {
            User::createStudent($email, password_hash($password, PASSWORD_DEFAULT), $firstName, $lastName);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $this->registrationError(['Cette adresse e-mail est déjà associée à un compte.'], $values);
                return;
            }
            throw $exception;
        }

        Session::flash('success', 'Ton compte est créé. Tu peux maintenant te connecter.');
        Response::redirect($this->path('/connexion'));
    }

    public function showLogin(Request $request): void
    {
        if (Session::user() !== null) {
            Response::redirect($this->path('/'));
        }

        View::render('auth/login', [
            'pageTitle' => 'Connexion',
            'error' => Session::consumeFlash('error'),
            'success' => Session::consumeFlash('success'),
            'email' => Session::consumeFlash('email', ''),
            'csrfToken' => Session::csrfToken(),
        ]);
    }

    public function login(Request $request): void
    {
        if (!Session::verifyCsrfToken($request->body['_csrf_token'] ?? null)) {
            Response::text('La session a expiré. Recharge le formulaire puis réessaie.', 403);
            return;
        }

        $email = mb_strtolower(trim((string) ($request->body['email'] ?? '')));
        $password = (string) ($request->body['password'] ?? '');
        $user = filter_var($email, FILTER_VALIDATE_EMAIL) ? User::findForLogin($email) : null;

        if ($user === null || $user['status'] !== 'active' || !password_verify($password, $user['password_hash'])) {
            Session::flash('error', 'Adresse e-mail ou mot de passe incorrect.');
            Session::flash('email', $email);
            Response::redirect($this->path('/connexion'));
        }

        User::touchLogin((int) $user['id']);
        Session::regenerate();
        Session::setUser($user);
        Session::flash('auth_success', 'Connexion réussie. Bienvenue sur ORIENTATION-BJ.');
        Response::redirect($this->path('/dashboard'));
    }

    public function logout(Request $request): void
    {
        if (!Session::verifyCsrfToken($request->body['_csrf_token'] ?? null)) {
            Response::text('La session a expiré. Recharge la page puis réessaie.', 403);
            return;
        }

        Session::clearUser();
        Session::regenerate();
        Session::flash('success', 'Tu es déconnecté. À bientôt.');
        Response::redirect($this->path('/connexion'));
    }

    private function registrationError(array $errors, array $values): void
    {
        Session::flash('errors', $errors);
        Session::flash('values', $values);
        Response::redirect($this->path('/inscription'));
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
