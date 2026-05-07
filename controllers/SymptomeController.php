<?php
// ============================================================
// controllers/SymptomeController.php
// Contrôleur des symptômes — Saisie quotidienne et historique
// Implémente le CRUD complet : CREATE (ajouter), READ (historique), DELETE (supprimer)
// UPDATE est intégré dans ajouter() : si une saisie du jour existe, on la remplace.
// ============================================================

require_once BASE_PATH . '/models/Symptome.php';

class SymptomeController {

    private $pdo;
    private $symptomeModel;

    public function __construct($pdo) {
        $this->pdo           = $pdo;
        $this->symptomeModel = new Symptome($pdo);
    }

    // ----------------------------------------------------------
    // ajouter() — Formulaire de saisie quotidienne (GET) ou enregistrement (POST)
    // ----------------------------------------------------------
    public function ajouter() {
        $userId  = $_SESSION['utilisateur_id'];
        $message = '';
        $erreur  = '';

        // Récupérer la saisie d'aujourd'hui pour pré-remplir le formulaire
        $saisieExistante = $this->symptomeModel->saisieAujourdhui($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation : fatigue et humeur sont obligatoires
            if (empty($_POST['niveau_fatigue']) || empty($_POST['niveau_humeur'])) {
                $erreur = 'Veuillez indiquer votre niveau de fatigue et votre humeur.';
            } elseif ($_POST['niveau_fatigue'] < 1 || $_POST['niveau_fatigue'] > 5) {
                $erreur = 'Le niveau de fatigue doit être entre 1 et 5.';
            } else {
                $this->symptomeModel->sauvegarder($userId, $_POST);
                $message = $saisieExistante
                    ? 'Votre saisie du jour a été mise à jour.'
                    : 'Vos symptômes ont été enregistrés avec succès.';
                // On recharge la saisie pour afficher les valeurs mises à jour
                $saisieExistante = $this->symptomeModel->saisieAujourdhui($userId);
            }
        }

        require_once BASE_PATH . '/views/layout/header.php';
        require_once BASE_PATH . '/views/layout/nav.php';
        require_once BASE_PATH . '/views/symptomes/ajouter.php';
        require_once BASE_PATH . '/views/layout/footer.php';
    }

    // ----------------------------------------------------------
    // historique() — Affiche l'historique des saisies
    // ----------------------------------------------------------
    public function historique() {
        $userId    = $_SESSION['utilisateur_id'];
        $symptomes = $this->symptomeModel->historique($userId, 60);

        require_once BASE_PATH . '/views/layout/header.php';
        require_once BASE_PATH . '/views/layout/nav.php';
        require_once BASE_PATH . '/views/symptomes/historique.php';
        require_once BASE_PATH . '/views/layout/footer.php';
    }

    // ----------------------------------------------------------
    // supprimer() — Supprime une saisie (DELETE du CRUD)
    // Action déclenchée via un lien GET avec l'ID de la saisie.
    // ----------------------------------------------------------
    public function supprimer() {
        $userId = $_SESSION['utilisateur_id'];
        $id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            $this->symptomeModel->supprimer($id, $userId);
        }

        // Redirection après suppression (pattern Post-Redirect-Get)
        header('Location: ' . BASE_URL . '/index.php?page=symptomes&action=historique&message=supprime');
        exit;
    }

    public function index() {
        $this->ajouter();
    }
}
