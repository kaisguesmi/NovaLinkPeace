<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - PeaceLink</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: linear-gradient(105deg, rgba(93, 173, 226, 0.8), rgba(123, 211, 137, 0.8)); }
        .signup-container { background-color: var(--blanc-pur); padding: 40px; border-radius: var(--border-radius); box-shadow: var(--card-shadow); width: 100%; max-width: 500px; transition: height 0.3s ease; }
        .signup-container h1 { font-family: var(--font-titre); text-align: center; color: var(--vert-doux); margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px 15px; border: 1px solid var(--gris-moyen); border-radius: 8px; font-size: 16px; }
        .error-message { color: var(--rouge-alerte); font-size: 14px; margin-top: 5px; min-height: 20px; }
        .role-specific-fields { display: none; } /* Caché par défaut */
    </style>
</head>
<body>

    <div class="signup-container">
        <h1>Join PeaceLink</h1>
        
        <?php 
            session_start();
            if (isset($_SESSION['errors'])) {
                foreach ($_SESSION['errors'] as $error) {
                    echo "<p class='error-message' style='text-align:center;'>$error</p>";
                }
                unset($_SESSION['errors']);
            }
        ?>

        <form id="signup-form" action="../../Controller/UtilisateurController.php" method="POST" novalidate>
            <!-- Sélecteur de rôle -->
            <input type="hidden" name="action" value="register">
            <div class="form-group">
                <label for="role">Join as:</label>
                <select id="role" name="role">
                    <option value="client" selected>Client</option>
                    <option value="organisation">Organization</option>
                </select>
            </div>

            <!-- Champs pour Client (visibles par défaut) -->
            <div id="client-fields" class="role-specific-fields" style="display: block;">
                <div class="form-group">
                    <label for="nom_complet">Full Name</label>
                    <input type="text" id="nom_complet" name="nom_complet">
                    <div class="error-message" id="error-nom"></div>
                </div>
                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" rows="3"></textarea>
                </div>
            </div>

            <!-- Champs pour Organisation -->
            <div id="organisation-fields" class="role-specific-fields">
                <div class="form-group">
                    <label for="nom_organisation">Organization Name</label>
                    <input type="text" id="nom_organisation" name="nom_organisation">
                    <div class="error-message" id="error-orga-nom"></div>
                </div>
                 <div class="form-group">
                    <label for="adresse">Adress</label>
                    <input type="text" id="adresse" name="adresse">
                    <div class="error-message" id="error-orga-adresse"></div>
                </div>
            </div>


            <hr style="margin: 20px 0; border: 1px solid var(--gris-moyen);">

            <!-- Champs communs -->
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email">
                <div class="error-message" id="error-email"></div>
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Password</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe">
                <div class="error-message" id="error-password"></div>
            </div>
            
            <button type="submit" class="btn-primary" style="width:100%;">Create Account</button>
        </form>
    </div>

    <!-- ... tout ton code HTML ... -->

<!-- Script pour gérer l'affichage dynamique des champs -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. On récupère les éléments
        const roleSelect = document.getElementById('role');
        const clientFields = document.getElementById('client-fields');
        const orgaFields = document.getElementById('organisation-fields');

        // 2. Fonction pour mettre à jour l'affichage
        function updateFields() {
            const selectedRole = roleSelect.value;

            // D'abord, on cache TOUT
            if(clientFields) clientFields.style.display = 'none';
            if(orgaFields) orgaFields.style.display = 'none';

            // Ensuite, on affiche seulement ce qui correspond au rôle
            if (selectedRole === 'client') {
                if(clientFields) clientFields.style.display = 'block';
            } 
            else if (selectedRole === 'organisation') {
                if(orgaFields) orgaFields.style.display = 'block';
            }
        }

        // 3. On écoute le changement sur le menu déroulant
        if (roleSelect) {
            roleSelect.addEventListener('change', updateFields);
            
            // 4. On lance la fonction au chargement de la page pour initialiser
            updateFields();
        }
    });
</script>

<!-- Ton script de validation reste ici -->
<script src="js/validation.js"></script>

</body>
</html>