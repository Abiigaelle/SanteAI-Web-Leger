<?php
// Variables disponibles : $bilans (tableau des bilans)
// Valeurs normales TSH : 0.4 - 4.0
?>

<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-graph-up text-primary me-2"></i>
            Bilans biologiques
        </h4>
        <a href="index.php?page=bilans&action=ajouter" class="btn btn-primary ms-auto">
            <i class="bi bi-plus-circle me-2"></i>Ajouter un bilan
        </a>
    </div>

    <?php if (empty($bilans)): ?>
        <div class="card border-0 text-center py-5">
            <div class="card-body">
                <i class="bi bi-clipboard-x fs-1 text-muted d-block mb-3"></i>
                <h5 class="text-muted">Aucun bilan enregistré</h5>
                <a href="index.php?page=bilans&action=ajouter" class="btn btn-primary mt-2">
                    Ajouter mon premier bilan
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
                                <th class="text-center">TSH <small class="text-muted">(0,4–4,0)</small></th>
                                <th class="text-center">T3 libre</th>
                                <th class="text-center">T4 libre</th>
                                <th class="text-center">Ferritine</th>
                                <th class="text-center">Vit. D</th>
                                <th class="text-center">Anti-TPO</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($bilans as $b): ?>
                            <?php
                            // Analyse TSH pour colorisation
                            $statutTSH = Bilan::analyserTSH($b['tsh']);
                            $classeTSH = match($statutTSH) {
                                'eleve' => 'text-danger fw-bold',
                                'bas'   => 'text-warning fw-bold',
                                default => 'text-success',
                            };
                            ?>
                            <tr>
                                <td><strong><?= date('d/m/Y', strtotime($b['date_bilan'])) ?></strong></td>

                                <td class="text-center">
                                    <?php if ($b['tsh'] !== null): ?>
                                        <span class="<?= $classeTSH ?>">
                                            <?= number_format($b['tsh'], 3) ?>
                                        </span>
                                        <?php if ($statutTSH !== 'normal'): ?>
                                            <i class="bi bi-exclamation-triangle-fill text-warning ms-1" title="Hors norme"></i>
                                        <?php endif; ?>
                                    <?php else: ?>—<?php endif; ?>
                                </td>

                                <td class="text-center text-muted">
                                    <?= $b['t3_libre'] !== null ? number_format($b['t3_libre'], 2) : '—' ?>
                                </td>
                                <td class="text-center text-muted">
                                    <?= $b['t4_libre'] !== null ? number_format($b['t4_libre'], 2) : '—' ?>
                                </td>
                                <td class="text-center text-muted">
                                    <?= $b['ferritine'] !== null ? number_format($b['ferritine'], 1) : '—' ?>
                                </td>
                                <td class="text-center <?= ($b['vitamine_d'] !== null && $b['vitamine_d'] < 50) ? 'text-danger' : 'text-muted' ?>">
                                    <?= $b['vitamine_d'] !== null ? number_format($b['vitamine_d'], 1) : '—' ?>
                                </td>
                                <td class="text-center <?= ($b['anticorps_tpo'] !== null && $b['anticorps_tpo'] > 35) ? 'text-warning' : 'text-muted' ?>">
                                    <?= $b['anticorps_tpo'] !== null ? number_format($b['anticorps_tpo'], 1) : '—' ?>
                                </td>

                                <td class="text-end">
                                    <a href="index.php?page=bilans&action=supprimer&id=<?= $b['id'] ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Supprimer ce bilan ?')">
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

        <!-- Légende des couleurs -->
        <div class="mt-3 small text-muted">
            <span class="text-danger me-3"><i class="bi bi-circle-fill me-1"></i>Valeur élevée (hors norme haute)</span>
            <span class="text-warning me-3"><i class="bi bi-circle-fill me-1"></i>Valeur basse (hors norme basse)</span>
            <span class="text-success"><i class="bi bi-circle-fill me-1"></i>Dans les normes</span>
        </div>

    <?php endif; ?>
</div>
