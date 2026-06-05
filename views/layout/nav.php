<?php
// Détermine la page active pour surligner le bon lien dans la navigation
$pageCourante = $_GET['page'] ?? 'dashboard';
?>

<nav class="navbar navbar-expand-lg navbar-santeai">
    <div class="container-fluid">

        <!-- Logo / Nom de l'application -->
        <a class="navbar-brand" href="index.php?page=dashboard">
            <span class="logo-icon">💊</span>
            <strong>SantéAI</strong>
        </a>

        <!-- Bouton hamburger pour mobile (Bootstrap gère l'ouverture/fermeture) -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">
            <!-- Menu de navigation principal -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link <?= $pageCourante === 'dashboard' ? 'active' : '' ?>"
                       href="index.php?page=dashboard">
                        <i class="bi bi-grid-1x2"></i> Tableau de bord
                    </a>
                </li>

                <?php if (($_SESSION['role'] ?? 'patient') !== 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $pageCourante === 'symptomes' ? 'active' : '' ?>"
                           href="index.php?page=symptomes&action=ajouter">
                            <i class="bi bi-journal-text"></i> Mes symptômes
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= $pageCourante === 'bilans' ? 'active' : '' ?>"
                           href="index.php?page=bilans&action=historique">
                            <i class="bi bi-graph-up"></i> Bilans biologiques
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= $pageCourante === 'medicaments' ? 'active' : '' ?>"
                           href="index.php?page=medicaments&action=liste">
                            <i class="bi bi-capsule"></i> Médicaments
                        </a>
                    </li>
                <?php endif; ?>

            </ul>

            <!-- Partie droite : avatar + nom + déconnexion -->
            <div class="d-flex align-items-center gap-3">
                <span class="navbar-text">
                    <span class="avatar-nav">
                        <?php if (($_SESSION['role'] ?? 'patient') === 'admin'): ?>
                            🩺
                        <?php else: ?>
                            <?= htmlspecialchars($_SESSION['avatar'] ?? 'avatar1') === 'avatar1' ? '🌸' : (htmlspecialchars($_SESSION['avatar'] ?? '') === 'avatar2' ? '🌿' : (htmlspecialchars($_SESSION['avatar'] ?? '') === 'avatar3' ? '⭐' : (htmlspecialchars($_SESSION['avatar'] ?? '') === 'avatar4' ? '🦋' : '🌊'))) ?>
                        <?php endif; ?>
                    </span>
                    <strong><?= htmlspecialchars($_SESSION['prenom'] ?? '') ?></strong>
                </span>

                <?php if (($_SESSION['role'] ?? 'patient') !== 'admin'): ?>
                    <a class="nav-link <?= $pageCourante === 'patient' ? 'active' : '' ?>"
                       href="index.php?page=patient&action=profil">
                        <i class="bi bi-person-gear"></i> Profil
                    </a>
                <?php endif; ?>

                <a class="btn btn-outline-danger btn-sm"
                   href="index.php?page=auth&action=logout">
                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                </a>
            </div>
        </div>

    </div>
</nav>

<!-- Contenu principal : les vues seront insérées ici -->
<main class="main-content">
