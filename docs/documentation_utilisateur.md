# Guide d'utilisation — SantéAI
**Plateforme de suivi — Thyroïdite de Hashimoto**

---

## Accès à la plateforme

Ouvrez votre navigateur et allez sur : **http://localhost:8080**

---

## 1. Créer un compte

1. Sur la page de connexion, cliquez sur **"Créer un compte"**
2. Remplissez le formulaire :
   - Prénom, Nom (obligatoires)
   - Adresse email (identifiant de connexion, doit être unique)
   - Date de naissance, sexe (optionnels)
   - Mot de passe (minimum 6 caractères) + confirmation
3. Cliquez sur **"Créer mon compte"**
4. Un message de succès apparaît → cliquez sur **"Se connecter"**

---

## 2. Se connecter

1. Saisissez votre **email** et votre **mot de passe**
2. Cliquez sur **"Se connecter"**
3. Vous êtes redirigé vers votre tableau de bord

> **Compte de démonstration :**  
> Email : `marie.dupont@exemple.fr` | Mot de passe : `patient123`

---

## 3. Tableau de bord

Le tableau de bord est votre page principale. Il affiche :

| Zone | Contenu |
|---|---|
| **Bannière** | Votre prénom, illustration, pathologie et médecin référent |
| **Cartes stats** | Fatigue du jour, humeur du jour, dernière TSH, taux d'adhérence médicaments |
| **Graphique symptômes** | Évolution fatigue/humeur sur 30 jours |
| **Graphique TSH** | Évolution de votre TSH sur les derniers bilans |
| **Médicaments du jour** | Liste avec boutons pour cocher vos prises |
| **Recommandations** | Conseils générés automatiquement par l'application |

> Le bouton **"Saisie du jour"** (en haut à droite) permet d'accéder directement au formulaire de saisie des symptômes.

---

## 4. Saisir ses symptômes quotidiens

**Menu → "Mes symptômes"**

1. Réglez le curseur **Fatigue** de 1 (faible) à 5 (épuisant)
2. Réglez le curseur **Humeur** de 1 (très mauvaise) à 5 (excellente)
3. Cochez les symptômes présents :
   - 🦴 Douleurs articulaires
   - 🧠 Brouillard mental (brain fog)
   - 🥶 Intolérance au froid
   - 💇 Chute de cheveux
4. Saisissez optionnellement votre température et votre poids
5. Ajoutez des notes libres si vous le souhaitez
6. Cliquez sur **"Enregistrer"**

> **Important :** une seule saisie est possible par jour. Si vous revenez sur cette page le même jour, vous pourrez modifier votre saisie existante.

---

## 5. Consulter l'historique des symptômes

**Menu → "Mes symptômes" → "Voir l'historique"**

Le tableau affiche les 60 dernières saisies avec :
- Les niveaux de fatigue/humeur (code couleur : vert = bon, rouge = élevé)
- Les icônes des symptômes présents
- La température et le poids si renseignés

Vous pouvez **supprimer une saisie** en cliquant sur 🗑️ (une confirmation est demandée).

---

## 6. Ajouter un bilan biologique

**Menu → "Bilans biologiques" → "Ajouter un bilan"**

1. Sélectionnez la **date du bilan** (obligatoire)
2. Saisissez les valeurs disponibles dans vos résultats :
   - **Marqueurs thyroïdiens :** TSH, T3 libre, T4 libre
   - **Micronutriments :** Ferritine, Vitamine D, Anticorps anti-TPO
3. Ajoutez des notes (remarques du médecin, contexte du bilan)
4. Cliquez sur **"Enregistrer le bilan"**

> Les valeurs de référence sont affichées sous chaque champ. Une alerte automatique est générée si votre TSH est hors norme.

---

## 7. Consulter l'historique des bilans

**Menu → "Bilans biologiques"**

Le tableau affiche tous vos bilans avec :
- Les valeurs en **rouge** si elles sont hors norme haute
- Les valeurs en **orange** si elles sont hors norme basse
- Les valeurs en gris normal si elles sont dans les normes

---

## 8. Gérer ses médicaments

**Menu → "Médicaments"**

### Ajouter un médicament
1. Cliquez sur **"Ajouter un médicament"**
2. Renseignez le nom (ex : "Lévothyroxine"), le dosage (ex : "50 mcg") et le moment de prise
3. Cliquez sur 💾

### Cocher une prise
- Sur la liste du jour, cliquez sur **"Marquer pris"** lorsque vous avez pris votre médicament
- Le bouton passe en vert avec "Pris ✓"
- Vous pouvez le décocher en cliquant à nouveau

### Archiver un médicament
- Cliquez sur l'icône 📦 pour archiver un traitement arrêté
- L'historique de prises est conservé

---

## 9. Personnaliser son profil

**Menu → votre prénom → "Profil"**

1. Choisissez votre **illustration** parmi les 5 disponibles :
   - 🌸 Fleur de cerisier
   - 🌿 Nature
   - ⭐ Étoile
   - 🦋 Papillon
   - 🌊 Vague
2. Modifiez vos informations personnelles
3. Renseignez le **nom de votre médecin référent** (affiché sur le tableau de bord)
4. Cliquez sur **"Enregistrer les modifications"**

> L'adresse email ne peut pas être modifiée car c'est votre identifiant de connexion.

---

## 10. Se déconnecter

Cliquez sur **"Déconnexion"** (en haut à droite de la barre de navigation).  
Vous êtes redirigé vers la page de connexion et votre session est sécurisée.

---

## Comprendre les recommandations

Les recommandations sont générées automatiquement par l'application selon vos données :

| Couleur | Signification |
|---|---|
| 🔴 Rouge (Alerte) | Action urgente recommandée (ex : TSH anormale, fatigue persistante) |
| 🟡 Jaune (Conseil) | Suggestion bénéfique (ex : penser à faire un bilan) |
| 🔵 Bleu (Info) | Information générale |

> **Important :** Les recommandations de SantéAI sont des suggestions basées sur vos données. Elles ne remplacent pas l'avis de votre médecin.

---

## En cas de problème

| Problème | Solution |
|---|---|
| Le site ne s'ouvre pas | Vérifiez que MySQL est lancé dans XAMPP et relancez `lancer.bat` |
| "Erreur de connexion BDD" | Vérifiez que MySQL tourne et que la BDD `santeai` existe |
| Mot de passe oublié | Fonctionnalité non encore implémentée — contactez l'administrateur |
| Page blanche | Vérifiez la console PHP dans le terminal |
