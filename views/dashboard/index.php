<?php
// Ce fichier est inclus par DashboardController::index()
// Les variables suivantes sont disponibles depuis le contrôleur :
// $dernierSymptome, $dernierBilan, $medicamentsDuJour, $tauxAdherence,
// $recommandations, $symptomesJSON, $bilansJSON, $statutTSH

// Helpers pour les emojis d'avatar
$avatarEmoji = [
    'avatar1' => '🌸', 'avatar2' => '🌿', 'avatar3' => '⭐',
    'avatar4' => '🦋', 'avatar5' => '🌊',
];
$emoji = $avatarEmoji[$_SESSION['avatar'] ?? 'avatar1'] ?? '🌸';
?>

<div class="container-fluid py-4 px-4">

    <!-- ========== BANNIÈRE D'ACCUEIL ========== -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card welcome-card border-0">
                <div class="card-body d-flex align-items-center gap-4">
                    <div class="avatar-display fs-1"><?= $emoji ?></div>
                    <div>
                        <h4 class="mb-1 fw-bold">Bonjour, <?= htmlspecialchars($_SESSION['prenom'] ?? '') ?> ! 👋</h4>
                        <p class="mb-0 text-muted">
                            <i class="bi bi-heart-pulse me-1"></i>
                            Pathologie suivie : <strong><?= htmlspecialchars($_SESSION['pathologie'] ?? 'Thyroïdite de Hashimoto') ?></strong>
                        </p>
                        <?php if (!empty($_SESSION['medecin_nom'])): ?>
                            <small class="text-muted">
                                <i class="bi bi-person-badge me-1"></i>
                                Médecin : <?= htmlspecialchars($_SESSION['medecin_nom']) ?>
                            </small>
                        <?php endif; ?>
                    </div>
                    <div class="ms-auto">
                        <a href="index.php?page=symptomes&action=ajouter" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Saisie du jour
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== CARTES DE STATISTIQUES ========== -->
    <div class="row g-3 mb-4">

        <!-- Fatigue du jour -->
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card h-100 border-0">
                <div class="card-body text-center">
                    <div class="stat-icon mb-2">😴</div>
                    <p class="text-muted small mb-1">Fatigue (aujourd'hui)</p>
                    <?php if ($dernierSymptome): ?>
                        <h3 class="fw-bold <?= $dernierSymptome['niveau_fatigue'] >= 4 ? 'text-danger' : 'text-success' ?>">
                            <?= $dernierSymptome['niveau_fatigue'] ?>/5
                        </h3>
                    <?php else: ?>
                        <h5 class="text-muted">Non saisie</h5>
                        <a href="index.php?page=symptomes&action=ajouter" class="btn btn-sm btn-outline-primary mt-1">Saisir</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Humeur du jour -->
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card h-100 border-0">
                <div class="card-body text-center">
                    <div class="stat-icon mb-2">😊</div>
                    <p class="text-muted small mb-1">Humeur (aujourd'hui)</p>
                    <?php if ($dernierSymptome): ?>
                        <h3 class="fw-bold <?= $dernierSymptome['niveau_humeur'] <= 2 ? 'text-danger' : 'text-success' ?>">
                            <?= $dernierSymptome['niveau_humeur'] ?>/5
                        </h3>
                    <?php else: ?>
                        <h5 class="text-muted">Non saisie</h5>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Dernière TSH -->
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card h-100 border-0">
                <div class="card-body text-center">
                    <div class="stat-icon mb-2">🩺</div>
                    <p class="text-muted small mb-1">Dernière TSH</p>
                    <?php if ($dernierBilan && $dernierBilan['tsh'] !== null): ?>
                        <?php
                        $couleurTSH = match($statutTSH) {
                            'eleve' => 'text-danger',
                            'bas'   => 'text-warning',
                            default => 'text-success',
                        };
                        ?>
                        <h3 class="fw-bold <?= $couleurTSH ?>">
                            <?= number_format($dernierBilan['tsh'], 2) ?>
                            <small class="fs-6">mUI/L</small>
                        </h3>
                        <small class="text-muted"><?= date('d/m/Y', strtotime($dernierBilan['date_bilan'])) ?></small>
                    <?php else: ?>
                        <h5 class="text-muted">Aucun bilan</h5>
                        <a href="index.php?page=bilans&action=ajouter" class="btn btn-sm btn-outline-primary mt-1">Ajouter</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Adhérence médicaments -->
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card h-100 border-0">
                <div class="card-body text-center">
                    <div class="stat-icon mb-2">💊</div>
                    <p class="text-muted small mb-1">Adhérence (7j)</p>
                    <h3 class="fw-bold <?= $tauxAdherence >= 80 ? 'text-success' : ($tauxAdherence >= 50 ? 'text-warning' : 'text-danger') ?>">
                        <?= $tauxAdherence ?>%
                    </h3>
                    <!-- Barre de progression Bootstrap -->
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar <?= $tauxAdherence >= 80 ? 'bg-success' : ($tauxAdherence >= 50 ? 'bg-warning' : 'bg-danger') ?>"
                             style="width: <?= $tauxAdherence ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ========== GRAPHIQUES ========== -->
    <div class="row g-3 mb-4">

        <!-- Graphique évolution symptômes (Chart.js) -->
        <div class="col-md-8">
            <div class="card border-0 h-100">
                <div class="card-header bg-transparent border-0">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-graph-up me-2 text-primary"></i>
                        Évolution fatigue & humeur (30 derniers jours)
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Le canvas est l'élément HTML sur lequel Chart.js dessine -->
                    <canvas id="chartSymptomes" height="120"></canvas>
                    <!-- Les données PHP sont passées à JavaScript via json_encode() -->
                    <script>
                        // Variable JavaScript initialisée depuis PHP
                        var donneesSymptomes = <?= $symptomesJSON ?>;
                    </script>
                </div>
            </div>
        </div>

        <!-- Graphique évolution TSH -->
        <div class="col-md-4">
            <div class="card border-0 h-100">
                <div class="card-header bg-transparent border-0">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-activity me-2 text-danger"></i>
                        Évolution TSH
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="chartTSH" height="180"></canvas>
                    <script>
                        var donneesBilans = <?= $bilansJSON ?>;
                    </script>
                </div>
            </div>
        </div>

    </div>

    <!-- ========== MÉDICAMENTS + RECOMMANDATIONS ========== -->
    <div class="row g-3">

        <!-- Médicaments du jour -->
        <div class="col-md-6">
            <div class="card border-0">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-capsule me-2 text-primary"></i>
                        Médicaments du jour
                    </h6>
                    <a href="index.php?page=medicaments&action=liste" class="btn btn-sm btn-outline-primary">
                        Gérer
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($medicamentsDuJour)): ?>
                        <p class="text-muted text-center">
                            Aucun médicament enregistré.
                            <a href="index.php?page=medicaments&action=liste">Ajouter un médicament</a>
                        </p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                        <?php foreach ($medicamentsDuJour as $med): ?>
                            <li class="list-group-item d-flex align-items-center justify-content-between px-0">
                                <div>
                                    <!-- Icône colorée selon le statut de prise -->
                                    <i class="bi <?= $med['pris'] ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' ?> me-2 fs-5"></i>
                                    <strong><?= htmlspecialchars($med['nom']) ?></strong>
                                    <?php if ($med['dosage']): ?>
                                        <span class="text-muted ms-1"><?= htmlspecialchars($med['dosage']) ?></span>
                                    <?php endif; ?>
                                    <br>
                                    <small class="text-muted ms-4">
                                        <i class="bi bi-clock me-1"></i><?= ucfirst($med['moment_prise']) ?>
                                    </small>
                                </div>
                                <!-- Lien pour cocher/décocher la prise -->
                                <a href="index.php?page=medicaments&action=prise&id=<?= $med['id'] ?>"
                                   class="btn btn-sm <?= $med['pris'] ? 'btn-success' : 'btn-outline-secondary' ?>">
                                    <?= $med['pris'] ? 'Pris ✓' : 'Marquer pris' ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recommandations de l'algorithme -->
        <div class="col-md-6">
            <div class="card border-0">
                <div class="card-header bg-transparent border-0">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-lightbulb me-2 text-warning"></i>
                        Recommandations de l'IA
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (empty($recommandations)): ?>
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-2"></i>
                            Tout va bien ! Aucun conseil particulier pour aujourd'hui.
                        </div>
                    <?php else: ?>
                        <?php foreach ($recommandations as $reco): ?>
                            <?php
                            // Classe Bootstrap selon le type de recommandation
                            $classeAlerte = match($reco['type']) {
                                'alerte'  => 'alert-danger',
                                'conseil' => 'alert-warning',
                                default   => 'alert-info',
                            };
                            $icone = match($reco['type']) {
                                'alerte'  => 'bi-exclamation-triangle-fill',
                                'conseil' => 'bi-lightbulb-fill',
                                default   => 'bi-info-circle-fill',
                            };
                            ?>
                            <div class="alert <?= $classeAlerte ?> d-flex align-items-start gap-2 mb-2">
                                <i class="bi <?= $icone ?> mt-1"></i>
                                <div>
                                    <small><?= htmlspecialchars($reco['message']) ?></small>
                                    <br>
                                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($reco['created_at'])) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- ===================================================
                         FONCTIONNALITÉ À COMPLÉTER LORS DE L'ORAL
                         Export PDF du journal de bord pour le médecin.
                         Piste d'implémentation : bibliothèque FPDF ou TCPDF en PHP.
                         Commande : composer require setasign/fpdf
                    ===================================================== -->
                    <div class="mt-3 text-center">
                        <button class="btn btn-outline-secondary btn-sm" disabled
                                title="Fonctionnalité en cours de développement">
                            <i class="bi bi-file-pdf me-2"></i>
                            Export PDF journal de bord
                            <span class="badge bg-secondary ms-1">Bientôt disponible</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>

</div>
