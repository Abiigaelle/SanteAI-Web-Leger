<?php
// Variables disponibles : $medicaments (du jour avec statut de prise), $message, $erreur
?>

<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-capsule text-primary me-2"></i>
            Mes médicaments
        </h4>
        <button class="btn btn-primary ms-auto" data-bs-toggle="collapse" data-bs-target="#formulaireAjout">
            <i class="bi bi-plus-circle me-2"></i>Ajouter un médicament
        </button>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if (!empty($erreur)): ?>
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <!-- ===== FORMULAIRE D'AJOUT (masqué par défaut, Bootstrap Collapse) ===== -->
    <div class="collapse mb-4" id="formulaireAjout">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">Nouveau médicament</h6>
                <form method="POST" action="">
                    <?= csrf_champ() ?>
                    <input type="hidden" name="action_form" value="ajouter">
                    <div class="row g-3">

                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Nom du médicament *</label>
                            <input type="text" class="form-control" name="nom" required
                                   placeholder="Ex : Lévothyroxine">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Dosage</label>
                            <input type="text" class="form-control" name="dosage"
                                   placeholder="Ex : 50 mcg">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Moment de prise</label>
                            <select class="form-select" name="moment_prise">
                                <option value="matin">Matin</option>
                                <option value="midi">Midi</option>
                                <option value="soir">Soir</option>
                                <option value="nuit">Nuit</option>
                            </select>
                        </div>

                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-save"></i>
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== LISTE DES MÉDICAMENTS DU JOUR ===== -->
    <?php if (empty($medicaments)): ?>
        <div class="card border-0 text-center py-5">
            <div class="card-body">
                <i class="bi bi-capsule fs-1 text-muted d-block mb-3"></i>
                <h5 class="text-muted">Aucun médicament enregistré</h5>
                <p class="text-muted">Ajoutez vos traitements pour suivre vos prises quotidiennes.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0">
                <small class="text-muted">
                    <i class="bi bi-calendar-check me-1"></i>
                    Suivi des prises — <?= date('d/m/Y') ?>
                </small>
            </div>
            <ul class="list-group list-group-flush">
                <?php foreach ($medicaments as $med): ?>
                    <li class="list-group-item d-flex align-items-center px-4 py-3 <?= $med['pris'] ? 'bg-light' : '' ?>">

                        <!-- Icône de statut -->
                        <i class="bi <?= $med['pris'] ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' ?> fs-4 me-3"></i>

                        <!-- Infos médicament -->
                        <div class="flex-grow-1">
                            <strong class="<?= $med['pris'] ? 'text-muted text-decoration-line-through' : '' ?>">
                                <?= htmlspecialchars($med['nom']) ?>
                            </strong>
                            <?php if ($med['dosage']): ?>
                                <span class="badge bg-secondary ms-2"><?= htmlspecialchars($med['dosage']) ?></span>
                            <?php endif; ?>
                            <br>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>
                                <?= ucfirst($med['moment_prise']) ?>
                            </small>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex gap-2">
                            <!-- Bouton cocher/décocher la prise (Sécurisé CSRF) -->
                            <a href="index.php?page=medicaments&action=prise&id=<?= $med['id'] ?>&csrf_token=<?= csrf_token() ?>"
                               class="btn btn-sm <?= $med['pris'] ? 'btn-success' : 'btn-outline-primary' ?>">
                                <i class="bi <?= $med['pris'] ? 'bi-check2' : 'bi-circle-fill' ?> me-1"></i>
                                <?= $med['pris'] ? 'Pris ✓' : 'Marquer pris' ?>
                            </a>

                            <!-- Archiver le médicament (Sécurisé CSRF) -->
                            <a href="index.php?page=medicaments&action=desactiver&id=<?= $med['id'] ?>&csrf_token=<?= csrf_token() ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Archiver ce médicament ? Il ne sera plus suivi mais l\'historique est conservé.')">
                                <i class="bi bi-archive"></i>
                            </a>
                        </div>

                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Note sur l'archivage (bonne pratique médicale) -->
        <div class="mt-3 small text-muted">
            <i class="bi bi-info-circle me-1"></i>
            Archiver un médicament ne supprime pas l'historique de suivi — les données restent disponibles pour votre médecin.
        </div>

    <?php endif; ?>
</div>
