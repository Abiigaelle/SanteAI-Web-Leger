<?php
// Point d'entrée unique de l'application (front controller MVC)

// Passer à true sur le serveur pour ne pas afficher les erreurs aux visiteurs (sécurité)
define('MODE_PRODUCTION', false);

if (MODE_PRODUCTION) {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Chemin du site depuis la racine du serveur (ex : '/santeai'), pour les redirections
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
define('BASE_URL', rtrim($scriptDir === DIRECTORY_SEPARATOR ? '' : $scriptDir, '/'));

// Cookie de session nommé et limité à ce site pour éviter les conflits sur localhost
session_name('SANTEAI_SESSID');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,   // cookie inaccessible en JavaScript
    'samesite' => 'Lax',
]);
session_start();

define('BASE_PATH', __DIR__);

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/csrf.php';

// --- Routage ---
$page   = $_GET['page']   ?? 'auth';
$action = $_GET['action'] ?? 'index';

// On ne garde que des lettres pour éviter le path traversal
$page   = preg_replace('/[^a-zA-Z]/', '', $page);
$action = preg_replace('/[^a-zA-Z]/', '', $action);

$pagesPubliques = ['auth'];

// Redirection vers la connexion si l'utilisateur n'est pas authentifié
if (!in_array($page, $pagesPubliques) && !isset($_SESSION['utilisateur_id'])) {
    header('Location: ' . BASE_URL . '/index.php?page=auth&action=login');
    exit;
}

// Accepte le pluriel dans l'URL (symptomes) en retrouvant le contrôleur au singulier
$nomPage = $page;
if (!file_exists(BASE_PATH . '/controllers/' . ucfirst($nomPage) . 'Controller.php') && substr($nomPage, -1) === 's') {
    $nomPage = substr($nomPage, 0, -1);
}

$fichierControleur = BASE_PATH . '/controllers/' . ucfirst($nomPage) . 'Controller.php';

if (file_exists($fichierControleur)) {
    require_once $fichierControleur;
    $nomControleur = ucfirst($nomPage) . 'Controller';
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
