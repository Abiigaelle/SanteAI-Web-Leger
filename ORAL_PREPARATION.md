# SantéAI — Préparation à l'épreuve E6 BTS SIO SLAM 2026

## Contexte du projet
Plateforme web de suivi à distance pour patients atteints de la thyroïdite de Hashimoto.  
Technologies : PHP, MySQL, HTML/CSS, JavaScript, Bootstrap 5, Chart.js.  
Architecture : MVC natif, CRUD complet, algorithme de recommandations.

---

## Architecture du projet (à connaître parfaitement)

```
index.php            ← Routeur unique (Front Controller)
config/
  database.php       ← Connexion PDO à MySQL
models/
  User.php           ← Requêtes sur la table 'utilisateurs'
  Symptome.php       ← Requêtes sur 'symptomes'
  Bilan.php          ← Requêtes sur 'bilans_biologiques'
  Medicament.php     ← Requêtes sur 'medicaments' + 'prises_medicaments'
  Recommandation.php ← Algorithme + requêtes sur 'recommandations'
controllers/
  AuthController.php      ← Connexion, inscription, déconnexion
  DashboardController.php ← Page principale
  SymptomeController.php  ← CRUD symptômes
  BilanController.php     ← CRUD bilans biologiques
  PatientController.php   ← Mise à jour du profil
  MedicamentController.php ← CRUD médicaments + suivi des prises
views/
  layout/ header.php, nav.php, footer.php
  auth/   login.php, register.php
  dashboard/index.php
  symptomes/ ajouter.php, historique.php
  bilans/    ajouter.php, historique.php
  patient/   profil.php
  medicaments/liste.php
assets/css/style.css   ← Charte graphique (variables CSS + Bootstrap)
assets/js/main.js      ← Graphiques Chart.js
database/santeai.sql   ← Schéma BDD + triggers + données démo
```

---

## Questions fréquentes du jury — Réponses préparées

### 🏗️ Architecture

**Q : Pourquoi avez-vous choisi l'architecture MVC ?**  
R : Le MVC sépare les responsabilités en trois couches : le Modèle gère uniquement les données (requêtes SQL), la Vue affiche uniquement l'interface HTML, et le Contrôleur fait le lien entre les deux. Ça rend le code plus lisible, plus facile à maintenir : si je veux changer l'affichage, je touche uniquement la vue, sans risquer de casser la logique métier.

**Q : C'est quoi un Front Controller (index.php) ?**  
R : Toutes les requêtes passent par un seul fichier, index.php. Il lit le paramètre `?page=` dans l'URL, charge le bon contrôleur et appelle la bonne méthode. L'avantage : on applique les vérifications de sécurité (session, authentification) à un seul endroit pour toute l'application.

**Q : Comment fonctionne le routage ?**  
R : `index.php?page=symptomes&action=ajouter` → charge SymptomeController.php, instancie la classe SymptomeController, appelle la méthode `ajouter()`. Le nom de la classe est construit dynamiquement : `ucfirst($page) . 'Controller'`.

---

### 🗄️ Base de données

**Q : Pourquoi utilisez-vous PDO plutôt que mysqli ?**  
R : PDO est compatible avec plusieurs SGBD (MySQL, PostgreSQL, SQLite). Surtout, les requêtes préparées avec `prepare()` et `execute()` protègent automatiquement contre les injections SQL : les valeurs utilisateur ne sont jamais interprétées comme du SQL.

**Q : Qu'est-ce qu'une injection SQL et comment vous protégez-vous ?**  
R : Une injection SQL, c'est quand un utilisateur malveillant tape du code SQL dans un champ de formulaire pour manipuler la base. Par exemple, mettre `' OR 1=1 --` dans le champ email. Avec PDO et `prepare()`/`execute()`, les paramètres sont envoyés séparément de la requête, donc le serveur MySQL ne les interprète jamais comme du SQL.

**Q : À quoi servent les triggers dans votre base de données ?**  
R : Les triggers sont des automatismes côté MySQL : ils se déclenchent automatiquement quand un événement se produit (INSERT, UPDATE, DELETE). J'ai deux triggers : `after_symptome_insert` génère une alerte si le niveau de fatigue est ≥ 4, et `after_bilan_insert` génère une alerte si la TSH est hors norme. L'avantage des triggers : la règle est garantie même si quelqu'un accède à la BDD directement sans passer par le site.

**Q : Quelle est la différence entre un trigger et l'algorithme PHP ?**  
R : Les triggers s'exécutent immédiatement lors de l'INSERT (réactivité maximale). L'algorithme PHP dans Recommandation.php fait une analyse périodique à chaque ouverture du dashboard, sur plusieurs jours de données (ex : fatigue élevée 3 jours consécutifs). Les deux sont complémentaires.

**Q : Pourquoi une UNIQUE KEY sur (utilisateur_id, date_saisie) dans la table symptomes ?**  
R : Pour garantir qu'un patient ne peut pas avoir deux saisies le même jour. Cette contrainte est dans la base elle-même, pas seulement dans le code PHP — c'est une sécurité supplémentaire.

**Q : Qu'est-ce qu'une VIEW SQL ?**  
R : Une VIEW est une requête SQL sauvegardée sous un nom. Elle se comporte comme une table virtuelle. J'ai créé `vue_sante_recente` qui agrège en une seule requête les dernières données de chaque patient. L'avantage : on simplifie les requêtes complexes et on peut les réutiliser.

---

### 🔐 Sécurité

**Q : Comment stockez-vous les mots de passe ?**  
R : Avec `password_hash()` en PHP, qui utilise l'algorithme bcrypt. C'est un hash irréversible : personne, même l'administrateur de la BDD, ne peut retrouver le mot de passe original. La vérification se fait avec `password_verify()` qui compare le mot de passe saisi avec le hash stocké.

**Q : Qu'est-ce que XSS et comment vous protégez-vous ?**  
R : XSS (Cross-Site Scripting) : un utilisateur injecte du JavaScript malveillant dans un champ, qui s'exécuterait ensuite chez d'autres utilisateurs. Je me protège avec `htmlspecialchars()` sur toutes les données affichées : ça transforme `<script>` en `&lt;script&gt;`, donc le code est affiché comme du texte, pas exécuté.

**Q : Pourquoi vérifiez-vous `$_SERVER['REQUEST_METHOD'] === 'POST'` ?**  
R : Pour distinguer un GET (affichage du formulaire) d'un POST (soumission). Sans cette vérification, le code de traitement s'exécuterait même quand l'utilisateur arrive sur la page pour la première fois, ce qui pourrait créer des données vides en BDD.

**Q : Qu'est-ce qu'une session PHP ?**  
R : Une session crée un identifiant unique (PHPSESSID) stocké dans un cookie dans le navigateur. Le serveur associe cet identifiant à un fichier contenant les données de session (nom, ID utilisateur...). À chaque requête, le serveur lit le cookie, retrouve le fichier de session, et sait qui est l'utilisateur connecté. `session_start()` est obligatoire au début pour activer ce mécanisme.

---

### 💻 Code PHP/JavaScript

**Q : Pourquoi `json_encode()` dans le contrôleur du dashboard ?**  
R : Chart.js est une bibliothèque JavaScript, elle a besoin de tableaux JavaScript pour dessiner les graphiques. `json_encode()` convertit un tableau PHP en chaîne JSON (format universel entre PHP et JS). Ensuite dans la vue, on initialise une variable JS : `var donnees = <?= $donneesJSON ?>;` — c'est le pont entre PHP et JavaScript.

**Q : Comment fonctionne Bootstrap ?**  
R : Bootstrap est un framework CSS/JavaScript qui fournit des classes CSS prêtes à l'emploi pour construire des interfaces responsives. Par exemple, `col-md-6` divise automatiquement la page en deux colonnes sur tablette/desktop. `btn btn-primary` stylise un bouton aux couleurs définies. Ça évite d'écrire du CSS de mise en page from scratch.

**Q : Pourquoi `bindValue()` avec `PDO::PARAM_INT` pour LIMIT ?**  
R : PDO traite tous les paramètres comme des chaînes par défaut. Mais MySQL n'accepte pas LIMIT '30' (avec guillemets), seulement LIMIT 30 (entier). `bindValue(':limite', 30, PDO::PARAM_INT)` force le typage entier pour ce paramètre spécifiquement.

**Q : Qu'est-ce que le pattern Post-Redirect-Get (PRG) ?**  
R : Après un POST (soumission de formulaire), on redirige vers une page GET. Sans ça, si l'utilisateur rafraîchit la page, le navigateur ré-envoie le POST et on recrée des données en double. Avec `header('Location: ...')` + `exit`, le navigateur fait une nouvelle requête GET, et le rechargement de page est inoffensif.

---

### 📊 Fonctionnalités

**Q : Comment fonctionne l'algorithme de recommandations ?**  
R : C'est un algorithme basé sur des règles simples (if/else) sur les données récentes. Règle 1 : si la fatigue est ≥ 4 pendant 3 jours consécutifs → alerte. Règle 2 : si les médicaments n'ont pas été pris 3 fois cette semaine → alerte. Règle 3 : si aucun bilan depuis 2 mois → rappel. Les triggers SQL complètent avec des alertes immédiates à l'insertion (TSH hors norme, etc.).

**Q : Comment évitez-vous les doublons dans les recommandations ?**  
R : La méthode `ajouterSiAbsent()` vérifie d'abord si le même message a déjà été créé aujourd'hui pour cet utilisateur. Si oui, on n'insère pas de nouveau. Si non, on insère. Ça évite d'avoir 10 fois le même conseil.

**Q : À quoi sert COALESCE dans la requête des médicaments du jour ?**  
R : `COALESCE(p.pris, 0)` retourne la valeur de `p.pris` si elle n'est pas NULL, sinon 0. Avec un LEFT JOIN, si aucune prise n'a encore été enregistrée pour aujourd'hui, `p.pris` est NULL — COALESCE le transforme en 0 (= non pris) pour l'affichage.

**Q : Pourquoi utilisez-vous `htmlspecialchars()` dans les vues et pas dans les modèles ?**  
R : La protection XSS doit se faire à l'affichage, pas au stockage. Si on échappe à l'écriture, on stocke `&amp;` en BDD au lieu de `&`, et ça pose des problèmes si d'autres systèmes accèdent aux données. On stocke les données brutes, on échappe uniquement à l'affichage.

---

## Fonctionnalités intentionnellement incomplètes (à ajouter à l'oral)

### 1. Export PDF du journal de bord
**Où c'est visible :** Bouton désactivé dans le dashboard et sur la page profil.  
**Ce que je dirai :** "L'export PDF est prévu mais pas encore implémenté. Je l'ajouterais avec la bibliothèque PHP FPDF ou TCPDF. La démarche serait : sélectionner les données des 30 derniers jours, créer un document PDF avec les tableaux et graphiques, et proposer le téléchargement avec `header('Content-Type: application/pdf')`."

### 2. Partage avec le médecin
**Où c'est visible :** Section "bientôt disponible" sur la page profil.  
**Ce que je dirai :** "Je l'ajouterais avec la bibliothèque PHPMailer : génération d'un rapport PDF, puis envoi automatique par email avec un lien sécurisé (token unique). Il faudrait ajouter un champ `email_medecin` dans la table `utilisateurs`."

### 3. Support d'autres pathologies
**Ce que je dirai :** "La base de données est prête : le champ `pathologie` dans `utilisateurs` peut accueillir n'importe quelle pathologie. L'extension consisterait à créer des tables spécifiques à chaque maladie (ex : `marqueurs_lupus`) et d'adapter l'algorithme de recommandations selon la pathologie choisie."

### 4. Système de notifications/rappels
**Ce que je dirai :** "On pourrait ajouter un système de cron job (tâche planifiée serveur) qui envoie un email de rappel si le patient n'a pas fait sa saisie quotidienne avant 21h. En PHP, ça passerait par PHPMailer + un script PHP appelé par le cron."

---

## Modifications rapides à réaliser en 1 heure (si le jury vous le demande)

| Demande du jury | Fichier(s) à modifier | Durée estimée |
|---|---|---|
| Ajouter un champ "douleur de tête" | `santeai.sql` (ALTER TABLE), `models/Symptome.php`, `views/symptomes/ajouter.php`, `views/symptomes/historique.php` | 15 min |
| Ajouter un graphique de poids | `models/Symptome.php` (nouvelle méthode), `views/dashboard/index.php` (canvas), `assets/js/main.js` (Chart.js) | 20 min |
| Ajouter la modification d'un bilan | `models/Bilan.php` (méthode modifier()), `controllers/BilanController.php` (action modifier()), nouvelle vue | 25 min |
| Ajouter une recherche dans l'historique | `models/Symptome.php` (requête avec WHERE date BETWEEN), `views/symptomes/historique.php` (formulaire de filtre) | 15 min |
| Changer la palette de couleurs | `assets/css/style.css` (modifier les variables :root) | 5 min |

---

## Points techniques à maîtriser absolument

1. **PDO + requêtes préparées** : `$stmt = $pdo->prepare($sql); $stmt->execute([':param' => $valeur]);`
2. **Sessions PHP** : `session_start()`, `$_SESSION['clé']`, `session_destroy()`
3. **Hachage de mots de passe** : `password_hash()` et `password_verify()`
4. **Relation entre tables** : FOREIGN KEY, JOIN (INNER, LEFT)
5. **Pattern MVC** : rôle de chaque couche, flux d'une requête
6. **CRUD** : Create (INSERT), Read (SELECT), Update (UPDATE), Delete (DELETE)
7. **XSS** : `htmlspecialchars()` systématique sur tout ce qui vient de la BDD ou de l'utilisateur
8. **JSON en PHP/JS** : `json_encode()` côté PHP, `JSON.parse()` côté JS
9. **Chart.js** : initialisation d'un graphique `new Chart(canvas, { type, data, options })`
10. **Bootstrap 5** : grille (col-md-6), composants (btn, card, alert, form-control)

---

## Schéma de la base de données (à dessiner au tableau si demandé)

```
utilisateurs (id, nom, prenom, email, mot_de_passe, date_naissance, sexe, avatar, pathologie, medecin_nom)
     │
     ├──< symptomes (id, utilisateur_id, date_saisie, niveau_fatigue, niveau_humeur, [booléens], temperature, poids, notes)
     │
     ├──< bilans_biologiques (id, utilisateur_id, date_bilan, tsh, t3_libre, t4_libre, ferritine, vitamine_d, anticorps_tpo, notes)
     │
     ├──< medicaments (id, utilisateur_id, nom, dosage, moment_prise, actif)
     │         │
     │         └──< prises_medicaments (id, medicament_id, utilisateur_id, date_prise, pris)
     │
     └──< recommandations (id, utilisateur_id, message, type, lu)

Triggers : after_symptome_insert → recommandations
           after_bilan_insert    → recommandations
```

---

## Questions pièges à éviter

**"Vous stockez les mots de passe en clair ?"**  
→ Non, jamais. `password_hash()` génère un hash bcrypt irréversible. Même en accédant directement à la BDD, impossible de retrouver le mot de passe.

**"Votre site est-il protégé contre les injections SQL ?"**  
→ Oui, grâce aux requêtes préparées PDO. Je n'utilise jamais de concaténation directe de variables dans les requêtes SQL.

**"Qu'est-ce qui se passe si deux utilisateurs essaient de s'inscrire avec le même email en même temps ?"**  
→ La contrainte `UNIQUE` sur l'email dans la table MySQL rejette automatiquement le doublon au niveau de la base. PDO lève une exception PDOException que je peux attraper.

**"Pourquoi ne pas utiliser un framework comme Laravel ou Symfony ?"**  
→ Pour ce projet de taille raisonnable, un MVC natif suffit et me permet de montrer que je comprends les fondamentaux. Un framework ajoute de la complexité et du code généré que je ne maîtriserais pas complètement pour un oral.
