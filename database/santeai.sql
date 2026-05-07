-- ============================================================
-- SANTEAI - Base de données
-- Dashboard de suivi patient - Thyroïdite de Hashimoto
-- BTS SIO SLAM - Épreuve E6 - 2026
-- Auteure : Abigaëlle SAIOVICI
-- ============================================================

CREATE DATABASE IF NOT EXISTS santeai
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE santeai;

-- ============================================================
-- TABLE : utilisateurs
-- Stocke les informations de chaque patient inscrit sur la plateforme.
-- L'email est UNIQUE pour qu'on ne crée pas deux comptes avec le même email.
-- Le mot_de_passe est toujours stocké hashé (jamais en clair) via password_hash() PHP.
-- L'avatar permet à l'utilisateur de personnaliser son profil avec une illustration.
-- ============================================================
CREATE TABLE IF NOT EXISTS utilisateurs (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe    VARCHAR(255) NOT NULL,          -- Hashé avec password_hash() en PHP
    date_naissance  DATE,
    sexe            ENUM('F', 'M', 'Autre') DEFAULT 'F',
    avatar          VARCHAR(20) DEFAULT 'avatar1',  -- Illustration de personnalisation
    pathologie      VARCHAR(100) DEFAULT 'Thyroïdite de Hashimoto',
    medecin_nom     VARCHAR(150),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE : symptomes
-- Saisie quotidienne des symptômes ressentis par le patient.
-- La UNIQUE KEY empêche deux saisies le même jour (une par jour max).
-- Les niveaux de fatigue et d'humeur sont des échelles de 1 à 5.
-- Les symptômes boolean (0/1) indiquent la présence ou l'absence d'un symptôme.
-- ============================================================
CREATE TABLE IF NOT EXISTS symptomes (
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id          INT NOT NULL,
    date_saisie             DATE NOT NULL,
    niveau_fatigue          TINYINT DEFAULT 1,       -- Échelle : 1 (faible) à 5 (intense)
    niveau_humeur           TINYINT DEFAULT 3,       -- Échelle : 1 (très mauvaise) à 5 (excellente)
    douleurs_articulaires   TINYINT(1) DEFAULT 0,   -- 0 = non, 1 = oui
    brouillard_mental       TINYINT(1) DEFAULT 0,
    intolerances_froid      TINYINT(1) DEFAULT 0,
    chute_cheveux           TINYINT(1) DEFAULT 0,
    temperature             DECIMAL(4,1),            -- Température corporelle en °C
    poids                   DECIMAL(5,2),            -- Poids en kg
    notes                   TEXT,
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    UNIQUE KEY une_saisie_par_jour (utilisateur_id, date_saisie)  -- Contrainte : une seule saisie/jour
) ENGINE=InnoDB;

-- ============================================================
-- TABLE : bilans_biologiques
-- Résultats des prises de sang du patient.
-- Les valeurs normales sont indiquées en commentaire pour chaque marqueur.
-- Ces valeurs servent à l'algorithme de recommandations.
-- ============================================================
CREATE TABLE IF NOT EXISTS bilans_biologiques (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id  INT NOT NULL,
    date_bilan      DATE NOT NULL,
    tsh             DECIMAL(6,3),            -- mUI/L — Normale : 0,4 à 4,0
    t3_libre        DECIMAL(6,3),            -- pmol/L — Normale : 3,1 à 6,8
    t4_libre        DECIMAL(6,3),            -- pmol/L — Normale : 12 à 22
    ferritine       DECIMAL(7,2),            -- µg/L — Normale femme : 15 à 150
    vitamine_d      DECIMAL(6,2),            -- nmol/L — Optimale : > 75
    anticorps_tpo   DECIMAL(8,2),            -- UI/mL — Normale : < 35 (Hashimoto : souvent très élevé)
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE : medicaments
-- Traitements déclarés par le patient (ex : Lévothyroxine 50mcg).
-- Le champ 'actif' permet d'archiver un médicament sans le supprimer.
-- ============================================================
CREATE TABLE IF NOT EXISTS medicaments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id  INT NOT NULL,
    nom             VARCHAR(100) NOT NULL,           -- Ex : "Lévothyroxine"
    dosage          VARCHAR(50),                     -- Ex : "50 mcg"
    moment_prise    ENUM('matin','midi','soir','nuit') DEFAULT 'matin',
    actif           TINYINT(1) DEFAULT 1,            -- 1 = traitement en cours, 0 = arrêté
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE : prises_medicaments
-- Journal quotidien : le patient coche s'il a pris son médicament.
-- UNIQUE KEY : on ne peut pas avoir deux entrées pour le même médicament le même jour.
-- ============================================================
CREATE TABLE IF NOT EXISTS prises_medicaments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    medicament_id   INT NOT NULL,
    utilisateur_id  INT NOT NULL,
    date_prise      DATE NOT NULL,
    pris            TINYINT(1) DEFAULT 0,            -- 0 = non pris, 1 = pris
    FOREIGN KEY (medicament_id) REFERENCES medicaments(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_prise_jour (medicament_id, date_prise)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE : recommandations
-- Conseils générés automatiquement par l'algorithme PHP ou les triggers SQL.
-- Le champ 'lu' permet de savoir si le patient a déjà vu le conseil.
-- Le type 'alerte' est affiché en rouge, 'conseil' en bleu, 'info' en gris.
-- ============================================================
CREATE TABLE IF NOT EXISTS recommandations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id  INT NOT NULL,
    message         TEXT NOT NULL,
    type            ENUM('alerte','conseil','info') DEFAULT 'conseil',
    lu              TINYINT(1) DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- ============================================================
-- TRIGGERS
-- Ce sont des automatisations déclenchées directement par la BDD
-- quand un événement se produit (INSERT, UPDATE, DELETE).
-- Avantage : la règle métier est garantie même si quelqu'un accède
-- à la BDD directement, sans passer par le site.
-- ============================================================

DELIMITER //

-- TRIGGER 1 : Analyse les symptômes à chaque nouvelle saisie
-- Si fatigue >= 4 → alerte immédiate dans la table recommandations
CREATE TRIGGER after_symptome_insert
AFTER INSERT ON symptomes
FOR EACH ROW
BEGIN
    IF NEW.niveau_fatigue >= 4 THEN
        INSERT INTO recommandations (utilisateur_id, message, type)
        VALUES (
            NEW.utilisateur_id,
            'Votre niveau de fatigue est très élevé. Pensez à vous reposer et consultez votre médecin si cela persiste plus de 3 jours.',
            'alerte'
        );
    END IF;

    -- Plusieurs symptômes graves combinés : double alerte
    IF NEW.brouillard_mental = 1 AND NEW.douleurs_articulaires = 1 AND NEW.niveau_fatigue >= 3 THEN
        INSERT INTO recommandations (utilisateur_id, message, type)
        VALUES (
            NEW.utilisateur_id,
            'Plusieurs symptômes importants sont détectés simultanément. Un suivi médical rapproché est recommandé.',
            'alerte'
        );
    END IF;
END//

-- TRIGGER 2 : Analyse le bilan biologique dès qu'il est saisi
-- Vérifie les valeurs de TSH et de vitamine D par rapport aux normes médicales
CREATE TRIGGER after_bilan_insert
AFTER INSERT ON bilans_biologiques
FOR EACH ROW
BEGIN
    -- TSH > 4.0 = hypothyroïdie possible (TSH trop haute)
    IF NEW.tsh IS NOT NULL AND NEW.tsh > 4.0 THEN
        INSERT INTO recommandations (utilisateur_id, message, type)
        VALUES (
            NEW.utilisateur_id,
            CONCAT('Votre TSH (', NEW.tsh, ' mUI/L) est au-dessus de la normale (0,4–4,0 mUI/L). Contactez votre endocrinologue pour ajuster votre traitement.'),
            'alerte'
        );
    END IF;

    -- TSH < 0.4 = hyperthyroïdie possible (dosage trop fort)
    IF NEW.tsh IS NOT NULL AND NEW.tsh < 0.4 THEN
        INSERT INTO recommandations (utilisateur_id, message, type)
        VALUES (
            NEW.utilisateur_id,
            CONCAT('Votre TSH (', NEW.tsh, ' mUI/L) est en dessous de la normale. Votre dosage de Lévothyroxine est peut-être trop élevé.'),
            'alerte'
        );
    END IF;

    -- Vitamine D insuffisante (< 50 nmol/L)
    IF NEW.vitamine_d IS NOT NULL AND NEW.vitamine_d < 50 THEN
        INSERT INTO recommandations (utilisateur_id, message, type)
        VALUES (
            NEW.utilisateur_id,
            CONCAT('Votre vitamine D (', NEW.vitamine_d, ' nmol/L) est insuffisante. Une supplémentation est souvent bénéfique pour les patients Hashimoto.'),
            'conseil'
        );
    END IF;
END//

DELIMITER ;


-- ============================================================
-- VUE : vue_sante_recente
-- Agrège en une seule requête les dernières données disponibles
-- pour chaque patient. Utilisée dans le dashboard.
-- Une VUE en SQL est comme une requête sauvegardée — pratique et lisible.
-- ============================================================
CREATE OR REPLACE VIEW vue_sante_recente AS
SELECT
    u.id AS utilisateur_id,
    u.prenom,
    u.nom,
    s.date_saisie   AS derniere_saisie_symptomes,
    s.niveau_fatigue,
    s.niveau_humeur,
    b.date_bilan    AS dernier_bilan,
    b.tsh
FROM utilisateurs u
LEFT JOIN symptomes s
    ON s.utilisateur_id = u.id
    AND s.date_saisie = (
        SELECT MAX(date_saisie) FROM symptomes WHERE utilisateur_id = u.id
    )
LEFT JOIN bilans_biologiques b
    ON b.utilisateur_id = u.id
    AND b.date_bilan = (
        SELECT MAX(date_bilan) FROM bilans_biologiques WHERE utilisateur_id = u.id
    );


-- ============================================================
-- DONNÉES DE DÉMONSTRATION
-- Compte test pour présenter le projet à l'oral.
-- Email : marie.dupont@exemple.fr | Mot de passe : patient123
-- Le hash ci-dessous correspond à "patient123" (bcrypt via PHP password_hash).
-- IMPORTANT : utilisez setup.php pour générer un hash sur votre machine si nécessaire.
-- ============================================================
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, date_naissance, sexe, avatar, medecin_nom)
VALUES (
    'Dupont',
    'Marie',
    'marie.dupont@exemple.fr',
    '$2y$10$TKh8H1.PfuAi24a4vQ7ReuEamGU6vBc33v2V0b2h5c2AVRsY4e6Ey',
    '1990-03-15',
    'F',
    'avatar1',
    'Dr. Martin'
);

-- Symptômes des 7 derniers jours (données fictives réalistes pour Hashimoto)
INSERT INTO symptomes (utilisateur_id, date_saisie, niveau_fatigue, niveau_humeur, douleurs_articulaires, brouillard_mental, poids, temperature)
VALUES
(1, DATE_SUB(CURDATE(), INTERVAL 6 DAY), 4, 2, 1, 0, 62.5, 36.7),
(1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 3, 3, 0, 1, 62.3, 36.8),
(1, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 5, 2, 1, 1, 62.5, 36.6),
(1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 3, 3, 0, 0, 62.4, 36.9),
(1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 2, 4, 0, 0, 62.2, 36.8),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 2, 4, 0, 0, 62.1, 37.0);

-- Bilans biologiques de démonstration (évolution sur 3 mois, amélioration progressive)
INSERT INTO bilans_biologiques (utilisateur_id, date_bilan, tsh, t3_libre, t4_libre, ferritine, vitamine_d, anticorps_tpo, notes)
VALUES
(1, DATE_SUB(CURDATE(), INTERVAL 90 DAY), 6.2, 3.8, 14.5, 42.0, 48.0, 285.0, 'TSH trop élevée, ajustement du dosage prévu'),
(1, DATE_SUB(CURDATE(), INTERVAL 45 DAY), 4.8, 4.1, 15.8, 55.0, 58.0, 210.0, 'Légère amélioration, poursuite du traitement'),
(1, DATE_SUB(CURDATE(), INTERVAL 7 DAY),  3.2, 4.5, 17.2, 68.0, 72.0, 180.0, 'TSH dans les normes, vitamine D améliorée');

-- Médicaments de démonstration
INSERT INTO medicaments (utilisateur_id, nom, dosage, moment_prise, actif)
VALUES
(1, 'Lévothyroxine', '50 mcg', 'matin', 1),
(1, 'Vitamine D3',   '2000 UI', 'midi', 1),
(1, 'Magnésium',     '300 mg', 'soir', 1);
