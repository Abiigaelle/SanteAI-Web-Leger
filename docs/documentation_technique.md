# Documentation Technique — SantéAI (Client Léger)
**BTS SIO SLAM — Épreuve E6 — 2026**
Auteure : Abigaëlle SAIOVICI | N° candidat : 02542553866

---

## 1. Présentation du projet

SantéAI est une plateforme web de suivi à distance pour patients atteints de maladies auto-immunes (thyroïdite de Hashimoto). Elle permet la saisie quotidienne de données cliniques, la visualisation des bilans biologiques, le suivi médicamenteux et la génération automatique de recommandations.

---

## 2. Architecture technique

### 2.1 Pattern MVC natif

```
Requête HTTP
     │
     ▼
index.php  ←── Routeur unique (Front Controller)
     │
     ├──► Controller   ←── lit $_GET, $_POST, $_SESSION
     │         │             appelle le Modèle
     │         ▼
     │       Model     ←── requêtes SQL via PDO
     │         │             retourne des données
     │         ▼
     │       View      ←── génère le HTML (PHP + Bootstrap)
     │
     ▼
Réponse HTML → Navigateur
```

| Couche | Rôle | Dossier |
|---|---|---|
| **Modèle** | Toutes les requêtes SQL (CRUD) | `models/` |
| **Vue** | Affichage HTML/CSS uniquement | `views/` |
| **Contrôleur** | Logique métier, lien Modèle↔Vue | `controllers/` |

### 2.2 Structure des fichiers

```
SantéAI Léger/
├── index.php                   ← Routeur (Front Controller)
├── lancer.bat                  ← Script de démarrage (serveur PHP intégré)
├── setup.php                   ← Init compte démo (supprimer après usage)
├── config/
│   └── database.php            ← Connexion PDO MySQL
├── models/
│   ├── User.php                ← CRUD table utilisateurs
│   ├── Symptome.php            ← CRUD table symptomes
│   ├── Bilan.php               ← CRUD table bilans_biologiques
│   ├── Medicament.php          ← CRUD tables medicaments + prises_medicaments
│   └── Recommandation.php      ← Algorithme + CRUD table recommandations
├── controllers/
│   ├── AuthController.php      ← Connexion / Inscription / Déconnexion
│   ├── DashboardController.php ← Tableau de bord
│   ├── SymptomeController.php  ← Saisie et historique des symptômes
│   ├── BilanController.php     ← Saisie et historique des bilans
│   ├── PatientController.php   ← Gestion du profil
│   └── MedicamentController.php← Gestion des médicaments
├── views/
│   ├── layout/ (header, nav, footer)
│   ├── auth/ (login, register)
│   ├── dashboard/ (index)
│   ├── symptomes/ (ajouter, historique)
│   ├── bilans/ (ajouter, historique)
│   ├── patient/ (profil)
│   └── medicaments/ (liste)
├── assets/
│   ├── css/style.css           ← Charte graphique (variables CSS + Bootstrap)
│   └── js/main.js              ← Graphiques Chart.js
├── database/
│   └── santeai.sql             ← Schéma BDD + triggers + données démo
└── docs/                       ← Documentation projet
```

### 2.3 Système de routage

Le routeur lit deux paramètres GET :
- `?page=X` → charge `controllers/[X]Controller.php`
- `?action=Y` → appelle la méthode `Y()` du contrôleur

**Exemples de routes :**

| URL | Contrôleur | Méthode |
|---|---|---|
| `index.php` | AuthController | login() |
| `?page=dashboard` | DashboardController | index() |
| `?page=symptomes&action=ajouter` | SymptomeController | ajouter() |
| `?page=symptomes&action=historique` | SymptomeController | historique() |
| `?page=bilans&action=ajouter` | BilanController | ajouter() |
| `?page=bilans&action=historique` | BilanController | historique() |
| `?page=medicaments&action=liste` | MedicamentController | liste() |
| `?page=medicaments&action=prise&id=X` | MedicamentController | prise() |
| `?page=patient&action=profil` | PatientController | profil() |
| `?page=auth&action=logout` | AuthController | logout() |

**Sécurisation du routage :** les paramètres `page` et `action` sont filtrés par regex (`[^a-zA-Z]`) pour empêcher les attaques de type Path Traversal.

---

## 3. Base de données

### 3.1 Modèle relationnel

```
utilisateurs (PK: id, email UNIQUE, mot_de_passe, avatar, pathologie, ...)
     │
     ├──< symptomes (FK: utilisateur_id, UNIQUE: utilisateur_id+date_saisie)
     │
     ├──< bilans_biologiques (FK: utilisateur_id)
     │
     ├──< medicaments (FK: utilisateur_id)
     │         │
     │         └──< prises_medicaments (FK: medicament_id, utilisateur_id, UNIQUE: medicament_id+date_prise)
     │
     └──< recommandations (FK: utilisateur_id)
```

### 3.2 Dictionnaire de données

#### Table `utilisateurs`
| Champ | Type | Contrainte | Description |
|---|---|---|---|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| nom | VARCHAR(100) | NOT NULL | Nom de famille |
| prenom | VARCHAR(100) | NOT NULL | Prénom |
| email | VARCHAR(150) | NOT NULL, UNIQUE | Email (identifiant de connexion) |
| mot_de_passe | VARCHAR(255) | NOT NULL | Hash bcrypt |
| date_naissance | DATE | NULL | Date de naissance |
| sexe | ENUM | DEFAULT 'F' | F / M / Autre |
| avatar | VARCHAR(20) | DEFAULT 'avatar1' | Illustration choisie (avatar1–5) |
| pathologie | VARCHAR(100) | DEFAULT 'Thyroïdite...' | Maladie suivie |
| medecin_nom | VARCHAR(150) | NULL | Nom du médecin référent |
| created_at | TIMESTAMP | DEFAULT NOW() | Date de création |

#### Table `symptomes`
| Champ | Type | Contrainte | Description |
|---|---|---|---|
| id | INT | PK | Identifiant |
| utilisateur_id | INT | FK → utilisateurs.id | Patient concerné |
| date_saisie | DATE | NOT NULL | Date de la saisie |
| niveau_fatigue | TINYINT | 1–5 | Échelle de fatigue |
| niveau_humeur | TINYINT | 1–5 | Échelle d'humeur |
| douleurs_articulaires | TINYINT(1) | 0/1 | Présence ou non |
| brouillard_mental | TINYINT(1) | 0/1 | Brain fog |
| intolerances_froid | TINYINT(1) | 0/1 | Intolérance au froid |
| chute_cheveux | TINYINT(1) | 0/1 | Chute de cheveux |
| temperature | DECIMAL(4,1) | NULL | °C |
| poids | DECIMAL(5,2) | NULL | kg |
| notes | TEXT | NULL | Notes libres |
| UNIQUE | — | (utilisateur_id, date_saisie) | 1 saisie max par jour |

#### Table `bilans_biologiques`
| Champ | Type | Unité | Valeurs normales |
|---|---|---|---|
| tsh | DECIMAL(6,3) | mUI/L | 0,4 – 4,0 |
| t3_libre | DECIMAL(6,3) | pmol/L | 3,1 – 6,8 |
| t4_libre | DECIMAL(6,3) | pmol/L | 12 – 22 |
| ferritine | DECIMAL(7,2) | µg/L | Femme : 15–150 |
| vitamine_d | DECIMAL(6,2) | nmol/L | Optimale : > 75 |
| anticorps_tpo | DECIMAL(8,2) | UI/mL | < 35 |

#### Table `medicaments`
| Champ | Type | Description |
|---|---|---|
| nom | VARCHAR(100) | Nom du médicament |
| dosage | VARCHAR(50) | Ex : "50 mcg" |
| moment_prise | ENUM | matin / midi / soir / nuit |
| actif | TINYINT(1) | 1 = en cours, 0 = archivé |

#### Table `prises_medicaments`
| Champ | Type | Description |
|---|---|---|
| medicament_id | INT FK | Médicament concerné |
| date_prise | DATE | Date de la prise |
| pris | TINYINT(1) | 0 = non pris, 1 = pris |
| UNIQUE | — | (medicament_id, date_prise) |

#### Table `recommandations`
| Champ | Type | Description |
|---|---|---|
| message | TEXT | Contenu du conseil |
| type | ENUM | alerte / conseil / info |
| lu | TINYINT(1) | 0 = non lu, 1 = lu |

### 3.3 Triggers SQL

**Trigger `after_symptome_insert`** — déclenché après chaque INSERT dans `symptomes` :
- Si `niveau_fatigue >= 4` → INSERT d'une alerte dans `recommandations`
- Si `brouillard_mental = 1 AND douleurs_articulaires = 1 AND fatigue >= 3` → INSERT d'une alerte combinée

**Trigger `after_bilan_insert`** — déclenché après chaque INSERT dans `bilans_biologiques` :
- Si `tsh > 4.0` → alerte TSH élevée
- Si `tsh < 0.4` → alerte TSH basse (surdosage possible)
- Si `vitamine_d < 50` → conseil supplémentation

### 3.4 Vue SQL

**Vue `vue_sante_recente`** — agrège les dernières données de chaque patient :
```sql
SELECT u.id, u.prenom, s.date_saisie, s.niveau_fatigue, b.date_bilan, b.tsh
FROM utilisateurs u
LEFT JOIN symptomes s ON s.id = (SELECT id FROM symptomes WHERE utilisateur_id = u.id ORDER BY date_saisie DESC LIMIT 1)
LEFT JOIN bilans_biologiques b ON b.id = (SELECT id FROM bilans_biologiques WHERE utilisateur_id = u.id ORDER BY date_bilan DESC LIMIT 1)
```

---

## 4. Sécurité

| Menace | Contre-mesure |
|---|---|
| **Injection SQL** | Requêtes préparées PDO (`prepare()` + `execute()`) sur toutes les requêtes |
| **XSS** | `htmlspecialchars()` sur toutes les données affichées |
| **Mots de passe** | `password_hash()` bcrypt au stockage, `password_verify()` à la connexion |
| **Accès non autorisé** | Vérification de `$_SESSION['utilisateur_id']` dans index.php avant chaque page |
| **Path Traversal** | Nettoyage des paramètres URL par regex `[^a-zA-Z]` |
| **CSRF (partiel)** | Validation des méthodes HTTP (POST uniquement pour les modifications) |
| **Données inter-utilisateurs** | Double condition `id = :id AND utilisateur_id = :uid` dans tous les DELETE/UPDATE |

---

## 5. Algorithme de recommandations

Implémenté dans `models/Recommandation.php`, méthode `genererRecommandations()`.

**Type :** Système à base de règles (if/else) — pas de machine learning.

**Règles :**

| # | Condition | Type | Message |
|---|---|---|---|
| 1 | Fatigue ≥ 4 pendant 3 jours consécutifs | alerte | "Fatigue persistante, consultez votre médecin" |
| 2 | ≥ 3 oublis de médicaments sur 7 jours | alerte | "Régularité du traitement essentielle" |
| 3 | Aucun bilan depuis 2 mois | conseil | "Pensez à faire un bilan sanguin" |
| 4 | Humeur moyenne < 2,5 sur 7 jours | conseil | "Accompagnement psychologique recommandé" |
| T1 | TSH > 4,0 (trigger SQL) | alerte | "TSH élevée, consultez votre endocrinologue" |
| T2 | TSH < 0,4 (trigger SQL) | alerte | "TSH basse, dosage peut-être trop élevé" |
| T3 | Vitamine D < 50 (trigger SQL) | conseil | "Supplémentation recommandée" |

**Anti-doublon :** la méthode `ajouterSiAbsent()` vérifie qu'un même message n'a pas déjà été généré dans la journée.

---

## 6. Stack technique

| Technologie | Version | Rôle |
|---|---|---|
| PHP | ≥ 7.4 | Langage serveur, MVC, sessions |
| MySQL | ≥ 5.7 | Base de données relationnelle |
| PDO | — | Interface PHP → MySQL (requêtes préparées) |
| HTML5 / CSS3 | — | Structure et style des vues |
| Bootstrap | 5.3 | Framework CSS responsive (CDN) |
| Bootstrap Icons | 1.11 | Bibliothèque d'icônes (CDN) |
| Chart.js | 4.4 | Graphiques en courbes (CDN) |
| JavaScript | ES6+ | Interactivité, initialisation des graphiques |
| XAMPP / WAMP | — | Serveur de développement local |
| Git / GitHub | — | Versionnage du code |
| VS Code | — | IDE de développement |
| Figma / Canva | — | Conception des maquettes et charte graphique |
| Trello | — | Gestion des tâches projet |

---

## 7. Installation et lancement

### Prérequis
- XAMPP (ou WAMP) installé avec MySQL
- PHP ≥ 7.4 (inclus dans XAMPP)

### Étapes

```
1. Démarrer MySQL depuis le panneau de contrôle XAMPP

2. Importer la base de données :
   → Ouvrir phpMyAdmin (http://localhost/phpmyadmin)
   → Créer une base "santeai"
   → Importer le fichier : database/santeai.sql

3. Lancer le serveur :
   → Double-cliquer sur : lancer.bat
   → Le site s'ouvre automatiquement sur http://localhost:8080

4. Initialiser le compte démo :
   → Aller sur http://localhost:8080/setup.php
   → Supprimer setup.php après

5. Se connecter :
   → Email    : marie.dupont@exemple.fr
   → Mot de passe : patient123
```

---

## 8. Points d'extension prévus

| Fonctionnalité | Technologie prévue | Complexité estimée |
|---|---|---|
| Export PDF du journal de bord | PHP FPDF / TCPDF | 1–2 jours |
| Partage avec le médecin (email) | PHP Mailer | 1 jour |
| Support d'autres pathologies | Ajout de tables de référence + UI | 3–5 jours |
| Notifications et rappels | Cron job serveur + PHPMailer | 2–3 jours |
| Filtrage avancé de l'historique | Formulaire de recherche + requêtes WHERE | 1 jour |
