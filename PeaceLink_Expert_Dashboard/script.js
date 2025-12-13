 document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const menuToggle = document.querySelector('.menu-toggle');
    const navItems = document.querySelectorAll('.sidebar-nav .nav-item'); // Cibler spécifiquement les éléments de nav
    const pageTitle = document.querySelector('.page-title');
    const contentContainer = document.getElementById('content-container');

    // --- Sidebar Toggle ---
    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');

        // Correction pour le mode mobile:
        // Si la sidebar est fermée (collapsed), le main content prend toute la largeur
        // Si la sidebar est ouverte (pas collapsed), le main content ne doit pas avoir de marge
        if (window.innerWidth <= 992) {
            if (sidebar.classList.contains('collapsed')) {
                mainContent.classList.remove('collapsed'); // Main content reprend toute la largeur
            } else {
                // Si la sidebar s'ouvre sur mobile, le main content n'est pas "collapsed" mais devrait être à margin-left: 0
                // Le CSS gère déjà cela avec "margin-left: 0" par défaut sur mobile
            }
        }
    });

    // --- Navigation entre les pages ---
    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();

            // Retirer la classe 'active' de l'élément de navigation précédent
            navItems.forEach(nav => nav.classList.remove('active'));
            // Ajouter la classe 'active' à l'élément de navigation cliqué
            item.classList.add('active');

            // Cacher toutes les pages
            document.querySelectorAll('.page-content').forEach(page => {
                page.classList.remove('active');
            });

            // Afficher la page correspondante
            const targetPageId = item.dataset.page + '-page';
            const targetPage = document.getElementById(targetPageId);
            if (targetPage) {
                targetPage.classList.add('active');
                // Mettre à jour le titre de la topbar
                pageTitle.textContent = item.querySelector('span').textContent;
            }

            // Sur les petits écrans, cacher la sidebar après navigation
            if (window.innerWidth <= 992) {
                sidebar.classList.add('collapsed');
                mainContent.classList.remove('collapsed'); // Le main content reprend toute la largeur
            }
        });
    });

    // --- Initialisation de la page Dashboard par défaut ---
    // S'assurer que le dashboard est actif et que le titre est correct au chargement
    const defaultNavItem = document.querySelector('.sidebar-nav .nav-item.active');
    if (defaultNavItem) {
        const defaultPageId = defaultNavItem.dataset.page + '-page';
        const defaultPage = document.getElementById(defaultPageId);
        if (defaultPage) {
            defaultPage.classList.add('active');
            pageTitle.textContent = defaultNavItem.querySelector('span').textContent;
        }
    }


    // --- Logique pour les modales (pop-ups) ---
    // La correction ici est d'ajouter un `display: flex;` pour centrer la modale
    const modals = document.querySelectorAll('.modal');
    const closeButtons = document.querySelectorAll('.modal .close-button'); // Cibler les boutons de fermeture dans les modales
    const modalTriggerButtons = document.querySelectorAll('[data-modal]');

    modalTriggerButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const modalId = btn.dataset.modal;
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex'; // Utilise flex pour centrer
            }
        });
    });

    closeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.modal').style.display = 'none';
        });
    });

    // Fermer la modale si on clique en dehors de son contenu
    window.addEventListener('click', (event) => {
        modals.forEach(modal => {
            if (event.target === modal) { // Vérifie si l'élément cliqué est la modale elle-même (l'arrière-plan sombre)
                modal.style.display = 'none';
            }
        });
    });

    // Gestion de la soumission du formulaire de création d'initiative
    const createInitiativeForm = document.getElementById('create-initiative-form');
    if (createInitiativeForm) {
        createInitiativeForm.addEventListener('submit', (e) => {
            e.preventDefault();
            // Ici, vous enverriez les données à votre backend
            alert('Initiative soumise pour validation ! (Simulation)');
            createInitiativeForm.reset(); // Réinitialiser le formulaire
            document.getElementById('create-initiative-modal').style.display = 'none'; // Fermer la modale
            // Optionnel: rafraîchir la liste des initiatives ou afficher un message de succès
        });
    }


    // --- Graphiques Chart.js (Dashboard) ---
    // S'assurer que les éléments canvas existent avant de tenter d'initialiser les graphes
    if (document.getElementById('participationChart')) {
        const participationCtx = document.getElementById('participationChart').getContext('2d');
        new Chart(participationCtx, {
            type: 'bar',
            data: {
                labels: ['Atelier', 'Campagne', 'Débat', 'Webinar'],
                datasets: [{
                    label: 'Nombre de Participants',
                    data: [12, 25, 8, 15],
                    backgroundColor: [
                        'rgba(93, 173, 226, 0.7)', // bleu-pastel
                        'rgba(123, 211, 137, 0.7)', // vert-doux
                        'rgba(244, 162, 97, 0.7)', // orange-chaud
                        'rgba(27, 38, 59, 0.7)' // bleu-nuit
                    ],
                    borderColor: [
                        'rgba(93, 173, 226, 1)',
                        'rgba(123, 211, 137, 1)',
                        'rgba(244, 162, 97, 1)',
                        'rgba(27, 38, 59, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    if (document.getElementById('initiativeTypesChart')) {
        const initiativeTypesCtx = document.getElementById('initiativeTypesChart').getContext('2d');
        new Chart(initiativeTypesCtx, {
            type: 'doughnut',
            data: {
                labels: ['Paix & Dialogue', 'Environnement', 'Inclusion', 'Éducation'],
                datasets: [{
                    label: 'Types d\'initiatives',
                    data: [3, 2, 1, 1], // Exemple de données
                    backgroundColor: [
                        'rgba(93, 173, 226, 0.8)',
                        'rgba(123, 211, 137, 0.8)',
                        'rgba(244, 162, 97, 0.8)',
                        'rgba(27, 38, 59, 0.8)'
                    ],
                    borderColor: 'rgba(255, 255, 255, 1)', // Utilise une couleur directe ici
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }


    // --- Logique pour l'upload d'avatar (Page Profil) ---
    const avatarPreview = document.getElementById('avatar-preview');
    const avatarUpload = document.getElementById('avatar-upload');

    if (avatarPreview && avatarUpload) {
        avatarPreview.addEventListener('click', () => {
            avatarUpload.click(); // Déclenche le clic sur l'input file
        });

        avatarUpload.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    let img = avatarPreview.querySelector('img');
                    if (img) {
                        img.src = e.target.result;
                    } else {
                        // Si pas d'image, créer une nouvelle balise img
                        img = document.createElement('img');
                        img.src = e.target.result;
                        avatarPreview.innerHTML = ''; // Nettoyer l'icône/texte précédent
                        avatarPreview.appendChild(img);
                    }
                    // Cacher l'overlay après le chargement
                    const overlay = avatarPreview.querySelector('.profile-avatar-overlay');
                    if (overlay) overlay.style.opacity = '0';
                };
                reader.readAsDataURL(file);
            }
        });
    }


    // --- Simuler la suppression/rejet d'histoire ---
    contentContainer.addEventListener('click', (e) => {
        // Cibler l'élément le plus proche qui est un bouton dans story-actions
        const targetButton = e.target.closest('.story-actions .btn');
        if (targetButton) {
            const storyCard = targetButton.closest('.story-card');
            if (storyCard) {
                let actionVerb = '';
                if (targetButton.classList.contains('btn-danger')) {
                    actionVerb = 'rejeter/supprimer';
                } else if (targetButton.classList.contains('btn-success')) {
                    actionVerb = 'approuver';
                } else if (targetButton.classList.contains('btn-secondary')) {
                    actionVerb = 'voir les détails de';
                }

                if (actionVerb) {
                    if (confirm(`Voulez-vous vraiment ${actionVerb} cette histoire ?`)) {
                        if (actionVerb === 'rejeter/supprimer' || actionVerb === 'approuver') {
                            storyCard.classList.add('is-hidden');
                            // Retirer la carte du DOM après l'animation
                            storyCard.addEventListener('transitionend', () => {
                                storyCard.remove();
                            });
                            alert(`Histoire ${actionVerb} avec succès !`);
                        } else {
                            alert(`Affichage des détails pour l'histoire ID: ${storyCard.dataset.storyId} (Simulation)`);
                            // Ici, vous pourriez ouvrir une autre modale avec les détails de l'histoire
                        }
                    }
                }
            }
        }
    });

});