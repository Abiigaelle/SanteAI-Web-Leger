<?php
// ============================================================
// controllers/PatientController.php
// Contrôleur du profil patient — Informations personnelles
// et personnalisation graphique (choix d'illustration)
// ============================================================

require_once BASE_PATH . '/models/User.php';

class PatientController {

    private $pdo;
    private $userModel;

    public function __construct($pdo) {
        $this->pdo       = $pdo;
        $this->userModel = new User($pdo);
    }

    // ----------------------------------------------------------
    // profil() — Affiche et met à jour le profil (GET/POST)
    // ----------------------------------------------------------
    public function profil() {
        $userId  = $_SESSION['utilisateur_id'];
        $message = '';
        $erreur  = '';

        // Charger les données actuelles du patient
        $utilisateur = $this->userModel->trouverParId($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verifier();

            if (empty($_POST['nom']) || empty($_POST['prenom'])) {
                $erreur = 'Le nom et le prénom sont obligatoires.';
            } else {
                $this->userModel->mettreAJour($userId, $_POST);

                // Mise à jour de la session pour un affichage immédiat sans reconnexion
                $_SESSION['nom']         = htmlspecialchars(trim($_POST['nom']));
                $_SESSION['prenom']      = htmlspecialchars(trim($_POST['prenom']));
                $_SESSION['avatar']      = $_POST['avatar'] ?? 'avatar1';
                $_SESSION['medecin_nom'] = htmlspecialchars(trim($_POST['medecin_nom'] ?? ''));

                $message     = 'Profil mis à jour avec succès.';
                $utilisateur = $this->userModel->trouverParId($userId);
            }
        }

        // Illustrations proposées pour la personnalisation
        $avatarsDisponibles = [
            'avatar1' => ['emoji' => '🌸', 'label' => 'Fleur de cerisier'],
            'avatar2' => ['emoji' => '🌿', 'label' => 'Nature'],
            'avatar3' => ['emoji' => '⭐', 'label' => 'Étoile'],
            'avatar4' => ['emoji' => '🦋', 'label' => 'Papillon'],
            'avatar5' => ['emoji' => '🌊', 'label' => 'Vague'],
        ];

        require_once BASE_PATH . '/views/layout/header.php';
        require_once BASE_PATH . '/views/layout/nav.php';
        require_once BASE_PATH . '/views/patient/profil.php';
        require_once BASE_PATH . '/views/layout/footer.php';
    }

    public function index() {
        $this->profil();
    }
}
