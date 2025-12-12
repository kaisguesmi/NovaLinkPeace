<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Offres</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- FontAwesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php 
    // Récupération action et rôle
    $current_action = $_GET['action'] ?? 'list';
    $is_organisateur = isset($_GET['role']) && $_GET['role'] === 'organisateur';
?>

<!-- NAVBAR -->
<nav class="navbar">
    
    <!-- GAUCHE : Logo + Menu -->
    <div class="navbar-left">
        <a href="index.php<?= $is_organisateur ? '?role=organisateur' : '' ?>" class="logo">
            <img src="assets/images/logo1-removebg-preview.png" alt="Logo">
        </a>

        <ul class="nav-links">
            <?php if ($is_organisateur): ?>
                <!-- MENU ORGANISATEUR -->
                <li>
                    <a href="index.php?role=organisateur" class="nav-item <?= ($current_action === 'list' || $current_action === 'create' || $current_action === 'edit') ? 'active' : '' ?>">
                        <i class="fas fa-briefcase"></i> Mes Offres
                    </a>
                </li>
                <li>
                    <a href="index.php?action=list_applications&role=organisateur" class="nav-item <?= ($current_action === 'list_applications') ? 'active' : '' ?>">
                        <i class="fas fa-inbox"></i> Candidatures
                    </a>
                </li>
            <?php else: ?>
                <!-- MENU CANDIDAT -->
                <li>
                    <a href="index.php" class="nav-item <?= ($current_action === 'list' || $current_action === 'apply') ? 'active' : '' ?>">
                        <i class="fas fa-search"></i> Trouver une mission
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- DROITE : Profil + Switch Role -->
    <div class="navbar-right">
        <div class="user-profile">
            <span class="user-avatar">
                <i class="<?= $is_organisateur ? 'fas fa-user-shield' : 'fas fa-user-circle' ?>"></i>
            </span>
            <div class="user-info">
                <span class="user-name"><?= $is_organisateur ? 'Organisateur' : 'Candidat' ?></span>
                <span class="user-role"><?= $is_organisateur ? 'Gestionnaire' : 'Visiteur' ?></span>
            </div>
        </div>

        <a href="index.php<?= $is_organisateur ? '' : '?role=organisateur' ?>" class="logout-btn" title="<?= $is_organisateur ? 'Quitter mode Organisateur' : 'Passer Organisateur' ?>">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>

</nav>

<!-- DÉBUT CONTENEUR (Fermé dans footer.php) -->
<div class="container">