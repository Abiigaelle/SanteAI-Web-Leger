<?php
// Variables disponibles : $message, $erreur
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="d-flex align-items-center mb-4">
                <h4 class="fw-bold mb-0">
                    <i class="bi bi-graph-up text-primary me-2"></i>
                    Ajouter un bilan biologique
                </h4>
                <a href="index.php?page=bilans&action=historique" class="btn btn-outline-secondary btn-sm ms-auto">
                    <i class="bi bi-list-ul me-2"></i>Voir l'historique
                </a>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if (!empty($erreur)): ?>
                <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($erreur) ?></div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <!-- Info sur les valeurs normales -->
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Valeurs de référence Hashimoto :</strong>
                        TSH : 0,4–4,0 mUI/L | T3 libre : 3,1–6,8 pmol/L | T4 libre : 12–22 pmol/L | Vitamine D optimale : > 75 nmol/L
                    </div>

                    <form method="POST" action="">
                        <?= csrf_champ() ?>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">📅 Date du bilan *</label>
                                <input type="date" class="form-control" name="date_bilan"
                                       max="<?= date('Y-m-d') ?>" required
                                       value="<?= $_POST['date_bilan'] ?? date('Y-m-d') ?>">
                            </div>

                            <!-- Groupe Thyroïde -->
                            <div class="col-12 mt-3">
                                <h6 class="fw-bold text-primary border-bottom pb-2">
                                    <i class="bi bi-activity me-2"></i>Marqueurs thyroïdiens
                                </h6>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">TSH (mUI/L)</label>
                                <input type="number" class="form-control" name="tsh"
                                       step="0.001" min="0" max="100" placeholder="3.2"
                                       value="<?= $_POST['tsh'] ?? '' ?>">
                                <div class="form-text">Normale : 0,4 – 4,0</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">T3 libre (pmol/L)</label>
                                <input type="number" class="form-control" name="t3_libre"
                                       step="0.01" min="0" max="50" placeholder="4.5"
                                       value="<?= $_POST['t3_libre'] ?? '' ?>">
                                <div class="form-text">Normale : 3,1 – 6,8</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">T4 libre (pmol/L)</label>
                                <input type="number" class="form-control" name="t4_libre"
                                       step="0.01" min="0" max="100" placeholder="17.2"
                                       value="<?= $_POST['t4_libre'] ?? '' ?>">
                                <div class="form-text">Normale : 12 – 22</div>
                            </div>

                            <!-- Groupe Micronutriments -->
                            <div class="col-12 mt-3">
                                <h6 class="fw-bold text-success border-bottom pb-2">
                                    <i class="bi bi-droplet me-2"></i>Micronutriments & Anticorps
                                </h6>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Ferritine (µg/L)</label>
                                <input type="number" class="form-control" name="ferritine"
                                       step="0.01" min="0" max="1000" placeholder="68"
                                       value="<?= $_POST['ferritine'] ?? '' ?>">
                                <div class="form-text">Femme : 15 – 150</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Vitamine D (nmol/L)</label>
                                <input type="number" class="form-control" name="vitamine_d"
                                       step="0.01" min="0" max="500" placeholder="72"
                                       value="<?= $_POST['vitamine_d'] ?? '' ?>">
                                <div class="form-text">Optimale : > 75</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Anticorps anti-TPO (UI/mL)</label>
                                <input type="number" class="form-control" name="anticorps_tpo"
                                       step="0.01" min="0" placeholder="180"
                                       value="<?= $_POST['anticorps_tpo'] ?? '' ?>">
                                <div class="form-text">Normale : < 35</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">📝 Notes du médecin / contexte</label>
                                <textarea class="form-control" name="notes" rows="2"
                                          placeholder="Contexte du bilan, remarques du médecin..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                            </div>

                            <div class="col-12 mt-2">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-save me-2"></i>Enregistrer le bilan
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
