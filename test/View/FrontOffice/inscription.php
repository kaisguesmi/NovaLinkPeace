<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - PeaceLink</title>
    
    <!-- FontAwesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- THEME PEACELINK (Même que Login) --- */
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
            display: flex; justify-content: center; align-items: center; min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card {
            background: var(--blanc-pur);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px; /* Un peu plus large que le login car plus de champs */
            text-align: center;
            animation: fadeIn 0.6s ease-out;
            margin: 20px; /* Marge pour mobile */
        }

        .logo-area { margin-bottom: 10px; color: var(--vert-doux); font-size: 50px; }
        h2 { color: var(--gris-fonce); margin-bottom: 25px; font-weight: 700; }

        /* Styles des Inputs */
        .input-group { position: relative; margin-bottom: 15px; text-align: left; }
        .input-group label { 
            display: block; margin-bottom: 5px; font-size: 13px; 
            font-weight: 600; color: #7f8c8d; margin-left: 15px;
        }
        
        .input-group i { 
            position: absolute; left: 15px; top: 42px; /* Ajusté avec le label */
            color: #aaa; font-size: 15px; 
        }
        
        /* Ajustement spécial pour le textarea */
        .input-group i.icon-textarea { top: 42px; } 

        .input-group input, .input-group select, .input-group textarea {
            width: 100%; padding: 12px 15px 12px 40px; /* Espace pour l'icône */
            border: 1px solid #ddd;
            border-radius: 50px; 
            font-size: 15px; 
            box-sizing: border-box; 
            background-color: #f9f9f9;
            font-family: inherit;
        }

        /* Style spécifique pour le Textarea (Bio) */
        .input-group textarea {
            border-radius: 20px; /* Moins arrondi pour le textarea */
            resize: vertical;
        }

        .input-group input:focus, .input-group select:focus, .input-group textarea:focus {
            outline: none; border-color: var(--bleu-pastel); background: #fff;
            box-shadow: 0 0 0 4px rgba(93, 173, 226, 0.1);
        }

        /* Bouton */
        .btn-primary {
            width: 100%; padding: 12px;
            background: linear-gradient(to right, var(--bleu-pastel), var(--vert-doux));
            color: white; border: none; border-radius: 50px; font-weight: bold; font-size: 16px;
            cursor: pointer; transition: transform 0.2s; margin-top: 15px;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(93, 173, 226, 0.4); }

        /* Messages d'erreur */
        .error-message { 
            color: var(--rouge-erreur); font-size: 12px; margin-top: 5px; 
            padding-left: 15px; min-height: 15px; font-weight: 600;
        }
        .server-error {
            background: #fdedec; color: #e74c3c; padding: 10px; border-radius: 8px;
            font-size: 13px; margin-bottom: 20px; border-left: 4px solid #e74c3c;
        }

        .login-link { margin-top: 20px; font-size: 14px; color: #7f8c8d; }
        .login-link a { color: var(--bleu-pastel); text-decoration: none; font-weight: bold; }
        .login-link a:hover { text-decoration: underline; }

        .role-specific-fields { display: none; animation: fadeIn 0.3s ease; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <div class="card">
        <div class="logo-area"><i class="fa-solid fa-leaf"></i></div>
        <h2>Rejoindre PeaceLink</h2>
        
        <!-- Affichage erreurs PHP -->
        <?php if (isset($_SESSION['errors'])): ?>
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <div class="server-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
            <?php endforeach; ?>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>

        <form id="signup-form" action="../../Controller/UtilisateurController.php" method="POST" novalidate>
            <input type="hidden" name="action" value="register">
            
            <!-- Sélecteur de rôle -->
            <div class="input-group">
                <label for="role">Je suis un(e)...</label>
                <i class="fa-solid fa-user-tag"></i>
                <select id="role" name="role">
                    <option value="client" selected>Client</option>
                    <option value="organisation">Organisation</option>
                </select>
            </div>

            <!-- ================= CHAMPS CLIENT ================= -->
            <div id="client-fields" class="role-specific-fields" style="display: block;">
                <div class="input-group">
                    <label for="nom_complet">Nom complet</label>
                    <i class="fa-solid fa-user"></i>
                    <input type="text" id="nom_complet" name="nom_complet" placeholder="Ex: Jean Dupont">
                    <div class="error-message" id="error-nom"></div>
                </div>
                <div class="input-group">
                    <label for="bio">Biographie</label>
                    <i class="fa-solid fa-pen-nib icon-textarea"></i>
                    <textarea id="bio" name="bio" rows="3" placeholder="Parlez-nous de vous..."></textarea>
                </div>
            </div>

            <!-- ================= CHAMPS ORGANISATION ================= -->
            <div id="organisation-fields" class="role-specific-fields">
                <div class="input-group">
                    <label for="nom_organisation">Nom de l'organisation</label>
                    <i class="fa-solid fa-building"></i>
                    <input type="text" id="nom_organisation" name="nom_organisation" placeholder="Ex: Peace World Inc.">
                    <div class="error-message" id="error-orga-nom"></div>
                </div>
                 <div class="input-group">
                    <label for="adresse">Adresse</label>
                    <i class="fa-solid fa-location-dot"></i>
                    <input type="text" id="adresse" name="adresse" placeholder="Ex: 12 Rue de la Paix, Paris">
                    <div class="error-message" id="error-orga-adresse"></div>
                </div>
            </div>

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

            <!-- ================= CHAMPS COMMUNS ================= -->
            <div class="input-group">
                <label for="email">Email</label>
                <i class="fa-solid fa-envelope"></i>
                <input type="text" id="email" name="email" placeholder="votre@email.com">
                <div class="error-message" id="error-email"></div>
            </div>
            
            <div class="input-group">
                <label for="mot_de_passe">Mot de passe</label>
                <i class="fa-solid fa-lock"></i>
                <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Minimum 8 caractères">
                <div class="error-message" id="error-password"></div>
            </div>
            
            <button type="submit" class="btn-primary">Créer mon compte</button>
        </form>

        <div class="login-link">
            Déjà membre ? <a href="login.php">Se connecter</a>
        </div>
    </div>

    <!-- Script pour l'affichage dynamique -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const clientFields = document.getElementById('client-fields');
            const orgaFields = document.getElementById('organisation-fields');

            function updateFields() {
                const selectedRole = roleSelect.value;
                
                // Masquer tout d'abord
                if(clientFields) clientFields.style.display = 'none';
                if(orgaFields) orgaFields.style.display = 'none';

                // Afficher selon le choix
                if (selectedRole === 'client') {
                    if(clientFields) clientFields.style.display = 'block';
                } else if (selectedRole === 'organisation') {
                    if(orgaFields) orgaFields.style.display = 'block';
                }
            }

            if (roleSelect) {
                roleSelect.addEventListener('change', updateFields);
                updateFields(); // Initialisation au chargement
            }
        });
    </script>

    <!-- Validation JS -->
    <script src="js/validation.js"></script>

</body>
</html>