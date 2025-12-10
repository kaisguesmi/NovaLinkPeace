document.addEventListener('DOMContentLoaded', function() {

    // 1. VALIDATION DU FORMULAIRE D'INSCRIPTION
    const signupForm = document.getElementById('signup-form');

    if (signupForm) {
        signupForm.addEventListener('submit', function(event) {
            event.preventDefault(); // On bloque l'envoi HTML5
            let isValid = true;

            // --- A. Récupération des champs communs ---
            const role = document.getElementById('role').value;
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('mot_de_passe');
            
            // --- B. Récupération des zones d'erreur ---
            const errorEmail = document.getElementById('error-email');
            const errorPassword = document.getElementById('error-password');
            
            // Reset des messages
            clearErrors();

            // --- C. Validation Spécifique par Rôle ---
            
            // CAS 1 : CLIENT
            if (role === 'client') {
                const nomInput = document.getElementById('nom_complet');
                const errorNom = document.getElementById('error-nom');
                
                if (nomInput.value.trim() === '') {
                    if(errorNom) errorNom.textContent = 'Le nom complet ne peut pas être vide.';
                    isValid = false;
                }
            } 
            
            // CAS 2 : ORGANISATION
            else if (role === 'organisation') {
                const orgaNomInput = document.getElementById('nom_organisation');
                const orgaAdresseInput = document.getElementById('adresse');
                const errorOrgaNom = document.getElementById('error-orga-nom');
                const errorOrgaAdresse = document.getElementById('error-orga-adresse');

                if (orgaNomInput.value.trim() === '') {
                    if(errorOrgaNom) errorOrgaNom.textContent = "Le nom de l'organisation est requis.";
                    isValid = false;
                }
                if (orgaAdresseInput.value.trim() === '') {
                    if(errorOrgaAdresse) errorOrgaAdresse.textContent = "L'adresse est requise.";
                    isValid = false;
                }
            } 
            

            // --- D. Validation des Champs Communs ---
            
            // Email (Regex standard)
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailInput.value)) {
                if(errorEmail) errorEmail.textContent = 'Veuillez entrer une adresse email valide.';
                isValid = false;
            }

            // Mot de passe (Min 8 caractères)
            if (passwordInput.value.length < 8) {
                if(errorPassword) errorPassword.textContent = 'Le mot de passe doit contenir au moins 8 caractères.';
                isValid = false;
            }

            // --- E. Envoi du formulaire si tout est OK ---
            if (isValid) {
                signupForm.submit();
            }
        });
    }

    // 2. VALIDATION DU FORMULAIRE DE CONNEXION (LOGIN)
    const loginForm = document.getElementById('login-form');

    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            event.preventDefault();
            let isValidLogin = true;

            // Récupération des champs (Assure-toi d'avoir ces ID dans login.php)
            const emailLog = document.getElementById('email'); 
            const passLog = document.getElementById('mot_de_passe');
            
            // Zones d'erreur (Peuvent être les mêmes ID que inscription si c'est des pages séparées)
            const errorLoginMsg = document.getElementById('error-login-general'); // Optionnel : un div global pour les erreurs

            // Nettoyage erreur précédente
            if (errorLoginMsg) errorLoginMsg.textContent = '';
            
            // Validation simple : Champs non vides
            if (emailLog.value.trim() === "") {
                alert("Veuillez entrer votre email."); // Ou affiche dans un div d'erreur
                isValidLogin = false;
            } else if (passLog.value.trim() === "") {
                alert("Veuillez entrer votre mot de passe.");
                isValidLogin = false;
            }

            if (isValidLogin) {
                loginForm.submit();
            }
        });
    }

    // --- Fonction utilitaire pour nettoyer tous les messages d'erreur ---
    function clearErrors() {
        const errors = document.querySelectorAll('.error-message');
        errors.forEach(function(el) {
            el.textContent = '';
        });
    }

});