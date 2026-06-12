<?php
// ============================================================
// models/Bilan.php
// Modèle Bilan Biologique — Requêtes sur la table 'bilans_biologiques'
// ============================================================

class Bilan {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ----------------------------------------------------------
    // CRÉER un nouveau bilan (CREATE du CRUD)
    // Les marqueurs non renseignés sont stockés NULL (et non zéro)
    // ----------------------------------------------------------
    public function ajouter($utilisateurId, array $data) {
        $sql = "INSERT INTO bilans_biologiques
                    (utilisateur_id, date_bilan, tsh, t3_libre, t4_libre,
                     ferritine, vitamine_d, anticorps_tpo, notes)
                VALUES
                    (:uid, :date, :tsh, :t3, :t4, :ferritine, :vitd, :tpo, :notes)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':uid'       => (int)$utilisateurId,
            ':date'      => $data['date_bilan'],
            ':tsh'       => !empty($data['tsh'])          ? (float)$data['tsh']          : null,
            ':t3'        => !empty($data['t3_libre'])      ? (float)$data['t3_libre']      : null,
            ':t4'        => !empty($data['t4_libre'])      ? (float)$data['t4_libre']      : null,
            ':ferritine' => !empty($data['ferritine'])     ? (float)$data['ferritine']     : null,
            ':vitd'      => !empty($data['vitamine_d'])   ? (float)$data['vitamine_d']   : null,
            ':tpo'       => !empty($data['anticorps_tpo']) ? (float)$data['anticorps_tpo'] : null,
            ':notes'     => htmlspecialchars($data['notes'] ?? ''),
        ]);

        return $this->pdo->lastInsertId();
    }

    // ----------------------------------------------------------
    // LIRE l'historique des bilans (READ)
    // ----------------------------------------------------------
    public function historique($utilisateurId) {
        $sql  = "SELECT * FROM bilans_biologiques
                 WHERE utilisateur_id = :uid
                 ORDER BY date_bilan DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$utilisateurId]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------
    // LIRE le dernier bilan uniquement (READ — pour le dashboard)
    // ----------------------------------------------------------
    public function dernier($utilisateurId) {
        $sql  = "SELECT * FROM bilans_biologiques
                 WHERE utilisateur_id = :uid
                 ORDER BY date_bilan DESC
                 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$utilisateurId]);
        return $stmt->fetch();
    }

    // ----------------------------------------------------------
    // LIRE les données pour le graphique d'évolution TSH
    // 10 derniers bilans, ordre chronologique (du plus ancien au plus récent)
    // ----------------------------------------------------------
    public function donneesGraphique($utilisateurId) {
        $sql  = "SELECT date_bilan, tsh, t4_libre, vitamine_d
                 FROM bilans_biologiques
                 WHERE utilisateur_id = :uid
                 ORDER BY date_bilan ASC
                 LIMIT 10";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$utilisateurId]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------
    // SUPPRIMER un bilan (DELETE du CRUD)
    // La double condition (id ET utilisateur_id) empêche la suppression
    // des données d'un autre patient.
    // ----------------------------------------------------------
    public function supprimer($id, $utilisateurId) {
        $sql  = "DELETE FROM bilans_biologiques WHERE id = :id AND utilisateur_id = :uid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => (int)$id, ':uid' => (int)$utilisateurId]);
    }

    // ----------------------------------------------------------
    // Vérifie si une valeur TSH est dans la norme (pour coloration dashboard)
    // Norme de référence : 0,4 – 4,0 mUI/L
    // Retourne 'normal', 'eleve' ou 'bas'
    // ----------------------------------------------------------
    public static function analyserTSH($valeur) {
        if ($valeur === null) return 'inconnu';
        if ($valeur < 0.4)   return 'bas';
        if ($valeur > 4.0)   return 'eleve';
        return 'normal';
    }
}
