<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SantéAI — Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-body">

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5">

            <!-- Carte de connexion -->
            <div class="card auth-card shadow">
                <div class="card-body p-5">

                    <!-- En-tête avec logo -->
                    <div class="text-center mb-4">
                        <div class="auth-logo">💊</div>
                        <h2 class="fw-bold text-primary">SantéAI</h2>
                        <p class="text-muted">Votre espace de suivi santé</p>
                    </div>

                    <!-- Affichage de l'erreur si connexion échouée -->
                    <?php if (!empty($erreur)): ?>
                        <div class="alert alert-danger d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <?= htmlspecialchars($erreur) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Formulaire de connexion -->
                    <!-- method="POST" : les données sont envoyées dans le corps de la requête (sécurisé) -->
                    <!-- action="" : envoi vers la même page (le contrôleur traite le POST) -->
                    <form method="POST" action="">

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Adresse email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email"
                                       placeholder="votre@email.fr" required
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="mot_de_passe" class="form-label fw-semibold">Mot de passe</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="mot_de_passe"
                                       name="mot_de_passe" placeholder="••••••••" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                        </button>

                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="text-muted mb-0">Pas encore de compte ?</p>
                        <a href="index.php?page=auth&action=register" class="btn btn-outline-secondary mt-2">
                            <i class="bi bi-person-plus me-2"></i>Créer un compte
                        </a>
                    </div>

                    <!-- Compte de démonstration (pour l'oral BTS) -->
                    <div class="alert alert-info mt-4 mb-0 small">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Compte démo :</strong> marie.dupont@exemple.fr / patient123
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
