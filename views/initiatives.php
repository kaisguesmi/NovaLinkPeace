<?php
// On démarre la session si elle n'est pas déjà active
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
    
    <!-- FontAwesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- 1. CSS GLOBAL (Pour que la Nav Bar soit jolie) -->
    <link rel="stylesheet" href="/integration/NovaLinkPeace/test/View/FrontOffice/style.css">

    <!-- Variables JS pour le script des initiatives -->
    <script>
        window.PEACELINK_SESSION = {
            role: <?php echo json_encode($sessionRole); ?>,
            userId: <?php echo json_encode($sessionUserId); ?>,
            username: <?php echo json_encode($sessionUsername); ?>
        };
    </script>
    <script src="../initiatives.js" defer></script>

    <style>
        /* --- CSS SPÉCIFIQUE AUX INITIATIVES --- */
        
        /* Reset pour éviter les conflits avec le style global */
        .initiatives-main {
            /* On laisse de la place pour la navbar fixe si nécessaire */
            /* Si ta navbar est fixed, ajoute : margin-top: 80px; */
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
            min-height: 80vh;
        }

        .mission-container { width: 100%; }
        .hidden { display: none !important; }

        /* --- EN-TÊTE DE PAGE --- */
        .page-header {
            text-align: center;
            margin-bottom: 50px;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .page-header h2 {
            font-size: 2.5rem;
            color: #2c3e50; /* Gris foncé */
            margin-bottom: 10px;
            font-family: 'Segoe UI', sans-serif;
        }

        .page-subtitle {
            color: #7f8c8d;
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto 25px auto;
            line-height: 1.6;
        }

        /* --- FILTRES --- */
        .filters-box {
            background: #ffffff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 40px;
            border: 1px solid #eee;
        }

        .filters-box input, .filters-box select {
            flex: 1;
            padding: 12px 20px;
            border: 1px solid #e0e0e0;
            border-radius: 50px;
            font-size: 14px;
            transition: 0.3s;
            min-width: 200px;
            outline: none;
        }

        .filters-box input:focus, .filters-box select:focus {
            border-color: #5dade2;
            box-shadow: 0 0 0 4px rgba(93, 173, 226, 0.1);
        }

        /* --- BOUTONS --- */
        .btn-primary, .btn-secondary, .btn-success {
            padding: 12px 25px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        /* Vert PeaceLink */
        .btn-primary {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
        }

        /* Bleu PeaceLink */
        .btn-secondary {
            background: linear-gradient(135deg, #5dade2, #3498db);
            color: white;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-success {
            background: #27ae60;
            color: white;
            width: 100%;
            margin-top: 15px;
        }

        button:hover { transform: translateY(-2px); opacity: 0.95; }

        .back-btn { margin-bottom: 20px; background: #95a5a6; color: white; }

        /* --- GRILLE DES CARTES --- */
        .event-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }

        /* Style des cartes générées par JS */
         .event-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        /* 1. Style pour le lien "VOIR LES DÉTAILS" (C'est souvent un <a>) */
        .event-card a {
            display: block;
            width: 100%;
            padding: 12px 0;
            margin-top: 10px;
            text-align: center;
            background-color: #5dade2; /* Bleu clair */
            color: white !important;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            box-shadow: 0 4px 6px rgba(93, 173, 226, 0.3);
            transition: 0.3s;
        }
        .event-card a:hover { background-color: #3498db; transform: translateY(-2px); }

        /* 2. Style de BASE pour tous les boutons (Modifier / Supprimer / Participer) */
        .event-card button {
            width: 100%;
            padding: 12px 0;
            margin-top: 8px;
            border: none !important; /* Force la suppression de la bordure noire */
            border-radius: 50px;
            color: white !important;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: 0.3s;
            text-transform: uppercase;
        }
        .event-card button:hover { transform: translateY(-2px); filter: brightness(1.1); }

        /* 3. COULEURS SPÉCIFIQUES (Logique Admin) */

        /* Le 1er bouton trouvé (C'est souvent "MODIFIER" pour l'admin) */
        .event-card button:nth-of-type(1) {
            background-color: #3498db !important; /* Bleu foncé */
        }

        /* Le 2ème bouton trouvé (C'est souvent "SUPPRIMER" pour l'admin) */
        .event-card button:nth-of-type(2) {
            background-color: #e74c3c !important; /* Rouge Vif */
        }

        /* Cas Client : Si le bouton est "Participer" (souvent vert) */
        /* On essaie de cibler le bouton unique s'il n'y en a qu'un */
        .event-card button:only-of-type {
            background-color: #2ecc71 !important; /* Vert */
        }

        /* --- DÉTAILS & FORMULAIRES --- */
        .event-detail-card, .event-form, .participation-form-wrapper {
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-group { flex: 1; display: flex; flex-direction: column; }
        
        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #34495e;
        }

        .form-group input, .form-group textarea, .form-group select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            background: #fdfdfd;
        }
        
        .form-group input:focus, .form-group textarea:focus {
            border-color: #2ecc71;
            outline: none;
        }

        /* --- CHATBOT --- */
        #chatbot-button {
            position: fixed; bottom: 30px; right: 30px;
            width: 60px; height: 60px;
            background: linear-gradient(135deg, #5dade2, #2ecc71);
            color: white; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; cursor: pointer;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            transition: transform 0.3s; z-index: 9999;
        }
        #chatbot-button:hover { transform: scale(1.1); }

        #chatbot-box {
            position: fixed; bottom: 100px; right: 30px;
            width: 350px; height: 450px;
            background: white; border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            display: flex; flex-direction: column;
            overflow: hidden; z-index: 9999;
            border: 1px solid #eee;
        }

        #chatbot-header {
            background: linear-gradient(135deg, #5dade2, #2ecc71);
            color: white; padding: 15px; font-weight: bold;
            display: flex; justify-content: space-between; align-items: center;
        }

        #chatbot-messages { flex: 1; padding: 15px; overflow-y: auto; background: #f9f9f9; font-size: 14px; }
        #chatbot-input-area { padding: 10px; border-top: 1px solid #eee; display: flex; gap: 10px; background: white; }
        
        #chatbot-input {
            flex: 1; padding: 10px; border: 1px solid #ddd;
            border-radius: 20px; outline: none;
        }
        
        #chatbot-send {
            background: #5dade2; color: white; border: none;
            padding: 0 15px; border-radius: 50%; cursor: pointer;
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 768px) {
            .form-row { flex-direction: column; gap: 15px; }
            .filters-box { flex-direction: column; align-items: stretch; }
            .page-header h2 { font-size: 2rem; }
            #chatbot-box { width: 90%; right: 5%; bottom: 90px; }
        }
    </style>
</head>

<body>

    <!-- 1. INCLUSION DE LA NAVBAR -->
    <!-- On remonte d'un dossier (..) pour sortir de 'views' -->
    <!-- Puis on entre dans 'test/View/FrontOffice/partials' -->
    <?php include __DIR__ . '/../test/View/FrontOffice/partials/header.php'; ?>


    <!-- 2. CONTENU PRINCIPAL -->
    <main class="initiatives-main">

        <!-- LISTE DES INITIATIVES -->
        <section class="mission-section">
            <div class="mission-container">

                <div class="page-header">
                    <h2>Initiatives Locales</h2>
                    <p class="page-subtitle">
                        Découvrez et participez à des actions solidaires près de chez vous.
                        Ensemble, construisons un avenir meilleur.
                    </p>
                    
                    <?php if (in_array($sessionRole, ['organisation', 'expert'], true)) : ?>
                        <button id="btn-open-create" class="btn-primary">
                            <i class="fa-solid fa-plus-circle"></i> Créer une initiative
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Filtres -->
                <div class="filters-box">
                    <input type="text" id="filter-location" placeholder="📍 Ville...">
                    <select id="filter-category">
                        <option value="">📂 Toutes catégories</option>
                        <option value="Écologie">Écologie</option>
                        <option value="Solidarité">Solidarité</option>
                        <option value="Éducation">Éducation</option>
                        <option value="Citoyenneté">Citoyenneté</option>
                    </select>
                    <input type="date" id="filter-date">
                    <button id="btn-apply-filters" class="btn-secondary">
                        <i class="fa-solid fa-filter"></i> Filtrer
                    </button>
                </div>

                <div id="events-list" class="event-grid">
                    <!-- Les cartes seront générées ici par JS -->
                </div>
            </div>
        </section>


        <!-- DÉTAIL D’INITIATIVE -->
        <section id="event-detail-section" class="mission-section hidden">
            <div class="mission-container">

                <button id="btn-back-to-list" class="btn-secondary back-btn">
                    <i class="fa-solid fa-arrow-left"></i> Retour
                </button>

                <article id="event-detail-card" class="event-detail-card">
                    <!-- Détails injectés par JS -->
                </article>

                <?php if ($sessionUserId && $sessionRole === 'client') : ?>
                    <div class="participation-form-wrapper">
                        <h3><i class="fa-solid fa-hand-holding-heart"></i> Je participe !</h3>
                        <form id="participation-form" class="event-form" style="box-shadow:none; padding:0;">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="participant-message">Message pour l'organisateur (optionnel)</label>
                                    <textarea id="participant-message" rows="3" placeholder="Ex: Je viendrai avec du matériel..."></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn-success">Confirmer ma participation</button>
                            <p id="participation-feedback" class="form-feedback"></p>
                        </form>
                    </div>
                <?php else : ?>
                    <!-- Message optionnel si non connecté -->
                <?php endif; ?>
            </div>
        </section>


        <?php if (in_array($sessionRole, ['organisation', 'expert'], true)) : ?>
            <!-- FORMULAIRE CRÉATION INITIATIVE -->
            <section id="event-create-section" class="mission-section hidden">
                <div class="mission-container">

                    <button id="btn-close-create" class="btn-secondary back-btn">
                        <i class="fa-solid fa-times"></i> Annuler
                    </button>

                    <div class="page-header" style="padding:20px; margin-bottom:20px;">
                        <h2>Nouvelle Initiative</h2>
                    </div>

                    <form id="create-event-form" class="event-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="event-title">Titre de l'événement</label>
                                <input id="event-title" type="text" placeholder="Ex: Nettoyage de plage" required>
                            </div>
                            <div class="form-group">
                                <label for="event-category">Catégorie</label>
                                <select id="event-category" required>
                                    <option value="">-- Sélectionner --</option>
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
                                <input id="event-location" type="text" placeholder="Ex: Paris 12ème" required>
                            </div>
                            <div class="form-group">
                                <label for="event-date">Date</label>
                                <input id="event-date" type="date" required>
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
                                <label for="event-description">Description détaillée</label>
                                <textarea id="event-description" rows="5" placeholder="Décrivez l'objectif, le déroulement..." required></textarea>
                            </div>
                        </div>

                        <button class="btn-primary" type="submit" style="width:100%">Publier l'initiative</button>
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

    <!-- 3. FOOTER -->
    <footer class="main-footer">
        <div class="footer-bottom" style="text-align:center; padding:30px; color:#777; background:#fff; margin-top:50px; border-top:1px solid #eee;">
            <p>© 2025 PeaceLink. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- ====== CHATBOT IA PEACELINK ====== -->
    <div id="chatbot-button"><i class="fa-solid fa-comments"></i></div>

    <div id="chatbot-box" class="hidden">
        <div id="chatbot-header">
            <span><i class="fa-solid fa-robot"></i> Assistant PeaceLink</span>
            <button id="chatbot-close" style="background:none; border:none; color:white; cursor:pointer;">✖</button>
        </div>

        <div id="chatbot-messages">
            <div style="padding:10px; background:#f1f1f1; border-radius:10px; margin:5px;">Bonjour ! Je suis l'assistant. Comment puis-je vous aider ?</div>
        </div>

        <div id="chatbot-input-area">
            <input type="text" id="chatbot-input" placeholder="Écrivez votre message...">
            <button id="chatbot-send"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </div>

</body>
</html>