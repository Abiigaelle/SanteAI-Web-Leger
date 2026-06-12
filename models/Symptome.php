<?php
// ============================================================
// models/Symptome.php
// Modèle Symptôme — Toutes les requêtes sur la table 'symptomes'
// ============================================================

class Symptome {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ----------------------------------------------------------
    // CRÉER ou METTRE À JOUR la saisie du jour (INSERT ou UPDATE)
    // Logique : si une saisie existe déjà pour aujourd'hui, on la remplace.
    // ----------------------------------------------------------
    public function sauvegarder($utilisateurId, array $data) {
        $existant = $this->saisieAujourdhui($utilisateurId);
        $date = $data['date_saisie'] ?? date('Y-m-d');

        // Une case cochée est présente dans $_POST, sinon le champ est absent
        $douleurs   = isset($data['douleurs_articulaires']) ? 1 : 0;
        $brouillard = isset($data['brouillard_mental']) ? 1 : 0;
        $froid      = isset($data['intolerances_froid']) ? 1 : 0;
        $cheveux    = isset($data['chute_cheveux']) ? 1 : 0;

        if ($existant) {
            $sql = "UPDATE symptomes
                    SET niveau_fatigue = :fatigue, niveau_humeur = :humeur,
                        douleurs_articulaires = :douleurs, brouillard_mental = :brouillard,
                        intolerances_froid = :froid, chute_cheveux = :cheveux,
                        temperature = :temp, poids = :poids, notes = :notes
                    WHERE utilisateur_id = :uid AND date_saisie = :date";
        } else {
            $sql = "INSERT INTO symptomes
                        (utilisateur_id, date_saisie, niveau_fatigue, niveau_humeur,
                         douleurs_articulaires, brouillard_mental, intolerances_froid,
                         chute_cheveux, temperature, poids, notes)
                    VALUES
                        (:uid, :date, :fatigue, :humeur, :douleurs, :brouillard,
                         :froid, :cheveux, :temp, :poids, :notes)";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':uid'        => (int)$utilisateurId,
            ':date'       => $date,
            ':fatigue'    => (int)$data['niveau_fatigue'],
            ':humeur'     => (int)$data['niveau_humeur'],
            ':douleurs'   => $douleurs,
            ':brouillard' => $brouillard,
            ':froid'      => $froid,
            ':cheveux'    => $cheveux,
            ':temp'       => !empty($data['temperature']) ? (float)$data['temperature'] : null,
            ':poids'      => !empty($data['poids']) ? (float)$data['poids'] : null,
            ':notes'      => htmlspecialchars($data['notes'] ?? ''),
        ]);
    }

    // ----------------------------------------------------------
    // LIRE la saisie du jour (READ — pour pré-remplir le formulaire)
    // Retourne false si aucune saisie aujourd'hui
    // ----------------------------------------------------------
    public function saisieAujourdhui($utilisateurId) {
        $sql  = "SELECT * FROM symptomes
                 WHERE utilisateur_id = :uid AND date_saisie = CURDATE()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$utilisateurId]);
        return $stmt->fetch();
    }

    // ----------------------------------------------------------
    // LIRE l'historique des symptômes (READ — page historique)
    // bindValue() avec PARAM_INT est obligatoire pour LIMIT avec PDO
    // ----------------------------------------------------------
    public function historique($utilisateurId, $limite = 30) {
        $sql  = "SELECT * FROM symptomes
                 WHERE utilisateur_id = :uid
                 ORDER BY date_saisie DESC
                 LIMIT :limite";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':uid', (int)$utilisateurId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------
    // LIRE les données pour les graphiques Chart.js
    // 30 derniers jours, ordre chronologique (plus ancien → plus récent)
    // ----------------------------------------------------------
    public function donneesGraphique($utilisateurId) {
        $sql  = "SELECT date_saisie, niveau_fatigue, niveau_humeur
                 FROM symptomes
                 WHERE utilisateur_id = :uid
                 ORDER BY date_saisie ASC
                 LIMIT 30";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$utilisateurId]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------
    // SUPPRIMER une saisie (DELETE du CRUD)
    // La double condition (id ET utilisateur_id) empêche qu'un
    // utilisateur supprime les données d'un autre.
    // ----------------------------------------------------------
    public function supprimer($id, $utilisateurId) {
        $sql  = "DELETE FROM symptomes WHERE id = :id AND utilisateur_id = :uid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => (int)$id, ':uid' => (int)$utilisateurId]);
    }
}
