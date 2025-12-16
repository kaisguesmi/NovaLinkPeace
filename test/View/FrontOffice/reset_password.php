<?php 
session_start(); 
$token = $_GET['token'] ?? '';
if (empty($token)) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe - PeaceLink</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- THEME PEACELINK AMÉLIORÉ --- */
        :root {
            --bleu-pastel: #5dade2;
            --vert-doux: #7bd389;
            --blanc-pur: #ffffff;
            --gris-fonce: #2c3e50;
            --rouge-erreur: #e74c3c;
            --vert-succes: #27ae60;
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

        .subtitle {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .input-group { 
            position: relative; 
            margin-bottom: 20px; 
            text-align: left; 
        }
        
        .input-group label { 
            display: block; 
            font-size: 13px; 
            color: #555; 
            margin-bottom: 8px; 
            font-weight: 600;
            padding-left: 5px;
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
            border-color: var(--vert-doux); 
            outline: none;
            box-shadow: 0 0 0 3px rgba(123, 211, 137, 0.1);
        }

        .input-group input:focus + i {
            color: var(--vert-doux);
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

        /* Indicateur de force du mot de passe */
        .password-strength {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
        }

        .strength-weak { background: #e74c3c; width: 33%; }
        .strength-medium { background: #f39c12; width: 66%; }
        .strength-strong { background: #27ae60; width: 100%; }

        button { 
            width: 100%; 
            padding: 15px; 
            margin-top: 10px;
            background: linear-gradient(135deg, var(--vert-succes), #2ecc71);
            color: white; 
            border: none; 
            border-radius: 12px; 
            font-weight: bold; 
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
        }
        
        button:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4); 
        }

        button:active {
            transform: translateY(0);
        }

        .error-msg { 
            background: #fee; 
            color: #e74c3c; 
            padding: 12px 15px; 
            border-radius: 8px; 
            font-size: 14px; 
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
            text-align: left;
        }

        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
            text-align: left;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #7f8c8d;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .back-link:hover {
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
            <i class="fa-solid fa-key"></i>
        </div>
        <h2>Nouveau mot de passe</h2>
        <p class="subtitle">Choisissez un mot de passe sécurisé pour protéger votre compte</p>
        
        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="error-msg">
                <i class="fa-solid fa-circle-exclamation"></i> 
                <?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="success-msg">
                <i class="fa-solid fa-circle-check"></i> 
                <?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
            </div>
        <?php endif; ?>

        <form id="reset-form" action="../../Controller/UtilisateurController.php" method="POST" novalidate>
            <input type="hidden" name="action" value="reset_password_submit">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <div class="input-group">
                <label><i class="fa-solid fa-shield-halved"></i> Nouveau mot de passe</label>
                <div class="input-wrapper">
                    <input type="password" id="new_password" name="new_password" placeholder="Minimum 6 caractères">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div class="password-strength">
                    <div class="strength-bar" id="strength-bar"></div>
                </div>
                <div id="err-new" class="error-text"></div>
            </div>

            <div class="input-group">
                <label><i class="fa-solid fa-check-double"></i> Confirmez le mot de passe</label>
                <div class="input-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Répétez le mot de passe">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div id="err-confirm" class="error-text"></div>
            </div>

            <button type="submit">
                <i class="fa-solid fa-check"></i> Changer le mot de passe
            </button>
        </form>

        <a href="login.php" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Retour à la connexion
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('reset-form');
            const pass1 = document.getElementById('new_password');
            const pass2 = document.getElementById('confirm_password');
            const err1 = document.getElementById('err-new');
            const err2 = document.getElementById('err-confirm');
            const strengthBar = document.getElementById('strength-bar');

            // Indicateur de force du mot de passe
            pass1.addEventListener('input', function() {
                const password = pass1.value;
                const length = password.length;
                
                strengthBar.className = 'strength-bar';
                
                if (length === 0) {
                    strengthBar.style.width = '0%';
                } else if (length < 6) {
                    strengthBar.classList.add('strength-weak');
                } else if (length < 10) {
                    strengthBar.classList.add('strength-medium');
                } else {
                    strengthBar.classList.add('strength-strong');
                }
            });

            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Reset
                [pass1, pass2].forEach(el => el.classList.remove('input-error'));
                [err1, err2].forEach(el => { el.style.display = 'none'; el.innerText = ''; });

                // Validation Pass 1
                if (pass1.value.length < 6) {
                    showError(pass1, err1, "Le mot de passe doit contenir au moins 6 caractères.");
                    isValid = false;
                }

                // Validation Pass 2
                if (pass2.value === '') {
                    showError(pass2, err2, "Veuillez confirmer le mot de passe.");
                    isValid = false;
                } else if (pass1.value !== pass2.value) {
                    showError(pass2, err2, "Les mots de passe ne correspondent pas.");
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
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