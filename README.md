# ORIENTATION-BJ

Socle PHP natif en architecture MVC pour le projet d’orientation académique et professionnelle des nouveaux bacheliers au Bénin.

## Prérequis locaux

- XAMPP avec Apache, PHP 8.2+ et MySQL/MariaDB démarrés.
- Extensions PHP `pdo` et `pdo_mysql` activées.
- Base `orientation_bj` créée et migration initiale importée (voir [database/README.md](database/README.md)).

## Lancer le projet

Placer le dossier dans `C:\xampp\htdocs\orientation-bj`, démarrer Apache et MySQL, puis ouvrir `http://localhost/orientation-bj/`. La route `/health` vérifie la connexion PDO sans exposer les paramètres de connexion.

Configuration locale par défaut : hôte `127.0.0.1`, port `3306`, base `orientation_bj`, utilisateur `root`, mot de passe vide. Les valeurs peuvent être remplacées par les variables d’environnement `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` et `DB_PASSWORD`. Ne pas versionner de secrets.

Le fichier `.htaccess` protège les sources et les migrations, désactive l’indexation des dossiers et route les requêtes vers `index.php`. Apache doit autoriser les directives `.htaccess` du dossier (`AllowOverride All` dans la configuration locale XAMPP).

## Structure MVC

```text
app/
  config/       Configuration application et base
  Controllers/  Contrôleurs HTTP
  Core/         Routeur, requête, réponse, vue, PDO
  Models/       Modèles métier (à créer dans les modules suivants)
  Views/        Vues PHP
database/
  migrations/   Migrations SQL numérotées
index.php       Point d’entrée et routes
```

Pas de dépendances Composer à ce stade. Les fonctionnalités métier, composants graphiques et images de fond seront introduits module par module après validation du socle.

## Comptes et authentification

- `GET /inscription` et `POST /inscription` créent un compte étudiant et son profil initial (prénom, nom, e-mail, mot de passe haché).
- `GET /connexion` et `POST /connexion` authentifient un compte actif; le rôle est conservé dans la session.
- `POST /deconnexion` invalide la session et exige un jeton CSRF valide.

Les sessions utilisent des cookies `HttpOnly` et `SameSite=Lax`, le mode strict PHP et la régénération de l’identifiant lors de l’authentification. Les contrôles de formulaire sont réalisés côté serveur; les erreurs de connexion ne révèlent pas si l’adresse ou le mot de passe est incorrect. Aucun compte administrateur par défaut n’est créé.
