document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. GESTION DES NOTIFICATIONS (TOASTS) ---
    const handleStatusMessages = () => {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');

        if (status) {
            let message = '';
            let type = 'success'; // Vert par défaut

            switch (status) {
                case 'created': message = 'Offre publiée avec succès !'; break;
                case 'updated': message = 'Offre mise à jour.'; break;
                case 'deleted': message = 'Offre supprimée.'; break;
                case 'applied': message = 'Candidature envoyée !'; break;
                case 'app_updated': message = 'Statut mis à jour.'; break;
                
                // CAS 1 : ATS (Mots-clés manquants)
                case 'applied_refused': 
                    message = 'Candidature refusée (Critères clés manquants).'; 
                    type = 'error'; 
                    break;

                // CAS 2 : IA DÉTECTÉE (API Hugging Face)
                case 'detected_ai':
                    message = '⚠️ ALERTE : Votre motivation a été détectée comme générée par une IA. Candidature annulée.';
                    type = 'error';
                    break;
                
                case 'error': message = 'Une erreur est survenue.'; type = 'error'; break;
                default: return;
            }

            // Création de la bulle
            const notification = document.createElement('div');
            notification.className = `status-notification ${type}`;
            notification.innerHTML = type === 'success' 
                ? `<i class="fas fa-check-circle" style="margin-right:10px;"></i> ${message}`
                : `<i class="fas fa-exclamation-triangle" style="margin-right:10px;"></i> ${message}`;
            
            document.body.appendChild(notification);

            // Disparition auto
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => { notification.remove(); }, 500);
            }, 6000); // 6 secondes pour lire les alertes

            // Nettoyage URL
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

            if (name.value.trim().length < 2) { isValid = false; showError(name, 'Nom trop court.'); }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) { isValid = false; showError(email, 'Email invalide.'); }
            if (motiv.value.trim().length < 20) { isValid = false; showError(motiv, 'Motivation trop courte (min 20 car).'); }

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

    // --- 3. GÉNÉRATEUR DESCRIPTION (AJAX) ---
    const btnAi = document.getElementById('btn-generate-ai');
    
    if (btnAi) {
        btnAi.addEventListener('click', function() {
            const titleInput = document.getElementById('title');
            const keywordsInput = document.getElementById('keywords');
            const descArea = document.getElementById('description');

            const title = titleInput.value.trim();
            const keywords = keywordsInput.value.trim();

            if (title.length < 3) {
                alert("Veuillez entrer un titre pour aider l'IA.");
                titleInput.focus();
                return;
            }

            // UI Chargement
            const originalText = btnAi.innerHTML;
            btnAi.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Rédaction...';
            btnAi.disabled = true;

            // Appel AJAX
            fetch(`index.php?action=generate_description&title=${encodeURIComponent(title)}&keywords=${encodeURIComponent(keywords)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        descArea.value = "";
                        let i = 0;
                        const text = data.text;
                        // Effet Machine à écrire
                        function typeWriter() {
                            if (i < text.length) {
                                descArea.value += text.charAt(i);
                                i++;
                                setTimeout(typeWriter, 5);
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
                    alert("Erreur de connexion serveur.");
                    btnAi.innerHTML = originalText;
                    btnAi.disabled = false;
                });
        });
    }
});