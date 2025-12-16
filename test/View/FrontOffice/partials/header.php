<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="main-navbar">
    <div class="navbar-container">
        <!-- Logo -->
        <div class="navbar-brand">
            <a href="/integration/NovaLinkPeace/test/View/FrontOffice/index.php">
                <img src="/integration/NovaLinkPeace/test/View/FrontOffice/mon-logo.png" alt="Logo PeaceLink" class="logo-img">
                <span class="site-name">PeaceLink</span>
            </a>
        </div>
        <div class="search-bar-container">
            <form action="/integration/NovaLinkPeace/test/Controller/UtilisateurController.php" method="GET" class="search-form">
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
                <li><a href="/integration/NovaLinkPeace/test/View/FrontOffice/index.php" class="nav-link active">Home</a></li>
                <li><a href="/integration/test/View/FrontOffice/histoires.php" class="nav-link">Stories</a></li>
                <li><a href="/integration/NovaLinkPeace/views/initiatives.php" class="nav-link">Initiatives</a></li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- UTILISATEUR CONNECTÉ -->
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <!-- CAS ADMIN : Lien vers le BackOffice -->
                        <li>
                            <a href="../BackOffice/backoffice.php" class="nav-link" style="color: var(--rouge-alerte); font-weight: bold;">
                                <i class="fa-solid fa-gauge"></i> Administration
                            </a>
                        </li>
                    <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'organisation'): ?>
                        <!-- CAS ORGANISATION : Liens Gestion des Offres + Candidatures -->
                        <li><a href="/integration/index.php?action=list" class="nav-link" style="color: var(--bleu-pastel); font-weight: bold;">
                            <i class="fa-solid fa-briefcase"></i> Gestion des Offres
                        </a></li>
                        <li><a href="/integration/index.php?action=list_applications" class="nav-link" style="color: var(--violet-admin); font-weight: bold;">
                            <i class="fa-solid fa-users"></i> Candidatures
                        </a></li>
                        <li><a href="profile.php" class="nav-link">Profile</a></li>
                    <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'expert'): ?>
                        <!-- CAS EXPERT : Offres -->
                        <li><a href="/integration/index.php?action=list" class="nav-link" style="color: var(--bleu-pastel); font-weight: bold;">
                            <i class="fa-solid fa-briefcase"></i> Offres
                        </a></li>
                        <li><a href="/integration/NovaLinkPeace/test/View/FrontOffice/profile.php" class="nav-link">Profile</a></li>
                    <?php else: ?>
                        <!-- CAS CLIENT : Lien vers les offres disponibles -->
                        <li><a href="/integration/index.php?action=list" class="nav-link" style="color: var(--bleu-pastel); font-weight: bold;">
                            <i class="fa-solid fa-briefcase"></i> Offres
                        </a></li>
                        <li><a href="/integration/NovaLinkPeace/test/View/FrontOffice/profile.php" class="nav-link">Profile</a></li>
                    <?php endif; ?>

                    <li><a href="/integration/NovaLinkPeace/test/Controller/UtilisateurController.php?action=logout" class="nav-link btn-join-us">Logout</a></li>
                
                <?php else: ?>
                    <!-- VISITEUR NON CONNECTÉ -->
                    <li><a href="/integration/NovaLinkPeace/test/View/FrontOffice/inscription.php" class="nav-link btn-join-us">Join Us</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>