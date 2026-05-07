<?php
// ============================================================
// models/Recommandation.php
// Modèle Recommandation + Algorithme d'analyse
//
// L'algorithme de recommandations est léger et basé sur des règles
// métier simples ("if/else"). C'est volontairement simple pour
// rester explicable à l'oral du BTS SIO.
// Les triggers SQL dans santeai.sql font une partie du travail
// (analyse immédiate à l'insertion). Ce modèle complète avec une
// analyse périodique à l'ouverture du dashboard.
// ============================================================

class Recommandation {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ----------------------------------------------------------
    // ALGORITHME DE RECOMMANDATIONS
    // Appelé à chaque ouverture du dashboard.
    // Analyse les données récentes et génère des conseils si nécessaire.
    // Pour éviter les doublons, on vérifie si le conseil n'a pas déjà
    // été généré dans la journée (ajouterSiAbsent).
    // ----------------------------------------------------------
    public function genererRecommandations($utilisateurId) {

        // --- RÈGLE 1 : Fatigue élevée depuis 3 jours consécutifs ---
        $sql = "SELECT COUNT(*) AS nb_jours
                FROM symptomes
                WHERE utilisateur_id = :uid
                  AND niveau_fatigue >= 4
                  AND date_saisie >= DATE_SUB(CURDATE(), INTERVAL 3 DAY)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $utilisateurId]);
        $r = $stmt->fetch();

        if ($r['nb_jours'] >= 3) {
            $this->ajouterSiAbsent(
                $utilisateurId,
                'Votre fatigue est persistante depuis au moins 3 jours. Consultez votre médecin et assurez-vous de bien dormir.',
                'alerte'
            );
        }

        // --- RÈGLE 2 : Médicaments souvent non pris cette semaine ---
        $sql = "SELECT COUNT(*) AS nb_oublis
                FROM prises_medicaments pm
                JOIN medicaments m ON pm.medicament_id = m.id
                WHERE pm.utilisateur_id = :uid
                  AND pm.pris = 0
                  AND pm.date_prise >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  AND m.actif = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $utilisateurId]);
        $r = $stmt->fetch();

        if ($r['nb_oublis'] >= 3) {
            $this->ajouterSiAbsent(
                $utilisateurId,
                'Vous avez oublié de prendre vos médicaments plusieurs fois cette semaine. La régularité du traitement Hashimoto est essentielle.',
                'alerte'
            );
        }

        // --- RÈGLE 3 : Rappel si aucun bilan depuis 2 mois ---
        $sql = "SELECT MAX(date_bilan) AS dernier
                FROM bilans_biologiques
                WHERE utilisateur_id = :uid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $utilisateurId]);
        $r = $stmt->fetch();

        if (!$r['dernier'] || $r['dernier'] < date('Y-m-d', strtotime('-2 months'))) {
            $this->ajouterSiAbsent(
                $utilisateurId,
                'Pensez à faire un bilan sanguin. Aucun bilan récent n\'a été enregistré depuis plus de 2 mois.',
                'conseil'
            );
        }

        // --- RÈGLE 4 : Conseil si humeur toujours basse ---
        $sql = "SELECT AVG(niveau_humeur) AS moyenne
                FROM symptomes
                WHERE utilisateur_id = :uid
                  AND date_saisie >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $utilisateurId]);
        $r = $stmt->fetch();

        if ($r['moyenne'] !== null && $r['moyenne'] < 2.5) {
            $this->ajouterSiAbsent(
                $utilisateurId,
                'Votre humeur est globalement basse cette semaine. Un accompagnement psychologique peut être bénéfique en complément du traitement médical.',
                'conseil'
            );
        }

        // TODO [EXTENSION FUTURE] : Ajouter une règle sur la variabilité du poids
        // TODO [EXTENSION FUTURE] : Intégrer un score global de bien-être (0-100)
    }

    // ----------------------------------------------------------
    // Méthode interne : ajoute un conseil uniquement s'il n'existe
    // pas déjà pour la journée en cours (évite le spam de doublons).
    // ----------------------------------------------------------
    private function ajouterSiAbsent($utilisateurId, $message, $type) {
        $sql  = "SELECT id FROM recommandations
                 WHERE utilisateur_id = :uid
                   AND message = :msg
                   AND DATE(created_at) = CURDATE()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $utilisateurId, ':msg' => $message]);

        // Si ce conseil n'existe pas encore aujourd'hui → on l'insère
        if (!$stmt->fetch()) {
            $sql  = "INSERT INTO recommandations (utilisateur_id, message, type)
                     VALUES (:uid, :msg, :type)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':uid' => $utilisateurId, ':msg' => $message, ':type' => $type]);
        }
    }

    // ----------------------------------------------------------
    // LIRE les recommandations non lues (READ — pour le dashboard)
    // ----------------------------------------------------------
    public function nonLues($utilisateurId, $limite = 5) {
        $sql  = "SELECT * FROM recommandations
                 WHERE utilisateur_id = :uid AND lu = 0
                 ORDER BY created_at DESC
                 LIMIT :limite";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':uid', (int)$utilisateurId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------
    // Marquer toutes les recommandations comme lues (UPDATE)
    // ----------------------------------------------------------
    public function marquerLues($utilisateurId) {
        $sql  = "UPDATE recommandations SET lu = 1
                 WHERE utilisateur_id = :uid AND lu = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$utilisateurId]);
    }

    // ----------------------------------------------------------
    // LIRE tout l'historique des recommandations (READ)
    // ----------------------------------------------------------
    public function toutes($utilisateurId) {
        $sql  = "SELECT * FROM recommandations
                 WHERE utilisateur_id = :uid
                 ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$utilisateurId]);
        return $stmt->fetchAll();
    }
}
