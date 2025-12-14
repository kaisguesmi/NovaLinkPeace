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
    // Démarrer la session si pas déjà fait
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Récupération des infos utilisateur depuis la session
    $user_name = $_SESSION['username'] ?? 'Utilisateur';
    $user_role = $_SESSION['role'] ?? 'client';
    $user_id = $_SESSION['user_id'] ?? null;
    
    // Déterminer le rôle affiché
    $role_display = '';
    switch($user_role) {
        case 'organisation':
            $role_display = 'Organisation';
            break;
        case 'expert':
            $role_display = 'Expert ⭐';
            break;
        case 'client':
            $role_display = 'Client';
            break;
        case 'admin':
            $role_display = 'Administrateur';
            break;
        default:
            $role_display = 'Visiteur';
    }
    
    // Récupération action
    $current_action = $_GET['action'] ?? 'list';
    $is_organisateur = ($user_role === 'organisation' || $user_role === 'expert');
?>

<!-- NAVBAR -->
<nav class="navbar">
    
    <!-- GAUCHE : Logo + Menu -->
    <div class="navbar-left">
        <a href="/integration/NovaLinkPeace/test/View/FrontOffice/index.php" class="logo" title="Retour à l'accueil">
            <img src="assets/images/logo1-removebg-preview.png" alt="Logo PeaceLink">
        </a>

        <ul class="nav-links">
            <?php if ($is_organisateur): ?>
                <!-- MENU ORGANISATEUR / EXPERT -->
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

    <!-- DROITE : Profil + Retour Home -->
    <div class="navbar-right">
        <div class="user-profile">
            <span class="user-avatar">
                <?php if ($user_role === 'organisation'): ?>
                    <i class="fas fa-building"></i>
                <?php elseif ($user_role === 'expert'): ?>
                    <i class="fas fa-star"></i>
                <?php elseif ($user_role === 'admin'): ?>
                    <i class="fas fa-user-shield"></i>
                <?php else: ?>
                    <i class="fas fa-user-circle"></i>
                <?php endif; ?>
            </span>
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($user_name) ?></span>
                <span class="user-role"><?= htmlspecialchars($role_display) ?></span>
            </div>
        </div>

        <a href="/integration/NovaLinkPeace/test/View/FrontOffice/index.php" class="logout-btn" title="Retour à l'accueil">
            <i class="fas fa-home"></i>
        </a>
    </div>

</nav>

<!-- DÉBUT CONTENEUR (Fermé dans footer.php) -->
<div class="container">