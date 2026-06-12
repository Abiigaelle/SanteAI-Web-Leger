<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SantéAI — Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-body">

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6">

            <div class="card auth-card shadow">
                <div class="card-body p-5">

                    <div class="text-center mb-4">
                        <div class="auth-logo">💊</div>
                        <h2 class="fw-bold text-primary">Créer mon compte</h2>
                        <p class="text-muted">Rejoignez SantéAI pour suivre votre santé</p>
                    </div>

                    <!-- Message de succès après inscription -->
                    <?php if (!empty($succes)): ?>
                        <div class="alert alert-success d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i>
                            <?= htmlspecialchars($succes) ?>
                            <a href="index.php?page=auth&action=login" class="ms-auto btn btn-sm btn-success">
                                Se connecter
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($erreur)): ?>
                        <div class="alert alert-danger d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <?= htmlspecialchars($erreur) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <?= csrf_champ() ?>
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Prénom *</label>
                                <input type="text" class="form-control" name="prenom" required
                                       value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom *</label>
                                <input type="text" class="form-control" name="nom" required
                                       value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Adresse email *</label>
                                <input type="email" class="form-control" name="email" required
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date de naissance</label>
                                <input type="date" class="form-control" name="date_naissance"
                                       value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sexe</label>
                                <select class="form-select" name="sexe">
                                    <option value="F" <?= ($_POST['sexe'] ?? 'F') === 'F' ? 'selected' : '' ?>>Féminin</option>
                                    <option value="M" <?= ($_POST['sexe'] ?? '') === 'M' ? 'selected' : '' ?>>Masculin</option>
                                    <option value="Autre" <?= ($_POST['sexe'] ?? '') === 'Autre' ? 'selected' : '' ?>>Autre</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Mot de passe * (min. 8 caractères)</label>
                                <input type="password" class="form-control" name="mot_de_passe"
                                       minlength="8" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Confirmer le mot de passe *</label>
                                <input type="password" class="form-control" name="mot_de_passe_confirm"
                                       minlength="8" required>
                            </div>

                            <div class="col-12 mt-2">
                                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                    <i class="bi bi-person-check me-2"></i>Créer mon compte
                                </button>
                            </div>

                        </div>
                    </form>

                    <hr class="my-4">
                    <div class="text-center">
                        <a href="index.php?page=auth&action=login" class="text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i>Retour à la connexion
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
