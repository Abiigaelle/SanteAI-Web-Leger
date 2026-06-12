<?php
// Variables disponibles : $utilisateur, $avatarsDisponibles, $message, $erreur
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <h4 class="fw-bold mb-4">
                <i class="bi bi-person-gear text-primary me-2"></i>
                Mon profil
            </h4>

            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if (!empty($erreur)): ?>
                <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($erreur) ?></div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <?= csrf_champ() ?>

                        <!-- ===== CHOIX D'AVATAR / ILLUSTRATION ===== -->
                        <!-- C'est la fonctionnalité de personnalisation graphique demandée -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold fs-5">
                                🎨 Choisir mon illustration personnelle
                            </label>
                            <div class="row g-3">
                                <?php foreach ($avatarsDisponibles as $cle => $info): ?>
                                    <div class="col-auto">
                                        <label class="avatar-option <?= ($utilisateur['avatar'] ?? 'avatar1') === $cle ? 'selected' : '' ?>">
                                            <input type="radio" name="avatar" value="<?= $cle ?>"
                                                   class="d-none"
                                                   <?= ($utilisateur['avatar'] ?? 'avatar1') === $cle ? 'checked' : '' ?>
                                                   onchange="this.closest('.row').querySelectorAll('.avatar-option').forEach(el => el.classList.remove('selected')); this.closest('.avatar-option').classList.add('selected')">
                                            <div class="avatar-choice text-center p-3">
                                                <div class="fs-1"><?= $info['emoji'] ?></div>
                                                <div class="small mt-1"><?= $info['label'] ?></div>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <hr>

                        <!-- ===== INFORMATIONS PERSONNELLES ===== -->
                        <div class="row g-3 mt-1">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Prénom *</label>
                                <input type="text" class="form-control" name="prenom" required
                                       value="<?= htmlspecialchars($utilisateur['prenom'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom *</label>
                                <input type="text" class="form-control" name="nom" required
                                       value="<?= htmlspecialchars($utilisateur['nom'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date de naissance</label>
                                <input type="date" class="form-control" name="date_naissance"
                                       value="<?= htmlspecialchars($utilisateur['date_naissance'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Médecin référent</label>
                                <input type="text" class="form-control" name="medecin_nom"
                                       placeholder="Dr. Prénom Nom"
                                       value="<?= htmlspecialchars($utilisateur['medecin_nom'] ?? '') ?>">
                            </div>

                            <!-- Email non modifiable (identifiant unique) -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Email (non modifiable)</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($utilisateur['email'] ?? '') ?>" disabled>
                                <div class="form-text">L'email est votre identifiant de connexion.</div>
                            </div>

                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-save me-2"></i>Enregistrer les modifications
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            <!-- ===================================================
                 FONCTIONNALITÉ FUTURE : Partage avec le médecin
                 Permettrait d'envoyer un rapport automatique par email au médecin.
                 Piste d'implémentation : PHP Mailer + génération PDF
            ===================================================== -->
            <div class="card border-dashed mt-4 border-0 bg-light">
                <div class="card-body text-center py-4">
                    <i class="bi bi-share fs-2 text-muted d-block mb-2"></i>
                    <h6 class="text-muted">Partage avec mon médecin</h6>
                    <p class="small text-muted">
                        Fonctionnalité prévue : envoi automatique d'un rapport PDF mensuel à votre médecin.
                    </p>
                    <button class="btn btn-outline-secondary btn-sm" disabled>
                        <i class="bi bi-envelope me-2"></i>Bientôt disponible
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
