<?php
// ============================================================
// controllers/AuthController.php
// Contrôleur d'authentification
//
// Dans le pattern MVC, le Contrôleur fait le lien entre le Modèle
// (données) et la Vue (interface). Il :
//   1. Reçoit la requête de l'utilisateur (GET ou POST)
//   2. Appelle le Modèle pour lire/écrire en BDD
//   3. Transmet les données à la Vue pour afficher le résultat
// ============================================================

require_once BASE_PATH . '/models/User.php';

class AuthController {

    private $pdo;
    private $userModel;

    public function __construct($pdo) {
        $this->pdo       = $pdo;
        $this->userModel = new User($pdo);
    }

    // ----------------------------------------------------------
    // login() — Affiche le formulaire (GET) ou traite la connexion (POST)
    // ----------------------------------------------------------
    public function login() {
        $erreur = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verifier();

            $email      = trim($_POST['email'] ?? '');
            $motDePasse = $_POST['mot_de_passe'] ?? '';

            if (empty($email) || empty($motDePasse)) {
                $erreur = 'Veuillez remplir tous les champs.';
            } else {
                $utilisateur = $this->userModel->trouverParEmail($email);

                // password_verify compare la saisie au hash bcrypt stocké
                // On ne peut pas déhasher bcrypt : c'est la seule méthode sécurisée
                if ($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
                    $_SESSION['utilisateur_id'] = $utilisateur['id'];
                    $_SESSION['nom']            = $utilisateur['nom'];
                    $_SESSION['prenom']         = $utilisateur['prenom'];
                    $_SESSION['email']          = $utilisateur['email'];
                    $_SESSION['avatar']         = $utilisateur['avatar'];
                    $_SESSION['pathologie']     = $utilisateur['pathologie'];
                    $_SESSION['medecin_nom']    = $utilisateur['medecin_nom'];
                    $_SESSION['role']           = $utilisateur['role'] ?? 'patient';

                    header('Location: ' . BASE_URL . '/index.php?page=dashboard');
                    exit;
                } else {
                    // Message volontairement vague (ne pas révéler si l'email existe)
                    $erreur = 'Email ou mot de passe incorrect.';
                }
            }
        }

        require_once BASE_PATH . '/views/auth/login.php';
    }

    // ----------------------------------------------------------
    // register() — Affiche le formulaire d'inscription (GET) ou crée le compte (POST)
    // ----------------------------------------------------------
    public function register() {
        $erreur  = '';
        $succes  = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verifier();

            $nom        = trim($_POST['nom'] ?? '');
            $prenom     = trim($_POST['prenom'] ?? '');
            $email      = trim($_POST['email'] ?? '');
            $mdp        = $_POST['mot_de_passe'] ?? '';
            $mdpConfirm = $_POST['mot_de_passe_confirm'] ?? '';
            $ddn        = $_POST['date_naissance'] ?? null;
            $sexe       = $_POST['sexe'] ?? 'F';

            // Validation côté serveur (on ne se fie pas aux contrôles HTML)
            if (empty($nom) || empty($prenom) || empty($email) || empty($mdp)) {
                $erreur = 'Tous les champs obligatoires doivent être remplis.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erreur = 'L\'adresse email n\'est pas valide.';
            } elseif (strlen($mdp) < 8) {
                $erreur = 'Le mot de passe doit contenir au moins 8 caractères.';
            } elseif ($mdp !== $mdpConfirm) {
                $erreur = 'Les deux mots de passe ne correspondent pas.';
            } elseif ($this->userModel->emailExiste($email)) {
                $erreur = 'Cette adresse email est déjà utilisée.';
            } else {
                // Tout est valide → création du compte
                $id = $this->userModel->creer($nom, $prenom, $email, $mdp, $ddn, $sexe);
                $succes = 'Compte créé avec succès ! Vous pouvez vous connecter.';
            }
        }

        require_once BASE_PATH . '/views/auth/register.php';
    }

    // ----------------------------------------------------------
    // logout() — Détruit la session et redirige vers le login
    // ----------------------------------------------------------
    public function logout() {
        session_unset();   // vide toutes les variables de session
        session_destroy(); // détruit le fichier de session côté serveur
        header('Location: ' . BASE_URL . '/index.php?page=auth&action=login');
        exit;
    }

    // Action par défaut (redirection vers login)
    public function index() {
        $this->login();
    }
}
