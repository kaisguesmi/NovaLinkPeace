<<<<<<< HEAD
<?php
session_start();

// Sécurité Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../FrontOffice/login.php");
    exit();
}

$user = $_SESSION['edit_user_data'] ?? null;
$role = $_SESSION['edit_user_role'] ?? '';

if (!$user) {
    header("Location: backoffice.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Utilisateur - Admin</title>
    <link rel="stylesheet" href="backofficeStyle.css"> <!-- Ton CSS existant -->
    <style>
        /* Style simple pour ce formulaire centré */
        body { background-color: #f4f6f9; font-family: sans-serif; }
        .edit-container { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn-submit { width: 100%; background-color: #3498db; color: white; border: none; padding: 12px; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px;}
        .btn-submit:hover { background-color: #2980b9; }
        .btn-cancel { display:block; text-align:center; margin-top:15px; color: #7f8c8d; text-decoration:none; }
        .password-note { font-size: 12px; color: #e74c3c; margin-top: 5px; }
    </style>
</head>
<body>

<div class="edit-container">
    <h2>Modifier <?php echo ucfirst($role); ?></h2>

    <form action="../../Controller/UtilisateurController.php" method="POST">
        <input type="hidden" name="action" value="admin_update_user">
        <input type="hidden" name="id_utilisateur" value="<?php echo $user['id_utilisateur']; ?>">
        <input type="hidden" name="role_user" value="<?php echo $role; ?>">

        <!-- 1. Champs Communs -->
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="form-group">
            <label>Nouveau Mot de passe</label>
            <input type="password" name="new_password" placeholder="Laisser vide pour ne pas changer">
            <p class="password-note">Attention : Si vous écrivez ici, le mot de passe de l'utilisateur sera changé.</p>
        </div>

        <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

        <!-- 2. Champs Spécifiques CLIENT -->
        <?php if ($role === 'client'): ?>
            <div class="form-group">
                <label>Nom Complet</label>
                <input type="text" name="nom_complet" value="<?php echo htmlspecialchars($user['nom_complet']); ?>" required>
            </div>
            <div class="form-group">
                <label>Biographie</label>
                <textarea name="bio" rows="4"><?php echo htmlspecialchars($user['bio']); ?></textarea>
            </div>
        <?php endif; ?>

        <!-- 3. Champs Spécifiques ORGANISATION -->
        <?php if ($role === 'organisation'): ?>
            <div class="form-group">
                <label>Nom de l'Organisation</label>
                <input type="text" name="nom_organisation" value="<?php echo htmlspecialchars($user['nom_organisation']); ?>" required>
            </div>
            <div class="form-group">
                <label>Adresse</label>
                <input type="text" name="adresse" value="<?php echo htmlspecialchars($user['adresse']); ?>" required>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn-submit">Enregistrer les modifications</button>
        <a href="backoffice.php" class="btn-cancel">Annuler</a>
    </form>
</div>

</body>
=======
<?php
session_start();

// Sécurité Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../FrontOffice/login.php");
    exit();
}

$user = $_SESSION['edit_user_data'] ?? null;
$role = $_SESSION['edit_user_role'] ?? '';

if (!$user) {
    header("Location: backoffice.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Utilisateur - Admin</title>
    <link rel="stylesheet" href="backofficeStyle.css"> <!-- Ton CSS existant -->
    <style>
        /* Style simple pour ce formulaire centré */
        body { background-color: #f4f6f9; font-family: sans-serif; }
        .edit-container { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn-submit { width: 100%; background-color: #3498db; color: white; border: none; padding: 12px; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px;}
        .btn-submit:hover { background-color: #2980b9; }
        .btn-cancel { display:block; text-align:center; margin-top:15px; color: #7f8c8d; text-decoration:none; }
        .password-note { font-size: 12px; color: #e74c3c; margin-top: 5px; }
    </style>
</head>
<body>

<div class="edit-container">
    <h2>Modifier <?php echo ucfirst($role); ?></h2>

    <form action="../../Controller/UtilisateurController.php" method="POST">
        <input type="hidden" name="action" value="admin_update_user">
        <input type="hidden" name="id_utilisateur" value="<?php echo $user['id_utilisateur']; ?>">
        <input type="hidden" name="role_user" value="<?php echo $role; ?>">

        <!-- 1. Champs Communs -->
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="form-group">
            <label>Nouveau Mot de passe</label>
            <input type="password" name="new_password" placeholder="Laisser vide pour ne pas changer">
            <p class="password-note">Attention : Si vous écrivez ici, le mot de passe de l'utilisateur sera changé.</p>
        </div>

        <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

        <!-- 2. Champs Spécifiques CLIENT -->
        <?php if ($role === 'client'): ?>
            <div class="form-group">
                <label>Nom Complet</label>
                <input type="text" name="nom_complet" value="<?php echo htmlspecialchars($user['nom_complet']); ?>" required>
            </div>
            <div class="form-group">
                <label>Biographie</label>
                <textarea name="bio" rows="4"><?php echo htmlspecialchars($user['bio']); ?></textarea>
            </div>
        <?php endif; ?>

        <!-- 3. Champs Spécifiques ORGANISATION -->
        <?php if ($role === 'organisation'): ?>
            <div class="form-group">
                <label>Nom de l'Organisation</label>
                <input type="text" name="nom_organisation" value="<?php echo htmlspecialchars($user['nom_organisation']); ?>" required>
            </div>
            <div class="form-group">
                <label>Adresse</label>
                <input type="text" name="adresse" value="<?php echo htmlspecialchars($user['adresse']); ?>" required>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn-submit">Enregistrer les modifications</button>
        <a href="backoffice.php" class="btn-cancel">Annuler</a>
    </form>
</div>

</body>
>>>>>>> origin/Taherwork
</html>