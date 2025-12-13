<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - PeaceLink</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- THEME PEACELINK --- */
        :root {
            --bleu-pastel: #5dade2;
            --vert-doux: #7bd389;
            --blanc-pur: #ffffff;
            --gris-fonce: #2c3e50;
            --rouge-erreur: #e74c3c;
        }

        body { 
            margin: 0; padding: 0;
            background: linear-gradient(135deg, var(--bleu-pastel), var(--vert-doux));
            display: flex; justify-content: center; align-items: center; height: 100vh; 
            font-family: 'Segoe UI', sans-serif; 
        }

        .card { 
            background: var(--blanc-pur); padding: 40px; 
            border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); 
            width: 100%; max-width: 400px; text-align: center;
        }

        h2 { color: var(--gris-fonce); margin-bottom: 10px; }
        p.desc { color: #7f8c8d; font-size: 0.9em; margin-bottom: 25px; }

        .input-group { position: relative; margin-bottom: 20px; text-align: left; }
        .input-group i { position: absolute; left: 15px; top: 14px; color: #aaa; }
        .input-group input { 
            width: 100%; padding: 12px 15px 12px 40px; 
            border: 1px solid #ddd; border-radius: 8px; font-size: 14px; box-sizing: border-box;
        }
        .input-group input:focus { border-color: var(--bleu-pastel); outline: none; }

        /* Style pour le message d'erreur JS */
        .error-text {
            color: var(--rouge-erreur);
            font-size: 12px;
            margin-top: 5px;
            padding-left: 5px;
            display: none; /* Caché par défaut */
        }
        .input-error { border-color: var(--rouge-erreur) !important; }

        button { 
            width: 100%; padding: 12px; 
            background: linear-gradient(to right, var(--bleu-pastel), var(--vert-doux)); 
            color: white; border: none; border-radius: 25px; font-weight: bold; cursor: pointer; 
        }
        button:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(93, 173, 226, 0.4); }

        .message-box { background: #e8f8f5; color: #27ae60; padding: 15px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; border-left: 4px solid #2ecc71; text-align: left;}
        .error-box { background: #fdedec; color: #e74c3c; padding: 15px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; border-left: 4px solid #e74c3c;}
        a.back-link { display: inline-block; margin-top: 20px; color: #7f8c8d; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <div style="font-size: 40px; color: var(--bleu-pastel); margin-bottom: 10px;">
            <i class="fa-solid fa-lock"></i>
        </div>
        <h2>Mot de passe oublié ?</h2>
        <p class="desc">Entrez votre email et nous vous enverrons un lien.</p>

        <?php if (isset($_SESSION['info_mail'])): ?>
            <div class="<?php echo strpos($_SESSION['info_mail'], 'Erreur') !== false ? 'error-box' : 'message-box'; ?>">
                <?php echo $_SESSION['info_mail']; unset($_SESSION['info_mail']); ?>
            </div>
        <?php endif; ?>

        <!-- Ajout de id="forgot-form" et novalidate -->
        <form id="forgot-form" action="../../Controller/UtilisateurController.php" method="POST" novalidate>
            <input type="hidden" name="action" value="forgot_password_request">
            
            <div class="input-group">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" id="email" name="email" placeholder="Entrez votre email">
                <!-- Zone d'erreur -->
                <div id="err-email" class="error-text"></div>
            </div>
            
            <button type="submit">Envoyer le lien</button>
        </form>
        
        <a href="login.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Retour à la connexion</a>
    </div>

    <!-- SCRIPT DE VALIDATION -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('forgot-form');
            const emailInput = document.getElementById('email');
            const errorEmail = document.getElementById('err-email');

            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Reset styles
                emailInput.classList.remove('input-error');
                errorEmail.style.display = 'none';
                errorEmail.innerText = '';

                // Validation Email Regex
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (emailInput.value.trim() === '') {
                    showError(emailInput, errorEmail, "L'adresse email est requise.");
                    isValid = false;
                } else if (!emailRegex.test(emailInput.value)) {
                    showError(emailInput, errorEmail, "Veuillez entrer une adresse email valide.");
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault(); // Bloque l'envoi si erreur
                }
            });

            function showError(input, errorDiv, message) {
                input.classList.add('input-error');
                errorDiv.innerText = message;
                errorDiv.style.display = 'block';
            }
        });
    </script>
</body>
</html>