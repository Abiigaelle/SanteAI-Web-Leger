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
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

try {
    // Création de la connexion PDO
    // Le DSN (Data Source Name) précise le driver, l'hôte, la BDD et l'encodage
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            // Toutes les erreurs SQL déclenchent une exception → plus facile à déboguer
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Résultats retournés sous forme de tableau associatif (clé = nom de colonne)
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Vraies requêtes préparées MySQL — pas d'émulation côté PHP
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // En production, on n'affiche JAMAIS l'erreur brute (fuite d'information)
    die("Erreur de connexion à la base de données. Vérifiez que XAMPP est lancé et que la BDD 'santeai' existe.");
}
