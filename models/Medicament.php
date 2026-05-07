<?php
// ============================================================
// models/Medicament.php
// Modèle Médicament — Requêtes sur 'medicaments' et 'prises_medicaments'
// Ce modèle gère deux tables liées :
//   - medicaments : la liste des traitements du patient
//   - prises_medicaments : le journal quotidien des prises
// ============================================================

class Medicament {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ----------------------------------------------------------
    // CRÉER un médicament (CREATE)
    // ----------------------------------------------------------
    public function ajouter($utilisateurId, array $data) {
        $sql = "INSERT INTO medicaments (utilisateur_id, nom, dosage, moment_prise)
                VALUES (:uid, :nom, :dosage, :moment)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':uid'    => (int)$utilisateurId,
            ':nom'    => htmlspecialchars(trim($data['nom'])),
            ':dosage' => htmlspecialchars(trim($data['dosage'] ?? '')),
            ':moment' => $data['moment_prise'] ?? 'matin',
        ]);
        return $this->pdo->lastInsertId();
    }

    // ----------------------------------------------------------
    // LIRE la liste des médicaments actifs du patient (READ)
    // ----------------------------------------------------------
    public function liste($utilisateurId) {
        $sql  = "SELECT * FROM medicaments
                 WHERE utilisateur_id = :uid AND actif = 1
                 ORDER BY moment_prise, nom";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$utilisateurId]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------
    // LIRE les médicaments du jour avec leur statut de prise (READ)
    // Utilise une jointure LEFT JOIN pour combiner la liste des
    // médicaments avec les prises du jour.
    // Si pris = NULL → le patient n'a pas encore coché (= non pris)
    // ----------------------------------------------------------
    public function listeDuJour($utilisateurId) {
        $sql = "SELECT m.id, m.nom, m.dosage, m.moment_prise,
                       COALESCE(p.pris, 0) AS pris,
                       p.id AS prise_id
                FROM medicaments m
                LEFT JOIN prises_medicaments p
                    ON p.medicament_id = m.id
                    AND p.utilisateur_id = m.utilisateur_id
                    AND p.date_prise = CURDATE()
                WHERE m.utilisateur_id = :uid AND m.actif = 1
                ORDER BY FIELD(m.moment_prise, 'matin', 'midi', 'soir', 'nuit')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$utilisateurId]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------
    // Basculer la prise du jour (cocher / décocher)
    // Si une prise n'existe pas encore → INSERT
    // Si elle existe → on inverse la valeur (pris = 1-pris)
    // ----------------------------------------------------------
    public function togglePrise($medicamentId, $utilisateurId) {
        // Chercher si une entrée existe pour aujourd'hui
        $sql  = "SELECT id, pris FROM prises_medicaments
                 WHERE medicament_id = :mid AND utilisateur_id = :uid AND date_prise = CURDATE()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':mid' => (int)$medicamentId, ':uid' => (int)$utilisateurId]);
        $existant = $stmt->fetch();

        if ($existant) {
            // Inverser la valeur : 0 → 1, 1 → 0
            $sql  = "UPDATE prises_medicaments SET pris = :pris
                     WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':pris' => $existant['pris'] ? 0 : 1, ':id' => $existant['id']]);
        } else {
            // Créer la prise pour aujourd'hui (cochée)
            $sql  = "INSERT INTO prises_medicaments (medicament_id, utilisateur_id, date_prise, pris)
                     VALUES (:mid, :uid, CURDATE(), 1)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':mid' => (int)$medicamentId, ':uid' => (int)$utilisateurId]);
        }
    }

    // ----------------------------------------------------------
    // DÉSACTIVER un médicament (UPDATE — on archive, on ne supprime pas)
    // Bonne pratique : ne pas supprimer les données médicales,
    // les archiver pour garder l'historique.
    // ----------------------------------------------------------
    public function desactiver($id, $utilisateurId) {
        $sql  = "UPDATE medicaments SET actif = 0 WHERE id = :id AND utilisateur_id = :uid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => (int)$id, ':uid' => (int)$utilisateurId]);
    }

    // ----------------------------------------------------------
    // Calcule le taux d'adhérence (pourcentage de prises effectuées)
    // sur les 7 derniers jours — utilisé pour le dashboard
    // ----------------------------------------------------------
    public function tauxAdherence($utilisateurId) {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(pris) AS prises_effectuees
                FROM prises_medicaments
                WHERE utilisateur_id = :uid
                  AND date_prise >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$utilisateurId]);
        $result = $stmt->fetch();

        if (!$result || $result['total'] == 0) return 0;
        return round(($result['prises_effectuees'] / $result['total']) * 100);
    }
}
