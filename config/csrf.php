<?php
// Protection CSRF : un jeton secret par session, renvoyé dans chaque
// formulaire et revérifié à la soumission.

// Jeton de la session (généré une seule fois)
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Champ caché à insérer dans les formulaires
function csrf_champ() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

// À appeler avant de traiter un POST : bloque si le jeton ne correspond pas
function csrf_verifier() {
    $recu = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $recu)) {
        http_response_code(403);
        die('Erreur de sécurité : jeton CSRF invalide. Rechargez la page et réessayez.');
    }
}

// À appeler avant de traiter un GET sensible (ex: suppression, action rapide)
function csrf_verifier_get() {
    $recu = $_GET['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $recu)) {
        http_response_code(403);
        die('Erreur de sécurité : jeton CSRF invalide (GET). Rechargez la page.');
    }
}
