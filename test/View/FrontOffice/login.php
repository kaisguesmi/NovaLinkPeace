<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - PeaceLink</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <!-- On réutilise les styles du formulaire d'inscription -->
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: linear-gradient(105deg, rgba(93, 173, 226, 0.8), rgba(123, 211, 137, 0.8)); }
        .login-container { background-color: var(--blanc-pur); padding: 40px; border-radius: var(--border-radius); box-shadow: var(--card-shadow); width: 100%; max-width: 450px; }
        .login-container h1 { font-family: var(--font-titre); text-align: center; color: var(--vert-doux); margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; }
        .form-group input { width: 100%; padding: 12px 15px; border: 1px solid var(--gris-moyen); border-radius: 8px; font-size: 16px; }
        .error-message { color: var(--rouge-alerte); font-size: 14px; text-align:center; min-height: 20px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Welcome to PeaceLink!</h1>

        <?php 
            session_start();
            if (isset($_SESSION['error_login'])) {
                echo "<p class='error-message'>" . $_SESSION['error_login'] . "</p>";
                unset($_SESSION['error_login']);
            }
        ?>

        <form action="../../Controller/UtilisateurController.php" method="POST" novalidate>
            <!-- Champ caché pour indiquer l'action au contrôleur -->
            <input type="hidden" name="action" value="login">
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Password</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;">Login</button>
            <div style="text-align: right; margin-bottom: 15px;">
                <a href="forgot_password.php" style="font-size: 14px; color: #5dade2;">Mot de passe oublié ?</a>
            </div>
        </form>
    </div>
</body>
</html>