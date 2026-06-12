# SantéAI — Dashboard de suivi patient

> Plateforme web de suivi à distance pour patients atteints de la **thyroïdite de Hashimoto**.  
> BTS SIO SLAM — Épreuve E6 — 2026 — Abigaëlle SAIOVICI (N° 02542553866)

---

## Liens du projet

- **GitHub** : https://github.com/Abiigaelle/SanteAI-Web-Leger  
- **Google Drive** : https://drive.google.com/drive/folders/1ok_Sly6azLQ7g8ZQ6cJf_ArkrIeuP_J3?usp=sharing

---

## Stack technique

| Technologie | Rôle |
|---|---|
| PHP 8+ | Langage serveur, architecture MVC |
| MySQL | Base de données relationnelle |
| Bootstrap 5 | Interface responsive |
| Chart.js | Graphiques d'évolution |
| XAMPP | Serveur local (Apache + MySQL) |

---

## Installation (première fois)

### Prérequis
- [XAMPP](https://www.apachefriends.org) installé (Apache + MySQL inclus)
- Un navigateur web

---

### Étape 1 — Cloner le projet

```bash
git clone https://github.com/Abiigaelle/SanteAI-Web-Leger.git
```

Ou télécharger le ZIP depuis GitHub → **Code → Download ZIP** → extraire.

---

### Étape 2 — Lancer XAMPP

1. Ouvrir **XAMPP Control Panel**
2. Cliquer **Start** sur **Apache**
3. Cliquer **Start** sur **MySQL**

Les deux doivent être verts avant de continuer.

---

### Étape 3 — Créer le lien vers Apache

**Clic droit sur `installer.bat` → "Exécuter en tant qu'administrateur"**

Ce script crée automatiquement un lien entre le dossier du projet et XAMPP.  
Il ne modifie aucun fichier, c'est juste un raccourci.

> Si ça échoue (lien déjà existant), supprimer `C:\xampp\htdocs\santeai` et réessayer.

---

### Étape 4 — Importer la base de données

1. Aller sur **http://localhost/phpmyadmin**
2. Cliquer sur **Nouvelle base de données** → nommer `santeai` → **Créer**
3. Cliquer sur la base `santeai` dans le menu gauche
4. Onglet **Importer** → **Choisir un fichier** → sélectionner `database/santeai.sql`
5. Cliquer **Importer** en bas de page

---

### Étape 5 — Initialiser le compte de démonstration

> Le script `setup.php` n'est pas versionné sur GitHub (il contient la logique de hash) : il est fourni sur le Google Drive du projet. Le placer à la racine avant cette étape.

Aller sur : **http://localhost/santeai/setup.php**

Un message "Setup réussi" doit s'afficher.  
**Supprimer `setup.php` après** (ou le laisser — il est dans le `.gitignore`).

---

### Étape 6 — Accéder au site

**http://localhost/santeai/**

Compte de démonstration :
```
Email    : marie.dupont@exemple.fr
Password : patient123
```

---

## Lancement au quotidien

Une fois l'installation faite, une seule commande lance tout (démarre Apache + MySQL et ouvre le navigateur) :

```bash
npm run dev
```

> `CTRL+C` dans le terminal arrête proprement Apache et MySQL.

**Alternative manuelle** (sans Node) : ouvrir **XAMPP Control Panel** → Start **Apache** + **MySQL**, puis double-cliquer sur **`lancer.bat`** ou aller sur **http://localhost/santeai/**.

---

## Structure du projet

```
SantéAI Léger/
├── index.php              ← Routeur unique (Front Controller MVC)
├── lancer.bat             ← Raccourci navigateur
├── installer.bat          ← Crée le lien XAMPP (1 seule fois)
├── config/
│   └── database.php       ← Connexion PDO MySQL
├── models/                ← Accès aux données (CRUD SQL)
│   ├── User.php
│   ├── Symptome.php
│   ├── Bilan.php
│   ├── Medicament.php
│   └── Recommandation.php ← Algorithme de recommandations
├── controllers/           ← Logique métier
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── SymptomeController.php
│   ├── BilanController.php
│   ├── PatientController.php
│   └── MedicamentController.php
├── views/                 ← Interface HTML
│   ├── layout/            ← Header, nav, footer communs
│   ├── auth/              ← Connexion, inscription
│   ├── dashboard/         ← Tableau de bord
│   ├── symptomes/         ← Saisie et historique
│   ├── bilans/            ← Saisie et historique
│   ├── patient/           ← Profil
│   └── medicaments/       ← Gestion des traitements
├── assets/
│   ├── css/style.css      ← Charte graphique
│   └── js/main.js         ← Graphiques Chart.js
└── database/
    └── santeai.sql        ← Schéma + triggers + données démo
```

> La documentation (`docs/`) et le script `setup.php` ne sont pas versionnés sur GitHub : ils sont fournis sur le Google Drive du projet.

---

## Fonctionnalités

| Fonctionnalité | Statut |
|---|---|
| Authentification (inscription / connexion) | ✅ Fait |
| Tableau de bord avec graphiques (Chart.js) | ✅ Fait |
| Profil patient + choix d'illustration | ✅ Fait |
| Saisie quotidienne des symptômes | ✅ Fait |
| Historique des symptômes | ✅ Fait |
| Bilans biologiques (TSH, T3, T4, etc.) | ✅ Fait |
| Suivi médicamenteux quotidien | ✅ Fait |
| Algorithme de recommandations automatiques | ✅ Fait |
| Triggers SQL (alertes BDD automatiques) | ✅ Fait |
| Export PDF du journal de bord | ⏳ Prévu (v2) |
| Partage avec le médecin | ⏳ Prévu (v2) |
| Autres pathologies auto-immunes | ⏳ Prévu (v2) |

---

## Problèmes fréquents

**Le site se charge mais les liens déconnectent**  
→ Vider les cookies du navigateur sur `localhost` et se reconnecter.  
→ Vérifier que la BDD est bien importée (toutes les tables présentes dans phpMyAdmin).

**"Erreur de connexion à la base de données"**  
→ Vérifier que MySQL est bien démarré dans XAMPP (bouton vert).  
→ Vérifier que la base `santeai` existe dans phpMyAdmin.

**Page blanche après connexion**  
→ Aller sur `http://localhost/santeai/setup.php` pour créer le hash du mot de passe.

**`installer.bat` échoue**  
→ Le relancer en **clic droit → Exécuter en tant qu'administrateur**.  
→ Si le lien existe déjà : supprimer `C:\xampp\htdocs\santeai` et réessayer.

---

## Documentation

La documentation complète (technique, utilisateur, diagramme des cas d'utilisation, registre des incidents) est disponible sur le **Google Drive** du projet :
https://drive.google.com/drive/folders/1ok_Sly6azLQ7g8ZQ6cJf_ArkrIeuP_J3?usp=sharing

---

*SantéAI — Les informations de cette plateforme ne remplacent pas un avis médical professionnel.*
