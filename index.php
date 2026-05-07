<?php
// ============================================================
// index.php — Point d'entrée unique de l'application (Front Controller)
// ============================================================

// Activation des erreurs en développement — à désactiver en production
error_reporting(E_ALL);
ini_set('display_errors', 1);

// BASE_URL = chemin depuis la racine du serveur web jusqu'à ce dossier
// Ex : si le site est à http://localhost/santeai/, BASE_URL = '/santeai'
// Sert à construire des redirections absolues qui marchent partout.
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
define('BASE_URL', rtrim($scriptDir === DIRECTORY_SEPARATOR ? '' : $scriptDir, '/'));

// Forcer le cookie de session sur le bon chemin pour éviter les pertes de session
// lors de la navigation entre les pages sous Apache/XAMPP
session_set_cookie_params([
    'lifetime' => 0,          // Cookie de session (expire à la fermeture du navigateur)
    'path'     => BASE_URL ?: '/',  // Même chemin que le site
    'httponly' => true,        // Cookie inaccessible depuis JavaScript (sécurité)
    'samesite' => 'Lax',       // Protection CSRF basique
]);
session_start();

// BASE_PATH = chemin absolu vers la racine du projet sur le disque
define('BASE_PATH', __DIR__);

// Connexion à la base de données (crée la variable $pdo)
require_once BASE_PATH . '/config/database.php';

// ============================================================
// ROUTAGE
// ============================================================
$page   = $_GET['page']   ?? 'auth';
$action = $_GET['action'] ?? 'index';

// Sécurisation : seulement des lettres (empêche Path Traversal)
$page   = preg_replace('/[^a-zA-Z]/', '', $page);
$action = preg_replace('/[^a-zA-Z]/', '', $action);

// Pages accessibles sans être connecté
$pagesPubliques = ['auth'];

// Vérification d'authentification — redirection absolue si non connecté
if (!in_array($page, $pagesPubliques) && !isset($_SESSION['utilisateur_id'])) {
    header('Location: ' . BASE_URL . '/index.php?page=auth&action=login');
    exit;
}

// Chargement du contrôleur
$fichierControleur = BASE_PATH . '/controllers/' . ucfirst($page) . 'Controller.php';

if (file_exists($fichierControleur)) {
    require_once $fichierControleur;
    $nomControleur = ucfirst($page) . 'Controller';
    $controleur    = new $nomControleur($pdo);

    if (method_exists($controleur, $action)) {
        $controleur->$action();
    } else {
        $controleur->index();
    }
} else {
    header('Location: ' . BASE_URL . '/index.php?page=auth&action=login');
    exit;
}
