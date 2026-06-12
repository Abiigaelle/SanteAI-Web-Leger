<?php
// ============================================================
// models/Recommandation.php
// Algorithme de recommandations
//
// Algorithme basé sur des règles métier simples (if/else).
// Les triggers SQL dans santeai.sql génèrent des alertes immédiates
// à l'insertion. Ce modèle complète avec une analyse périodique
// à chaque ouverture du dashboard.
// ============================================================

class Recommandation {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ----------------------------------------------------------
    // Analyse les données récentes et génère les conseils manquants.
    // Appelé à chaque ouverture du dashboard.
    // ajouterSiAbsent() évite les doublons (un conseil par jour max).
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
    }

    // ----------------------------------------------------------
    // Ajoute un conseil uniquement s'il n'existe pas déjà aujourd'hui
    // (évite le spam de doublons à chaque rechargement du dashboard)
    // ----------------------------------------------------------
    private function ajouterSiAbsent($utilisateurId, $message, $type) {
        $sql  = "SELECT id FROM recommandations
                 WHERE utilisateur_id = :uid
                   AND message = :msg
                   AND DATE(created_at) = CURDATE()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $utilisateurId, ':msg' => $message]);

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

    public function marquerLues($utilisateurId) {
        $sql  = "UPDATE recommandations SET lu = 1
                 WHERE utilisateur_id = :uid AND lu = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$utilisateurId]);
    }

    public function toutes($utilisateurId) {
        $sql  = "SELECT * FROM recommandations
                 WHERE utilisateur_id = :uid
                 ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$utilisateurId]);
        return $stmt->fetchAll();
    }
}
