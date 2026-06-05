<?php
// ============================================================
// controllers/DashboardController.php
// Contrôleur du tableau de bord principal
//
// Le dashboard est la page centrale de l'application.
// Il agrège les données de plusieurs modèles pour donner une
// vue d'ensemble de la santé du patient.
// ============================================================

require_once BASE_PATH . '/models/Symptome.php';
require_once BASE_PATH . '/models/Bilan.php';
require_once BASE_PATH . '/models/Medicament.php';
require_once BASE_PATH . '/models/Recommandation.php';
require_once BASE_PATH . '/models/User.php';

class DashboardController {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ----------------------------------------------------------
    // index() — Charge et affiche le tableau de bord
    // ----------------------------------------------------------
    public function index() {
        $userId = $_SESSION['utilisateur_id'];
        $role   = $_SESSION['role'] ?? 'patient';

        if ($role === 'admin') {
            $userModel = new User($this->pdo);
            $patients  = $userModel->obtenirSanteRecente();

            require_once BASE_PATH . '/views/layout/header.php';
            require_once BASE_PATH . '/views/layout/nav.php';
            require_once BASE_PATH . '/views/dashboard/admin.php';
            require_once BASE_PATH . '/views/layout/footer.php';
            exit;
        }

        // Instanciation des modèles nécessaires
        $symptomeModel      = new Symptome($this->pdo);
        $bilanModel         = new Bilan($this->pdo);
        $medicamentModel    = new Medicament($this->pdo);
        $recommandationModel = new Recommandation($this->pdo);

        // --- DONNÉES DU DASHBOARD ---

        // Saisie des symptômes d'aujourd'hui (peut être null si pas encore saisie)
        $dernierSymptome = $symptomeModel->saisieAujourdhui($userId);

        // Dernier bilan biologique disponible
        $dernierBilan = $bilanModel->dernier($userId);

        // Médicaments du jour avec statut de prise
        $medicamentsDuJour = $medicamentModel->listeDuJour($userId);

        // Taux d'adhérence médicamenteuse sur 7 jours (en %)
        $tauxAdherence = $medicamentModel->tauxAdherence($userId);

        // --- ALGORITHME DE RECOMMANDATIONS ---
        // Analyse les données récentes et génère de nouveaux conseils si nécessaire.
        // Les triggers SQL génèrent des alertes immédiates (à l'insertion),
        // cet algorithme fait une analyse périodique à chaque connexion.
        $recommandationModel->genererRecommandations($userId);

        // Récupération des recommandations non lues (max 5)
        $recommandations = $recommandationModel->nonLues($userId);

        // Marquer les recommandations affichées comme lues
        $recommandationModel->marquerLues($userId);

        // --- DONNÉES POUR LES GRAPHIQUES CHART.JS ---
        // On encode les données PHP en JSON pour les passer au JavaScript.
        // json_encode() convertit un tableau PHP en chaîne JSON.

        // Graphique 1 : Évolution fatigue/humeur sur 30 jours
        $donneesSymptomes = $symptomeModel->donneesGraphique($userId);
        $symptomesJSON    = json_encode($donneesSymptomes);

        // Graphique 2 : Évolution TSH sur les derniers bilans
        $donneesBilans = $bilanModel->donneesGraphique($userId);
        $bilansJSON    = json_encode($donneesBilans);

        // Analyse de la TSH pour l'affichage coloré
        $statutTSH = $dernierBilan ? Bilan::analyserTSH($dernierBilan['tsh']) : 'inconnu';

        // --- AFFICHAGE ---
        require_once BASE_PATH . '/views/layout/header.php';
        require_once BASE_PATH . '/views/layout/nav.php';
        require_once BASE_PATH . '/views/dashboard/index.php';
        require_once BASE_PATH . '/views/layout/footer.php';
    }
}
