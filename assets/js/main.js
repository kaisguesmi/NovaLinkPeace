document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. GESTION DES NOTIFICATIONS (TOASTS) ---
    const handleStatusMessages = () => {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');

        if (status) {
            let message = '';
            let type = 'success';

            switch (status) {
                case 'created': message = 'Offre publiée avec succès !'; break;
                case 'updated': message = 'Offre mise à jour avec succès !'; break;
                case 'deleted': message = 'Offre supprimée.'; break;
                case 'applied': message = 'Votre candidature a été envoyée !'; break;
                
                // CAS ATS : Refus automatique
                case 'applied_refused': 
                    message = 'Candidature envoyée, mais refusée automatiquement (critères manquants).'; 
                    type = 'error'; 
                    break;
                
                case 'app_updated': message = 'Statut mis à jour.'; break;
                case 'error': message = 'Une erreur est survenue.'; type = 'error'; break;
                default: return;
            }

            const notification = document.createElement('div');
            notification.className = `status-notification ${type}`;
            notification.innerHTML = type === 'success' 
                ? `<i class="fas fa-check-circle" style="margin-right:10px;"></i> ${message}`
                : `<i class="fas fa-exclamation-circle" style="margin-right:10px;"></i> ${message}`;
            
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => { notification.remove(); }, 500);
            }, 5000);

            const role = urlParams.get('role');
            const newUrl = window.location.pathname + (role ? `?role=${role}` : '');
            history.replaceState({}, '', newUrl);
        }
    };
    handleStatusMessages();

    // --- 2. VALIDATION FORMULAIRES ---
    function showError(input, message) {
        const div = input.parentElement.querySelector('.error-message');
        input.classList.add('input-error');
        if(div) { div.textContent = message; div.classList.add('visible'); }
    }
    function clearError(input) {
        const div = input.parentElement.querySelector('.error-message');
        input.classList.remove('input-error');
        if(div) { div.classList.remove('visible'); }
    }

    const appForm = document.getElementById('application-form');
    if (appForm) {
        appForm.addEventListener('submit', function(e) {
            let isValid = true;
            const name = document.getElementById('candidate_name');
            const email = document.getElementById('candidate_email');
            const motiv = document.getElementById('motivation');
            [name, email, motiv].forEach(clearError);
            if (name.value.trim().length < 2) { isValid = false; showError(name, 'Nom invalide.'); }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) { isValid = false; showError(email, 'Email invalide.'); }
            if (motiv.value.trim().length < 20) { isValid = false; showError(motiv, 'Motivation trop courte.'); }
            if (!isValid) e.preventDefault();
        });
    }

    const offerForm = document.getElementById('offer-form');
    if (offerForm) {
        offerForm.addEventListener('submit', function(e) {
            let isValid = true;
            const title = document.getElementById('title');
            const desc = document.getElementById('description');
            [title, desc].forEach(clearError);
            if (title.value.trim().length < 5) { isValid = false; showError(title, 'Titre trop court.'); }
            if (desc.value.trim().length < 20) { isValid = false; showError(desc, 'Description trop courte.'); }
            if (!isValid) e.preventDefault();
        });
    }

    // --- 3. GÉNÉRATEUR DE DESCRIPTION IA (AJAX) ---
    const btnAi = document.getElementById('btn-generate-ai');
    
    if (btnAi) {
        btnAi.addEventListener('click', function() {
            const titleInput = document.getElementById('title');
            const keywordsInput = document.getElementById('keywords');
            const descArea = document.getElementById('description');

            const title = titleInput.value.trim();
            const keywords = keywordsInput.value.trim();

            if (title.length < 3) {
                alert("Veuillez d'abord entrer un Titre de mission pour aider l'IA.");
                titleInput.focus();
                return;
            }

            // Animation bouton
            const originalText = btnAi.innerHTML;
            btnAi.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Rédaction...';
            btnAi.disabled = true;

            // Appel AJAX
            fetch(`index.php?action=generate_description&title=${encodeURIComponent(title)}&keywords=${encodeURIComponent(keywords)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Effet machine à écrire
                        descArea.value = "";
                        let i = 0;
                        const text = data.text;
                        function typeWriter() {
                            if (i < text.length) {
                                descArea.value += text.charAt(i);
                                i++;
                                setTimeout(typeWriter, 5); // Vitesse
                            } else {
                                btnAi.innerHTML = originalText;
                                btnAi.disabled = false;
                            }
                        }
                        typeWriter();
                    } else {
                        alert("Erreur : " + data.message);
                        btnAi.innerHTML = originalText;
                        btnAi.disabled = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Erreur lors de la génération.");
                    btnAi.innerHTML = originalText;
                    btnAi.disabled = false;
                });
        });
    }
});