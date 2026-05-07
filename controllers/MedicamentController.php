<?php
// ============================================================
// controllers/MedicamentController.php
// Contrôleur des médicaments — Gestion du suivi médicamenteux
// ============================================================

require_once BASE_PATH . '/models/Medicament.php';

class MedicamentController {

    private $pdo;
    private $medicamentModel;

    public function __construct($pdo) {
        $this->pdo             = $pdo;
        $this->medicamentModel = new Medicament($pdo);
    }

    // ----------------------------------------------------------
    // liste() — Affiche la liste des médicaments + formulaire d'ajout
    // ----------------------------------------------------------
    public function liste() {
        $userId      = $_SESSION['utilisateur_id'];
        $message     = '';
        $erreur      = '';

        // Traitement du formulaire d'ajout
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_form'])) {
            if ($_POST['action_form'] === 'ajouter') {
                if (empty($_POST['nom'])) {
                    $erreur = 'Le nom du médicament est obligatoire.';
                } else {
                    $this->medicamentModel->ajouter($userId, $_POST);
                    $message = 'Médicament ajouté avec succès.';
                }
            }
        }

        $medicaments = $this->medicamentModel->listeDuJour($userId);

        require_once BASE_PATH . '/views/layout/header.php';
        require_once BASE_PATH . '/views/layout/nav.php';
        require_once BASE_PATH . '/views/medicaments/liste.php';
        require_once BASE_PATH . '/views/layout/footer.php';
    }

    // ----------------------------------------------------------
    // prise() — Bascule l'état de prise d'un médicament (coché/décoché)
    // Appelé via un lien ou un formulaire AJAX-like (redirection après)
    // ----------------------------------------------------------
    public function prise() {
        $userId       = $_SESSION['utilisateur_id'];
        $medicamentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($medicamentId > 0) {
            $this->medicamentModel->togglePrise($medicamentId, $userId);
        }

        // Redirection vers la liste (pattern Post-Redirect-Get)
        header('Location: ' . BASE_URL . '/index.php?page=medicaments&action=liste');
        exit;
    }

    // ----------------------------------------------------------
    // desactiver() — Archive un médicament (ne le supprime pas)
    // ----------------------------------------------------------
    public function desactiver() {
        $userId       = $_SESSION['utilisateur_id'];
        $medicamentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($medicamentId > 0) {
            $this->medicamentModel->desactiver($medicamentId, $userId);
        }

        header('Location: ' . BASE_URL . '/index.php?page=medicaments&action=liste');
        exit;
    }

    public function index() {
        $this->liste();
    }
}
