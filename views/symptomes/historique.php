<?php
// Variables disponibles : $symptomes (tableau des saisies)
?>

<div class="container py-4">
    <div class="d-flex align-items-center mb-4 gap-3">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-clock-history text-primary me-2"></i>
            Historique des symptômes
        </h4>
        <a href="index.php?page=symptomes&action=ajouter" class="btn btn-primary ms-auto">
            <i class="bi bi-plus-circle me-2"></i>Saisie du jour
        </a>
    </div>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'supprime'): ?>
        <div class="alert alert-success">Saisie supprimée avec succès.</div>
    <?php endif; ?>

    <?php if (empty($symptomes)): ?>
        <div class="card border-0 text-center py-5">
            <div class="card-body">
                <i class="bi bi-journal-x fs-1 text-muted d-block mb-3"></i>
                <h5 class="text-muted">Aucune saisie enregistrée</h5>
                <a href="index.php?page=symptomes&action=ajouter" class="btn btn-primary mt-2">
                    Faire ma première saisie
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th class="text-center">Fatigue</th>
                                <th class="text-center">Humeur</th>
                                <th>Symptômes</th>
                                <th class="text-center">Temp.</th>
                                <th class="text-center">Poids</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($symptomes as $s): ?>
                            <tr>
                                <td>
                                    <strong><?= date('d/m/Y', strtotime($s['date_saisie'])) ?></strong>
                                    <?php if ($s['date_saisie'] === date('Y-m-d')): ?>
                                        <span class="badge bg-primary ms-1">Aujourd'hui</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Fatigue avec badge coloré selon le niveau -->
                                <td class="text-center">
                                    <span class="badge <?= $s['niveau_fatigue'] >= 4 ? 'bg-danger' : ($s['niveau_fatigue'] >= 3 ? 'bg-warning text-dark' : 'bg-success') ?> fs-6">
                                        <?= $s['niveau_fatigue'] ?>/5
                                    </span>
                                </td>

                                <!-- Humeur avec badge coloré -->
                                <td class="text-center">
                                    <span class="badge <?= $s['niveau_humeur'] <= 2 ? 'bg-danger' : ($s['niveau_humeur'] === 3 ? 'bg-warning text-dark' : 'bg-success') ?> fs-6">
                                        <?= $s['niveau_humeur'] ?>/5
                                    </span>
                                </td>

                                <!-- Icônes des symptômes booléens -->
                                <td>
                                    <?php if ($s['douleurs_articulaires']): ?><span title="Douleurs articulaires">🦴</span><?php endif; ?>
                                    <?php if ($s['brouillard_mental']): ?><span title="Brouillard mental">🧠</span><?php endif; ?>
                                    <?php if ($s['intolerances_froid']): ?><span title="Intolérance au froid">🥶</span><?php endif; ?>
                                    <?php if ($s['chute_cheveux']): ?><span title="Chute de cheveux">💇</span><?php endif; ?>
                                    <?php if (!$s['douleurs_articulaires'] && !$s['brouillard_mental'] && !$s['intolerances_froid'] && !$s['chute_cheveux']): ?>
                                        <span class="text-muted small">Aucun</span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center text-muted">
                                    <?= $s['temperature'] ? number_format($s['temperature'], 1) . '°C' : '—' ?>
                                </td>

                                <td class="text-center text-muted">
                                    <?= $s['poids'] ? number_format($s['poids'], 1) . ' kg' : '—' ?>
                                </td>

                                <td class="text-end">
                                    <!-- Lien de suppression avec confirmation JavaScript -->
                                    <a href="index.php?page=symptomes&action=supprimer&id=<?= $s['id'] ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Supprimer cette saisie ?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
