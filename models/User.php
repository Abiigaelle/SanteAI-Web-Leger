<?php
// ============================================================
// models/User.php
// Modèle Utilisateur — Toutes les requêtes sur la table 'utilisateurs'
//
// Dans le pattern MVC, le Modèle gère exclusivement l'accès aux données.
// Il n'affiche rien et n'applique pas de logique d'interface.
// Il expose des méthodes simples que les contrôleurs appellent.
// ============================================================

class User {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ----------------------------------------------------------
    // CRÉER un nouvel utilisateur (CREATE du CRUD)
    // Le mot de passe est hashé avec bcrypt (PASSWORD_DEFAULT).
    // password_hash() est irréversible : on ne peut pas retrouver
    // le mot de passe original depuis le hash.
    // ----------------------------------------------------------
    public function creer($nom, $prenom, $email, $motDePasse, $dateNaissance = null, $sexe = 'F') {
        $hash = password_hash($motDePasse, PASSWORD_DEFAULT);

        $sql = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, date_naissance, sexe)
                VALUES (:nom, :prenom, :email, :mdp, :ddn, :sexe)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nom'    => htmlspecialchars(trim($nom)),
            ':prenom' => htmlspecialchars(trim($prenom)),
            ':email'  => strtolower(trim($email)),
            ':mdp'    => $hash,
            ':ddn'    => $dateNaissance ?: null,
            ':sexe'   => $sexe,
        ]);

        return $this->pdo->lastInsertId();
    }

    // ----------------------------------------------------------
    // LIRE un utilisateur par email (READ — utilisé à la connexion)
    // ----------------------------------------------------------
    public function trouverParEmail($email) {
        $sql  = "SELECT * FROM utilisateurs WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => strtolower(trim($email))]);
        return $stmt->fetch();
    }

    // ----------------------------------------------------------
    // LIRE un utilisateur par ID (READ — utilisé partout via la session)
    // ----------------------------------------------------------
    public function trouverParId($id) {
        $sql  = "SELECT * FROM utilisateurs WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch();
    }

    // ----------------------------------------------------------
    // METTRE À JOUR le profil (UPDATE du CRUD)
    // htmlspecialchars() encode les caractères spéciaux pour éviter le XSS
    // ----------------------------------------------------------
    public function mettreAJour($id, array $data) {
        $sql = "UPDATE utilisateurs
                SET nom = :nom, prenom = :prenom, date_naissance = :ddn,
                    medecin_nom = :medecin, avatar = :avatar
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nom'     => htmlspecialchars(trim($data['nom'])),
            ':prenom'  => htmlspecialchars(trim($data['prenom'])),
            ':ddn'     => !empty($data['date_naissance']) ? $data['date_naissance'] : null,
            ':medecin' => htmlspecialchars(trim($data['medecin_nom'] ?? '')),
            ':avatar'  => $data['avatar'] ?? 'avatar1',
            ':id'      => (int)$id,
        ]);
    }

    // ----------------------------------------------------------
    // Vérifie si un email est déjà utilisé (pour éviter les doublons)
    // ----------------------------------------------------------
    public function emailExiste($email) {
        $sql  = "SELECT COUNT(*) FROM utilisateurs WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => strtolower(trim($email))]);
        return $stmt->fetchColumn() > 0;
    }

    // ----------------------------------------------------------
    // Vue d'ensemble des patients pour l'espace admin (via la vue SQL)
    // ----------------------------------------------------------
    public function obtenirSanteRecente() {
        $sql  = "SELECT * FROM vue_sante_recente WHERE utilisateur_id NOT IN (SELECT id FROM utilisateurs WHERE role = 'admin')";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
}
