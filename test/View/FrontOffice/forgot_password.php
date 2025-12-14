<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - PeaceLink</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- THEME PEACELINK AMÉLIORÉ --- */
        :root {
            --bleu-pastel: #5dade2;
            --vert-doux: #7bd389;
            --blanc-pur: #ffffff;
            --gris-fonce: #2c3e50;
            --rouge-erreur: #e74c3c;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { 
            background: linear-gradient(135deg, var(--bleu-pastel) 0%, var(--vert-doux) 100%);
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .card { 
            background: var(--blanc-pur); 
            padding: 45px 40px; 
            border-radius: 20px; 
            box-shadow: 0 15px 40px rgba(0,0,0,0.2); 
            width: 100%; 
            max-width: 450px; 
            text-align: center;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .lock-icon {
            font-size: 70px;
            background: linear-gradient(135deg, var(--bleu-pastel), var(--vert-doux));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 15px;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        h2 { 
            color: var(--gris-fonce); 
            margin-bottom: 10px; 
            font-size: 28px;
            font-weight: 700;
        }
        
        p.desc { 
            color: #7f8c8d; 
            font-size: 14px; 
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .input-group { 
            position: relative; 
            margin-bottom: 20px; 
            text-align: left; 
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-group i { 
            position: absolute; 
            left: 15px; 
            top: 50%; 
            transform: translateY(-50%);
            color: #aaa;
            transition: color 0.3s ease;
        }
        
        .input-group input { 
            width: 100%; 
            padding: 14px 15px 14px 45px; 
            border: 2px solid #e0e0e0; 
            border-radius: 10px; 
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .input-group input:focus { 
            border-color: var(--bleu-pastel); 
            outline: none;
            box-shadow: 0 0 0 3px rgba(93, 173, 226, 0.1);
        }

        .input-group input:focus + i {
            color: var(--bleu-pastel);
        }

        /* Style pour le message d'erreur JS */
        .error-text {
            color: var(--rouge-erreur);
            font-size: 12px;
            margin-top: 6px;
            padding-left: 5px;
            display: none;
            font-weight: 500;
        }
        
        .input-error { 
            border-color: var(--rouge-erreur) !important;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        button { 
            width: 100%; 
            padding: 15px; 
            background: linear-gradient(135deg, var(--bleu-pastel), var(--vert-doux)); 
            color: white; 
            border: none; 
            border-radius: 12px; 
            font-weight: bold; 
            font-size: 16px;
            cursor: pointer; 
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(93, 173, 226, 0.3);
        }
        
        button:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 6px 20px rgba(93, 173, 226, 0.4); 
        }

        button:active {
            transform: translateY(0);
        }

        .message-box { 
            background: #d4edda; 
            color: #155724; 
            padding: 15px; 
            border-radius: 10px; 
            font-size: 14px; 
            margin-bottom: 25px; 
            border-left: 4px solid #28a745; 
            text-align: left;
            animation: fadeIn 0.5s ease;
        }
        
        .error-box { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 15px; 
            border-radius: 10px; 
            font-size: 14px; 
            margin-bottom: 25px; 
            border-left: 4px solid #e74c3c;
            text-align: left;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        a.back-link { 
            display: inline-block; 
            margin-top: 25px; 
            color: #7f8c8d; 
            text-decoration: none; 
            font-size: 14px;
            transition: color 0.3s ease;
        }

        a.back-link:hover {
            color: var(--bleu-pastel);
        }

        /* Responsive */
        @media (max-width: 480px) {
            .card { padding: 35px 25px; }
            h2 { font-size: 24px; }
            .lock-icon { font-size: 60px; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="lock-icon">
            <i class="fa-solid fa-lock"></i>
        </div>
        <h2>Mot de passe oublié ?</h2>
        <p class="desc">Pas de problème ! Entrez votre email et nous vous enverrons un lien sécurisé pour réinitialiser votre mot de passe.</p>

        <?php if (isset($_SESSION['info_mail'])): ?>
            <div class="<?php echo strpos($_SESSION['info_mail'], '❌') !== false ? 'error-box' : 'message-box'; ?>">
                <?php echo $_SESSION['info_mail']; unset($_SESSION['info_mail']); ?>
            </div>
        <?php endif; ?>

        <form id="forgot-form" action="../../Controller/UtilisateurController.php" method="POST" novalidate>
            <input type="hidden" name="action" value="forgot_password_request">
            
            <div class="input-group">
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" placeholder="Entrez votre adresse email">
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <div id="err-email" class="error-text"></div>
            </div>
            
            <button type="submit">
                <i class="fa-solid fa-paper-plane"></i> Envoyer le lien de réinitialisation
            </button>
        </form>
        
        <a href="login.php" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Retour à la connexion
        </a>
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