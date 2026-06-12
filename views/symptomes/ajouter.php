<?php
// Variables disponibles depuis SymptomeController::ajouter() :
// $message, $erreur, $saisieExistante (la saisie du jour si elle existe)
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="d-flex align-items-center mb-4 gap-3">
                <h4 class="fw-bold mb-0">
                    <i class="bi bi-journal-text text-primary me-2"></i>
                    <?= $saisieExistante ? 'Modifier la saisie du jour' : 'Saisie quotidienne' ?>
                </h4>
                <span class="badge bg-light text-dark border ms-auto">
                    <?= date('d/m/Y') ?>
                </span>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-success d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($erreur)): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>

            <?php if ($saisieExistante): ?>
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    Vous avez déjà saisi vos symptômes aujourd'hui. Vous pouvez modifier votre saisie ci-dessous.
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <?= csrf_champ() ?>

                        <!-- ===== NIVEAUX (Échelles 1-5) ===== -->
                        <div class="row g-4 mb-4">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    😴 Niveau de fatigue *
                                    <span class="text-muted fw-normal">(1 = faible, 5 = épuisant)</span>
                                </label>
                                <!-- Range HTML5 : curseur de 1 à 5 -->
                                <input type="range" class="form-range" name="niveau_fatigue" id="niveau_fatigue"
                                       min="1" max="5" step="1"
                                       value="<?= $saisieExistante ? $saisieExistante['niveau_fatigue'] : 2 ?>"
                                       oninput="document.getElementById('affiche_fatigue').textContent = this.value">
                                <div class="text-center">
                                    <span class="badge bg-primary fs-6" id="affiche_fatigue">
                                        <?= $saisieExistante ? $saisieExistante['niveau_fatigue'] : 2 ?>
                                    </span>
                                    <span class="text-muted">/5</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    😊 Humeur *
                                    <span class="text-muted fw-normal">(1 = très mauvaise, 5 = excellente)</span>
                                </label>
                                <input type="range" class="form-range" name="niveau_humeur" id="niveau_humeur"
                                       min="1" max="5" step="1"
                                       value="<?= $saisieExistante ? $saisieExistante['niveau_humeur'] : 3 ?>"
                                       oninput="document.getElementById('affiche_humeur').textContent = this.value">
                                <div class="text-center">
                                    <span class="badge bg-success fs-6" id="affiche_humeur">
                                        <?= $saisieExistante ? $saisieExistante['niveau_humeur'] : 3 ?>
                                    </span>
                                    <span class="text-muted">/5</span>
                                </div>
                            </div>

                        </div>

                        <!-- ===== SYMPTÔMES BOOLÉENS (cases à cocher) ===== -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Symptômes présents aujourd'hui :</label>
                            <div class="row g-2">

                                <div class="col-md-6">
                                    <div class="form-check symptome-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="douleurs_articulaires" id="douleurs"
                                               <?= ($saisieExistante && $saisieExistante['douleurs_articulaires']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="douleurs">
                                            🦴 Douleurs articulaires
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check symptome-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="brouillard_mental" id="brouillard"
                                               <?= ($saisieExistante && $saisieExistante['brouillard_mental']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="brouillard">
                                            🧠 Brouillard mental (brain fog)
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check symptome-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="intolerances_froid" id="froid"
                                               <?= ($saisieExistante && $saisieExistante['intolerances_froid']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="froid">
                                            🥶 Intolérance au froid
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check symptome-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="chute_cheveux" id="cheveux"
                                               <?= ($saisieExistante && $saisieExistante['chute_cheveux']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="cheveux">
                                            💇 Chute de cheveux
                                        </label>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- ===== MESURES OPTIONNELLES ===== -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">🌡️ Température (°C)</label>
                                <input type="number" class="form-control" name="temperature"
                                       step="0.1" min="35" max="42" placeholder="36.8"
                                       value="<?= $saisieExistante ? $saisieExistante['temperature'] : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">⚖️ Poids (kg)</label>
                                <input type="number" class="form-control" name="poids"
                                       step="0.1" min="30" max="200" placeholder="62.5"
                                       value="<?= $saisieExistante ? $saisieExistante['poids'] : '' ?>">
                            </div>
                        </div>

                        <!-- ===== NOTES LIBRES ===== -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">📝 Notes libres</label>
                            <textarea class="form-control" name="notes" rows="3"
                                      placeholder="Comment vous sentez-vous aujourd'hui ? Des événements particuliers ?"><?= htmlspecialchars($saisieExistante['notes'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-2"></i>
                                <?= $saisieExistante ? 'Mettre à jour' : 'Enregistrer' ?>
                            </button>
                            <a href="index.php?page=symptomes&action=historique"
                               class="btn btn-outline-secondary">
                                <i class="bi bi-clock-history me-2"></i>Voir l'historique
                            </a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
