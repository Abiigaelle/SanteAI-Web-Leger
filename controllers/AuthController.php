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
            $email     = trim($_POST['email'] ?? '');
            $motDePasse = $_POST['mot_de_passe'] ?? '';

            // Validation : les deux champs sont obligatoires
            if (empty($email) || empty($motDePasse)) {
                $erreur = 'Veuillez remplir tous les champs.';
            } else {
                $utilisateur = $this->userModel->trouverParEmail($email);

                // password_verify() compare le mot de passe saisi avec le hash en BDD
                // C'est la seule méthode sécurisée : on ne peut pas déhasher bcrypt.
                if ($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
                    // Connexion réussie : on stocke les infos en session
                    $_SESSION['utilisateur_id'] = $utilisateur['id'];
                    $_SESSION['nom']            = $utilisateur['nom'];
                    $_SESSION['prenom']         = $utilisateur['prenom'];
                    $_SESSION['email']          = $utilisateur['email'];
                    $_SESSION['avatar']         = $utilisateur['avatar'];
                    $_SESSION['pathologie']     = $utilisateur['pathologie'];
                    $_SESSION['medecin_nom']    = $utilisateur['medecin_nom'];

                    // Redirection vers le dashboard
                    header('Location: ' . BASE_URL . '/index.php?page=dashboard');
                    exit;
                } else {
                    // Message volontairement vague pour ne pas révéler si l'email existe
                    $erreur = 'Email ou mot de passe incorrect.';
                }
            }
        }

        // Affichage de la vue login
        require_once BASE_PATH . '/views/auth/login.php';
    }

    // ----------------------------------------------------------
    // register() — Affiche le formulaire d'inscription (GET) ou crée le compte (POST)
    // ----------------------------------------------------------
    public function register() {
        $erreur  = '';
        $succes  = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom        = trim($_POST['nom'] ?? '');
            $prenom     = trim($_POST['prenom'] ?? '');
            $email      = trim($_POST['email'] ?? '');
            $mdp        = $_POST['mot_de_passe'] ?? '';
            $mdpConfirm = $_POST['mot_de_passe_confirm'] ?? '';
            $ddn        = $_POST['date_naissance'] ?? null;
            $sexe       = $_POST['sexe'] ?? 'F';

            // Validations côté serveur (même si le HTML a des contrôles,
            // on ne fait jamais confiance au navigateur seul)
            if (empty($nom) || empty($prenom) || empty($email) || empty($mdp)) {
                $erreur = 'Tous les champs obligatoires doivent être remplis.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erreur = 'L\'adresse email n\'est pas valide.';
            } elseif (strlen($mdp) < 6) {
                $erreur = 'Le mot de passe doit contenir au moins 6 caractères.';
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
        // session_unset() vide toutes les variables de session
        session_unset();
        // session_destroy() détruit le fichier de session côté serveur
        session_destroy();
        header('Location: ' . BASE_URL . '/index.php?page=auth&action=login');
        exit;
    }

    // Action par défaut (redirection vers login)
    public function index() {
        $this->login();
    }
}
