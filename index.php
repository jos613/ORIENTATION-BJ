<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

use App\Core\Request;
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\SystemController;
use App\Controllers\ProfileController;
use App\Controllers\BacSeriesController;
use App\Controllers\OrientationTestController;
use App\Controllers\CatalogController;
use App\Controllers\FavoritesController;
use App\Controllers\HistoryController;
use App\Controllers\AdminUsersController;
use App\Controllers\AdminSecurityController;

$router = new Router();
$router->get('/', [SystemController::class, 'home']);
$router->get('/dashboard', [SystemController::class, 'dashboard']);
$router->get('/health', [SystemController::class, 'health']);
$router->get('/inscription', [AuthController::class, 'showRegistration']);
$router->post('/inscription', [AuthController::class, 'register']);
$router->get('/connexion', [AuthController::class, 'showLogin']);
$router->post('/connexion', [AuthController::class, 'login']);
$router->post('/deconnexion', [AuthController::class, 'logout']);
$router->get('/profil', [ProfileController::class, 'show']);
$router->post('/profil', [ProfileController::class, 'update']);
$router->get('/admin/series', [BacSeriesController::class, 'index']);
$router->post('/admin/series', [BacSeriesController::class, 'handle']);
$catalog = new CatalogController();
$router->get('/admin/referentiel/domaines', [$catalog, 'domains']);
$router->post('/admin/referentiel/domaines', [$catalog, 'saveDomains']);
$router->get('/admin/referentiel/formations', [$catalog, 'programs']);
$router->post('/admin/referentiel/formations', [$catalog, 'savePrograms']);
$router->get('/admin/referentiel/etablissements', [$catalog, 'institutions']);
$router->post('/admin/referentiel/etablissements', [$catalog, 'saveInstitutions']);
$router->get('/admin/referentiel/metiers', [$catalog, 'careers']);
$router->post('/admin/referentiel/metiers', [$catalog, 'saveCareers']);
$router->get('/test-orientation', [OrientationTestController::class, 'show']);
$router->get('/resultats', [OrientationTestController::class, 'showResults']);
$favorites = new FavoritesController();
$router->get('/favoris', [$favorites, 'index']);
$router->post('/favoris', [$favorites, 'handle']);
$router->get('/historique', [HistoryController::class, 'index']);
$adminUsers = new AdminUsersController();
$router->get('/admin/utilisateurs', [$adminUsers, 'index']);
$router->post('/admin/utilisateurs', [$adminUsers, 'update']);
$router->get('/admin/securite', [AdminSecurityController::class, 'index']);
$router->post('/test-orientation', [OrientationTestController::class, 'startOrResume']);
$router->post('/test-orientation/reponses', [OrientationTestController::class, 'submit']);
$router->post('/test-orientation/reponse', [OrientationTestController::class, 'saveAnswer']);

$router->dispatch(Request::capture());
