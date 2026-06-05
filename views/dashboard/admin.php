<?php
// Ce fichier est inclus par DashboardController::index() pour l'administrateur/médecin
// Les variables suivantes sont disponibles :
// $patients : liste des patients avec leur dernier état de santé (depuis vue_sante_recente)
?>

<div class="container-fluid py-4 px-4">

    <!-- ========== BANNIÈRE DE L'ESPACE SUIVI PATIENT (MÉDECIN / ADMIN) ========== -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card welcome-card border-0 text-white bg-dark shadow-sm">
                <div class="card-body d-flex align-items-center gap-4 py-4">
                    <div class="avatar-display fs-1">🩺</div>
                    <div>
                        <h4 class="mb-1 fw-bold">Espace de Suivi Médical — SantéAI 👋</h4>
                        <p class="mb-0 text-white-50">
                            <i class="bi bi-shield-check me-1"></i>
                            Connecté en tant que : <strong>Docteur / Administrateur</strong>
                        </p>
                    </div>
                    <div class="ms-auto text-end">
                        <span class="badge bg-primary fs-6 px-3 py-2">
                            Total Patients : <?= count($patients) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== TABLEAU DES PATIENTS ========== -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-people me-2 text-primary"></i>Liste des patients sous surveillance
                    </h5>
                    <div class="d-flex gap-2">
                        <input type="text" id="recherchePatient" class="form-control form-control-sm" placeholder="Rechercher un patient..." style="width: 250px;">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablePatients">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Patient</th>
                                    <th>Dernière Saisie</th>
                                    <th class="text-center">Fatigue</th>
                                    <th class="text-center">Humeur</th>
                                    <th>Dernier Bilan</th>
                                    <th>TSH Actuelle</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($patients)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            Aucun patient enregistré pour le moment.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($patients as $p): ?>
                                        <?php
                                        // Analyse des niveaux et statuts
                                        $tsh = $p['tsh'];
                                        $statutTSH = Bilan::analyserTSH($tsh);
                                        
                                        $classeFatigue = 'bg-secondary';
                                        if ($p['niveau_fatigue'] !== null) {
                                            $classeFatigue = $p['niveau_fatigue'] >= 4 ? 'bg-danger' : ($p['niveau_fatigue'] >= 3 ? 'bg-warning text-dark' : 'bg-success');
                                        }

                                        $classeHumeur = 'bg-secondary';
                                        if ($p['niveau_humeur'] !== null) {
                                            $classeHumeur = $p['niveau_humeur'] <= 2 ? 'bg-danger' : ($p['niveau_humeur'] >= 4 ? 'bg-success' : 'bg-warning text-dark');
                                        }

                                        $classeTSH = 'text-muted';
                                        $badgeTSH = '<span class="badge bg-secondary">Aucun</span>';
                                        if ($tsh !== null) {
                                            if ($statutTSH === 'eleve') {
                                                $classeTSH = 'text-danger fw-bold';
                                                $badgeTSH = '<span class="badge bg-danger"><i class="bi bi-arrow-up-circle me-1"></i>TSH Élevée</span>';
                                            } elseif ($statutTSH === 'bas') {
                                                $classeTSH = 'text-warning fw-bold';
                                                $badgeTSH = '<span class="badge bg-warning text-dark"><i class="bi bi-arrow-down-circle me-1"></i>TSH Basse</span>';
                                            } else {
                                                $classeTSH = 'text-success';
                                                $badgeTSH = '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Normal</span>';
                                            }
                                        }
                                        ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="fs-4">🌸</span>
                                                    <div>
                                                        <strong class="text-dark"><?= htmlspecialchars($p['nom'] . ' ' . $p['prenom']) ?></strong>
                                                        <div class="text-muted small">ID : #<?= $p['utilisateur_id'] ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?= $p['derniere_saisie_symptomes'] ? date('d/m/Y', strtotime($p['derniere_saisie_symptomes'])) : '<span class="text-muted">Aucune saisie</span>' ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($p['niveau_fatigue'] !== null): ?>
                                                    <span class="badge <?= $classeFatigue ?> rounded-pill px-3 py-2">
                                                        <?= $p['niveau_fatigue'] ?> / 5
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted small">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($p['niveau_humeur'] !== null): ?>
                                                    <span class="badge <?= $classeHumeur ?> rounded-pill px-3 py-2">
                                                        <?= $p['niveau_humeur'] ?> / 5
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted small">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= $p['dernier_bilan'] ? date('d/m/Y', strtotime($p['dernier_bilan'])) : '<span class="text-muted">Aucun bilan</span>' ?>
                                            </td>
                                            <td>
                                                <span class="<?= $classeTSH ?>">
                                                    <?= $tsh !== null ? number_format($tsh, 2) . ' mUI/L' : '<span class="text-muted">—</span>' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= $badgeTSH ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('recherchePatient').addEventListener('keyup', function() {
    let filter = this.value.toUpperCase();
    let rows = document.querySelector("#tablePatients tbody").rows;
    for (let i = 0; i < rows.length; i++) {
        let firstCol = rows[i].cells[0].textContent.toUpperCase();
        if (firstCol.indexOf(filter) > -1) {
            rows[i].style.display = "";
        } else {
            rows[i].style.display = "none";
        }      
    }
});
</script>
