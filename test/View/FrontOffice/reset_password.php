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
    <title>Réinitialisation - PeaceLink</title>
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

        h2 { color: var(--gris-fonce); margin-bottom: 25px; }

        .input-group { position: relative; margin-bottom: 15px; text-align: left; }
        .input-group label { display:block; font-size:12px; color:#777; margin-bottom:5px; font-weight:600;}
        .input-group i { position: absolute; left: 15px; top: 42px; color: #aaa; }
        .input-group input { 
            width: 100%; padding: 12px 15px 12px 40px; 
            border: 1px solid #ddd; border-radius: 8px; font-size: 14px; box-sizing: border-box;
        }
        .input-group input:focus { border-color: var(--vert-doux); outline: none; }

        /* Style erreur JS */
        .error-text { color: var(--rouge-erreur); font-size: 12px; margin-top: 3px; display: none; }
        .input-error { border-color: var(--rouge-erreur) !important; }

        button { 
            width: 100%; padding: 12px; margin-top: 10px;
            background: linear-gradient(to right, #2ecc71, #27ae60);
            color: white; border: none; border-radius: 25px; 
            font-weight: bold; cursor: pointer;
        }
        button:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(46, 204, 113, 0.4); }

        .error-msg { color: #e74c3c; font-size: 13px; margin-bottom: 15px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <div style="font-size: 40px; color: var(--vert-doux); margin-bottom: 10px;">
            <i class="fa-solid fa-key"></i>
        </div>
        <h2>Nouveau mot de passe</h2>
        
        <?php if (isset($_SESSION['error_msg'])): ?>
            <p class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?></p>
        <?php endif; ?>

        <!-- Ajout de id="reset-form" et novalidate -->
        <form id="reset-form" action="../../Controller/UtilisateurController.php" method="POST" novalidate>
            <input type="hidden" name="action" value="reset_password_submit">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <div class="input-group">
                <label>Nouveau mot de passe</label>
                <i class="fa-solid fa-lock"></i>
                <input type="password" id="new_password" name="new_password" placeholder="Min. 6 caractères">
                <div id="err-new" class="error-text"></div>
            </div>

            <div class="input-group">
                <label>Confirmez le mot de passe</label>
                <i class="fa-solid fa-lock"></i>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Répétez le mot de passe">
                <div id="err-confirm" class="error-text"></div>
            </div>

            <button type="submit">Changer le mot de passe</button>
        </form>
    </div>

    <!-- SCRIPT DE VALIDATION -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('reset-form');
            const pass1 = document.getElementById('new_password');
            const pass2 = document.getElementById('confirm_password');
            const err1 = document.getElementById('err-new');
            const err2 = document.getElementById('err-confirm');

            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Reset
                [pass1, pass2].forEach(el => el.classList.remove('input-error'));
                [err1, err2].forEach(el => { el.style.display = 'none'; el.innerText = ''; });

                // Validation Pass 1 (Longueur)
                if (pass1.value.length < 6) {
                    showError(pass1, err1, "Le mot de passe doit contenir au moins 6 caractères.");
                    isValid = false;
                }

                // Validation Pass 2 (Correspondance)
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