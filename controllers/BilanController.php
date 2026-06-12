<?php
// ============================================================
// controllers/BilanController.php
// Contrôleur des bilans biologiques — CRUD complet
// ============================================================

require_once BASE_PATH . '/models/Bilan.php';

class BilanController {

    private $pdo;
    private $bilanModel;

    public function __construct($pdo) {
        $this->pdo        = $pdo;
        $this->bilanModel = new Bilan($pdo);
    }

    // ----------------------------------------------------------
    // ajouter() — Formulaire de saisie d'un nouveau bilan (GET/POST)
    // ----------------------------------------------------------
    public function ajouter() {
        $userId  = $_SESSION['utilisateur_id'];
        $message = '';
        $erreur  = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verifier();

            if (empty($_POST['date_bilan'])) {
                $erreur = 'La date du bilan est obligatoire.';
            } else {
                $this->bilanModel->ajouter($userId, $_POST);
                $message = 'Bilan biologique enregistré avec succès.';
            }
        }

        require_once BASE_PATH . '/views/layout/header.php';
        require_once BASE_PATH . '/views/layout/nav.php';
        require_once BASE_PATH . '/views/bilans/ajouter.php';
        require_once BASE_PATH . '/views/layout/footer.php';
    }

    // ----------------------------------------------------------
    // historique() — Liste tous les bilans du patient
    // ----------------------------------------------------------
    public function historique() {
        $userId = $_SESSION['utilisateur_id'];
        $bilans = $this->bilanModel->historique($userId);

        require_once BASE_PATH . '/views/layout/header.php';
        require_once BASE_PATH . '/views/layout/nav.php';
        require_once BASE_PATH . '/views/bilans/historique.php';
        require_once BASE_PATH . '/views/layout/footer.php';
    }

    // ----------------------------------------------------------
    // supprimer() — Supprime un bilan (DELETE du CRUD)
    // ----------------------------------------------------------
    public function supprimer() {
        csrf_verifier_get();

        $userId = $_SESSION['utilisateur_id'];
        $id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            $this->bilanModel->supprimer($id, $userId);
        }

        header('Location: ' . BASE_URL . '/index.php?page=bilans&action=historique');
        exit;
    }

    public function index() {
        $this->historique();
    }
}
