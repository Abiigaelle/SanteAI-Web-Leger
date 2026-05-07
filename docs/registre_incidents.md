# Registre des Incidents — SantéAI (Client Léger)
**BTS SIO SLAM — Épreuve E6 — 2026**
Auteure : Abigaëlle SAIOVICI | N° candidat : 02542553866

> Ce registre simule l'utilisation du gestionnaire d'incidents **GLPI** mentionné dans la fiche de réalisation.  
> Chaque incident est enregistré avec sa date, sa criticité, sa cause et sa résolution.

---

## Tableau récapitulatif

| ID | Date | Titre | Criticité | Statut | Résolu le |
|---|---|---|---|---|---|
| INC-001 | 15/01/2026 | Erreur PDO : LIMIT avec named parameters | Moyen | ✅ Résolu | 15/01/2026 |
| INC-002 | 20/01/2026 | Doublon de recommandations au rafraîchissement | Faible | ✅ Résolu | 21/01/2026 |
| INC-003 | 28/01/2026 | Trigger SQL non déclenché sur WAMP | Élevé | ✅ Résolu | 29/01/2026 |
| INC-004 | 05/02/2026 | Formulaire symptômes : checkbox non cochées absentes de $_POST | Moyen | ✅ Résolu | 05/02/2026 |
| INC-005 | 12/02/2026 | Graphique Chart.js vide si aucune donnée | Faible | ✅ Résolu | 12/02/2026 |
| INC-006 | 20/02/2026 | Session non détruite après logout sur navigateur partagé | Élevé | ✅ Résolu | 20/02/2026 |
| INC-007 | 03/03/2026 | Navbar avatar incorrect après changement de profil | Faible | ✅ Résolu | 03/03/2026 |
| INC-008 | 10/03/2026 | Export PDF non implémenté | Faible | ⏳ En attente | — |

---

## Fiches d'incidents détaillées

---

### INC-001 — Erreur PDO : LIMIT avec named parameters
**Date d'ouverture :** 15/01/2026  
**Criticité :** Moyen  
**Composant :** `models/Symptome.php`

**Description :**  
La requête `SELECT * FROM symptomes LIMIT :limite` échouait avec l'erreur :  
`SQLSTATE[42000]: Syntax error near ''30'' at line 1`  
PDO traitait le paramètre `:limite` comme une chaîne de caractères (avec guillemets), ce qui est syntaxiquement invalide pour `LIMIT`.

**Cause identifiée :**  
PDO encapsule les paramètres nommés en chaînes par défaut (`'30'`). MySQL n'accepte pas `LIMIT '30'`, uniquement `LIMIT 30` (entier).

**Solution appliquée :**  
Remplacement de `execute([':limite' => $limite])` par `bindValue(':limite', (int)$limite, PDO::PARAM_INT)` pour forcer le type entier.

```php
// Avant (bug)
$stmt->execute([':limite' => 30]);

// Après (corrigé)
$stmt->bindValue(':limite', 30, PDO::PARAM_INT);
$stmt->execute();
```

**Statut :** ✅ Résolu le 15/01/2026

---

### INC-002 — Doublon de recommandations au rafraîchissement
**Date d'ouverture :** 20/01/2026  
**Criticité :** Faible  
**Composant :** `models/Recommandation.php`

**Description :**  
Chaque rechargement du dashboard créait une nouvelle recommandation identique dans la table, multipliant les mêmes alertes.

**Cause identifiée :**  
La méthode `genererRecommandations()` insérait sans vérifier si le conseil existait déjà pour la journée.

**Solution appliquée :**  
Création de la méthode privée `ajouterSiAbsent()` qui vérifie l'existence du message (même texte + même utilisateur + même jour) avant d'insérer.

```php
private function ajouterSiAbsent($userId, $message, $type) {
    $sql = "SELECT id FROM recommandations
            WHERE utilisateur_id = :uid AND message = :msg AND DATE(created_at) = CURDATE()";
    // ... si inexistant → INSERT
}
```

**Statut :** ✅ Résolu le 21/01/2026

---

### INC-003 — Trigger SQL non déclenché sur WAMP
**Date d'ouverture :** 28/01/2026  
**Criticité :** Élevé  
**Composant :** `database/santeai.sql`

**Description :**  
Sur un poste utilisant WAMP (vs XAMPP), les triggers n'étaient pas créés lors de l'import du fichier SQL. Aucune recommandation automatique n'était générée depuis la BDD.

**Cause identifiée :**  
Le DELIMITER `//` n'était pas correctement interprété par certaines versions de phpMyAdmin. Le parser SQL splittait le trigger sur le premier `;` rencontré à l'intérieur du corps du trigger.

**Solution appliquée :**  
Vérification de la version de phpMyAdmin et import du fichier SQL en deux temps : les tables d'abord, puis les triggers séparément. Documentation ajoutée dans le fichier SQL.

**Statut :** ✅ Résolu le 29/01/2026

---

### INC-004 — Checkbox non cochées absentes de $_POST
**Date d'ouverture :** 05/02/2026  
**Criticité :** Moyen  
**Composant :** `models/Symptome.php`, `controllers/SymptomeController.php`

**Description :**  
Quand l'utilisateur décochait "Douleurs articulaires", la valeur n'était pas mise à 0 en base de données — l'ancienne valeur restait.

**Cause identifiée :**  
En HTML, une checkbox non cochée n'envoie pas de valeur dans `$_POST`. Le code utilisait `$_POST['douleurs_articulaires']` sans valeur par défaut, donc la mise à jour ne touchait pas ce champ.

**Solution appliquée :**  
Utilisation de `isset()` pour détecter la présence de la checkbox :
```php
$douleurs = isset($data['douleurs_articulaires']) ? 1 : 0;
```

**Statut :** ✅ Résolu le 05/02/2026

---

### INC-005 — Graphique Chart.js vide si aucune donnée
**Date d'ouverture :** 12/02/2026  
**Criticité :** Faible  
**Composant :** `assets/js/main.js`

**Description :**  
Si l'utilisateur n'avait aucun symptôme enregistré, le graphique du dashboard crashait avec une erreur JavaScript et la page ne s'affichait pas correctement.

**Cause identifiée :**  
`donneesSymptomes` était un tableau vide `[]`. Chart.js n'échoue pas mais `map()` retournait des tableaux vides, ce qui affichait un canvas blanc. L'erreur venait d'une tentative d'accès à `donneesSymptomes[0].date_saisie` qui n'existait pas.

**Solution appliquée :**  
Vérification de `donneesSymptomes.length > 0` avant d'initialiser le graphique, et affichage d'un message "Aucune donnée" si le tableau est vide.

**Statut :** ✅ Résolu le 12/02/2026

---

### INC-006 — Session non détruite après logout
**Date d'ouverture :** 20/02/2026  
**Criticité :** Élevé  
**Composant :** `controllers/AuthController.php`

**Description :**  
Sur un navigateur partagé, après déconnexion, un autre utilisateur pouvait appuyer sur "Précédent" dans le navigateur et accéder aux pages protégées sans se connecter.

**Cause identifiée :**  
Seul `session_unset()` était appelé, sans `session_destroy()`. La session existait encore côté serveur.

**Solution appliquée :**  
Ajout de `session_destroy()` après `session_unset()` dans la méthode `logout()`.

```php
public function logout() {
    session_unset();   // Vide les variables de session
    session_destroy(); // Détruit la session côté serveur
    header('Location: index.php?page=auth&action=login');
    exit;
}
```

**Statut :** ✅ Résolu le 20/02/2026

---

### INC-007 — Navbar : avatar incorrect après changement de profil
**Date d'ouverture :** 03/03/2026  
**Criticité :** Faible  
**Composant :** `controllers/PatientController.php`, `views/layout/nav.php`

**Description :**  
Après avoir changé d'avatar dans la page Profil, la navbar continuait d'afficher l'ancien avatar jusqu'à la prochaine connexion.

**Cause identifiée :**  
La mise à jour BDD était correcte, mais la variable de session `$_SESSION['avatar']` n'était pas rafraîchie après le PUT.

**Solution appliquée :**  
Ajout de la mise à jour de session immédiatement après le `mettreAJour()` en BDD :
```php
$this->userModel->mettreAJour($userId, $_POST);
$_SESSION['avatar'] = $_POST['avatar'] ?? 'avatar1'; // Rafraîchissement immédiat
```

**Statut :** ✅ Résolu le 03/03/2026

---

### INC-008 — Export PDF non implémenté
**Date d'ouverture :** 10/03/2026  
**Criticité :** Faible  
**Composant :** À créer (`controllers/ExportController.php`)

**Description :**  
La fonctionnalité d'export PDF du journal de bord, demandée par la société InnoSanté, n'est pas encore implémentée. Le bouton est visible dans l'interface mais désactivé.

**Cause :** Fonctionnalité hors périmètre de la première version (phase pilote). Prévue en v2.

**Solution prévue :**  
Intégration de la bibliothèque FPDF (PHP) :
```bash
composer require setasign/fpdf
```
Création d'un `ExportController.php` générant un PDF avec les données des 30 derniers jours.

**Statut :** ⏳ En attente — v2.0
