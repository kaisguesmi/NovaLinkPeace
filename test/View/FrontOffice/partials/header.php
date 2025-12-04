<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="main-navbar">
    <div class="navbar-container">
        <!-- Logo -->
        <div class="navbar-brand">
            <a href="index.php">
                <img src="mon-logo.png" alt="Logo PeaceLink" class="logo-img">
                <span class="site-name">PeaceLink</span>
            </a>
        </div>
        <div class="search-bar-container">
            <form action="../../Controller/UtilisateurController.php" method="GET" class="search-form">
                <input type="hidden" name="action" value="search">
                <input type="text" name="q" placeholder="Rechercher un membre..." required>
                <button type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>

        <!-- Navigation -->
        <nav class="navbar-links">
            <ul>
                <li><a href="index.php" class="nav-link active">Home</a></li>
                <li><a href="#" class="nav-link">Stories</a></li>
                <li><a href="#" class="nav-link">Initiatives</a></li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- UTILISATEUR CONNECTÉ -->
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <!-- CAS ADMIN : Lien vers le BackOffice -->
                        <li>
                            <a href="../BackOffice/backoffice.php" class="nav-link" style="color: var(--rouge-alerte); font-weight: bold;">
                                <i class="fa-solid fa-gauge"></i> Administration
                            </a>
                        </li>
                    <?php else: ?>
                        <!-- CAS CLIENT / ORGA : Lien vers le Profil -->
                        <li><a href="profile.php" class="nav-link">Profile</a></li>
                    <?php endif; ?>

                    <li><a href="../../Controller/UtilisateurController.php?action=logout" class="nav-link btn-join-us">Logout</a></li>
                
                <?php else: ?>
                    <!-- VISITEUR NON CONNECTÉ -->
                    <li><a href="inscription.php" class="nav-link btn-join-us">Join Us</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>