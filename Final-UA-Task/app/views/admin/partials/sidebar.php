<?php
$currentController = $_GET['controller'] ?? 'admin';
// In the dedicated admin area, everyone is treated as admin.
$isAdmin = true;
?>

<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <?php if ($isAdmin): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentController === 'admin' ? 'active' : '' ?>" 
                   href="?controller=admin">
                    <i class="fas fa-cog me-2"></i>
                    Administration
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentController === 'user' ? 'active' : '' ?>" 
                   href="?controller=user">
                    <i class="fas fa-users me-2"></i>
                    Utilisateurs
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentController === 'admin' && ($_GET['action'] ?? '') === 'moderation' ? 'active' : '' ?>" 
                   href="?controller=admin&action=moderation">
                    <i class="fas fa-clipboard-check me-2"></i>
                    Modération
                </a>
            </li>
            <?php endif; ?>
            
        </ul>
    </div>
</nav>
