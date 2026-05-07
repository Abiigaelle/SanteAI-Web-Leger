<?php
// ============================================================
// config/database.php
// Connexion à la base de données via PDO
//
// PDO (PHP Data Objects) est l'interface recommandée pour accéder
// à une base de données en PHP. Ses avantages :
//   - Protection contre les injections SQL (via prepare/execute)
//   - Compatible avec plusieurs SGBD (MySQL, PostgreSQL, SQLite...)
//   - Gestion des erreurs par exceptions (plus facile à déboguer)
// ============================================================

// Paramètres de connexion (à modifier selon l'environnement)
define('DB_HOST',    'localhost');
define('DB_NAME',    'santeai');
define('DB_USER',    'root');    // Utilisateur par défaut XAMPP/WAMP
define('DB_PASS',    '');        // Mot de passe vide par défaut XAMPP
define('DB_CHARSET', 'utf8mb4'); // utf8mb4 supporte les emojis et caractères spéciaux

try {
    // Création de la connexion PDO
    // Le DSN (Data Source Name) contient : driver:host=...;dbname=...;charset=...
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            // Toutes les erreurs SQL déclenchent une exception → plus facile à déboguer
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Les résultats sont retournés sous forme de tableaux associatifs (clé = nom de colonne)
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Utilise les vraies requêtes préparées du serveur MySQL (plus sécurisé)
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // En production, on n'affiche JAMAIS l'erreur brute (fuite d'information)
    // En développement, vous pouvez décommenter la ligne ci-dessous pour déboguer :
    // echo $e->getMessage();
    die("Erreur de connexion à la base de données. Vérifiez que XAMPP est lancé et que la BDD 'santeai' existe.");
}
