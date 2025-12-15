<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sessionRole     = $_SESSION['role'] ?? 'client';
$sessionUserId   = $_SESSION['user_id'] ?? null;
$sessionUsername = $_SESSION['username'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeaceLink - Initiatives Locales</title>
    <!-- Charge le CSS global via un chemin absolu pour éviter les soucis de résolution -->
    <link rel="stylesheet" href="/integration/NovaLinkPeace/style.css?v=3">
    <link rel="stylesheet" href="/integration/NovaLinkPeace/initiatives.css?v=3">
    <script>
        window.PEACELINK_SESSION = {
            role: <?php echo json_encode($sessionRole); ?>,
            userId: <?php echo json_encode($sessionUserId); ?>,
            username: <?php echo json_encode($sessionUsername); ?>
        };
    </script>
    <script src="../initiatives.js" defer></script>
</head>

<body>

    <!-- NAVBAR -->
    <header class="main-navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <a href="/integration/NovaLinkPeace/test/View/FrontOffice/index.php">
                    <img src="/integration/NovaLinkPeace/mon-logo.png" alt="Logo PeaceLink" class="logo-img">
                    <span class="site-name">PeaceLink</span>
                </a>
            </div>
            <nav class="navbar-links">
                <ul>
                    <li><a href="/integration/NovaLinkPeace/test/View/FrontOffice/index.php" class="nav-link">Home</a></li>
                    <li><a href="#" class="nav-link">Stories</a></li>
                    <li><a href="/integration/NovaLinkPeace/views/initiatives.php" class="nav-link active">Initiatives</a></li>
                </ul>
            </nav>
        </div>
    </header>


    <!-- CONTENU PRINCIPAL -->
    <main class="initiatives-main">

        <!-- LISTE DES INITIATIVES -->
        <section class="mission-section">
            <div class="mission-container">

                <div class="page-header">
                    <h2>Initiatives locales</h2>
                    <p class="page-subtitle">
                        Découvrez et participez à des actions solidaires près de chez vous.
                    </p>
                    <?php if (in_array($sessionRole, ['organisation', 'expert'], true)) : ?>
                        <button id="btn-open-create" class="btn-primary">+ Créer une initiative</button>
                    <?php endif; ?>
                </div>

                <!-- Filtres -->
                <div class="filters-box">
                    <input type="text" id="filter-location" placeholder="Filtrer par ville...">
                    <select id="filter-category">
                        <option value="">Toutes catégories</option>
                        <option value="Écologie">Écologie</option>
                        <option value="Solidarité">Solidarité</option>
                        <option value="Éducation">Éducation</option>
                        <option value="Citoyenneté">Citoyenneté</option>
                    </select>
                    <input type="date" id="filter-date">
                    <button id="btn-apply-filters" class="btn-secondary">Filtrer</button>
                </div>

                <div id="events-list" class="event-grid"></div>
            </div>
        </section>


        <!-- DÉTAIL D’INITIATIVE -->
        <section id="event-detail-section" class="mission-section hidden">
            <div class="mission-container">

                <button id="btn-back-to-list" class="btn-secondary back-btn">← Retour à la liste</button>

                <article id="event-detail-card" class="event-detail-card"></article>

                <?php if ($sessionUserId && $sessionRole === 'client') : ?>
                    <div class="participation-form-wrapper">
                        <h3>Participer à cette initiative</h3>

                        <form id="participation-form" class="event-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="participant-message">Message (optionnel)</label>
                                    <textarea id="participant-message" rows="3"></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn-success">Participer avec mon compte</button>
                            <p id="participation-feedback" class="form-feedback"></p>
                        </form>
                    </div>
                <?php else : ?>
                    <div class="participation-form-wrapper">
                        <h3>Participer à cette initiative</h3>
                        <p id="participation-feedback" class="form-feedback">Connectez-vous en tant que client pour participer.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>


        <?php if (in_array($sessionRole, ['organisation', 'expert'], true)) : ?>
            <!-- FORMULAIRE CRÉATION INITIATIVE -->
            <section id="event-create-section" class="mission-section hidden">
                <div class="mission-container">

                    <button id="btn-close-create" class="btn-secondary back-btn">← Retour aux initiatives</button>

                    <h2>Créer une initiative</h2>

                    <form id="create-event-form" class="event-form">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="event-title">Titre</label>
                                <input id="event-title" type="text">
                            </div>

                            <div class="form-group">
                                <label for="event-category">Catégorie</label>
                                <select id="event-category">
                                    <option value="">-- Choisir --</option>
                                    <option value="Écologie">Écologie</option>
                                    <option value="Solidarité">Solidarité</option>
                                    <option value="Éducation">Éducation</option>
                                    <option value="Citoyenneté">Citoyenneté</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="event-location">Lieu</label>
                                <input id="event-location" type="text">
                            </div>

                            <div class="form-group">
                                <label for="event-date">Date</label>
                                <input id="event-date" type="date">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="event-capacity">Nombre de places</label>
                                <input id="event-capacity" type="number" min="1" value="20">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="event-description">Description</label>
                                <textarea id="event-description" rows="4"></textarea>
                            </div>
                        </div>

                        <button class="btn-primary" type="submit">Enregistrer</button>
                        <p id="create-event-feedback" class="form-feedback"></p>
                    </form>
                </div>
            </section>
        <?php endif; ?>


        <?php if ($sessionRole === 'admin') : ?>
            <section id="admin-section" class="mission-section hidden">
                <div class="mission-container">
                    <h2>Validation des initiatives</h2>
                    <div id="admin-events-list" class="event-grid"></div>
                </div>
            </section>
        <?php endif; ?>

    </main>


    <!-- FOOTER -->
    <footer class="main-footer">
        <div class="footer-container"></div>
        <div class="footer-bottom">
            <p>© 2025 PeaceLink. Tous droits réservés.</p>
        </div>
    </footer>
<!-- ====== CHATBOT IA PEACELINK ====== -->
  <div id="chatbot-button">💬</div>

   <div id="chatbot-box" class="hidden">
    <div id="chatbot-header">
        <span>PeaceLink Assistant</span>
        <button id="chatbot-close">✖</button>
    </div>

    <div id="chatbot-messages"></div>

    <div id="chatbot-input-area">
        <input type="text" id="chatbot-input" placeholder="Écrivez votre message...">
        <button id="chatbot-send">➤</button>
    </div>
  </div>
</body>
</html>
