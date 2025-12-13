<<<<<<< HEAD
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../FrontOffice/login.php"); exit(); }
$targetId = $_SESSION['ban_target_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bannir un utilisateur</title>
    <link rel="stylesheet" href="backofficeStyle.css">
    <style>
        .ban-container { max-width: 500px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-top: 5px solid #e74c3c; }
        h2 { color: #e74c3c; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        textarea, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 12px; background: #e74c3c; color: white; border: none; font-weight: bold; cursor: pointer; border-radius: 4px; }
        button:hover { background: #c0392b; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #777; text-decoration: none; }
    </style>
</head>
<body>
    <div class="ban-container">
        <h2><i class="fa-solid fa-gavel"></i> Bannir l'utilisateur</h2>
        <form action="../../Controller/UtilisateurController.php" method="POST">
            <input type="hidden" name="action" value="admin_ban_submit">
            <input type="hidden" name="id_utilisateur" value="<?php echo $targetId; ?>">

            <div class="form-group">
                <label>Raison du bannissement</label>
                <textarea name="raison" rows="4" placeholder="Pourquoi bannir cet utilisateur ?" required></textarea>
            </div>

            <div class="form-group">
                <label>Durée du bannissement</label>
                <select name="duree">
                    <option value="1">24 Heures</option>
                    <option value="3">3 Jours</option>
                    <option value="7">1 Semaine</option>
                    <option value="30">1 Mois</option>
                    <option value="365">1 An</option>
                    <option value="3650">Permanent (10 ans)</option>
                </select>
            </div>

            <button type="submit">Confirmer le bannissement</button>
        </form>
        <a href="backoffice.php" class="back-link">Annuler</a>
    </div>
</body>
=======
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../FrontOffice/login.php"); exit(); }
$targetId = $_SESSION['ban_target_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bannir un utilisateur</title>
    <link rel="stylesheet" href="backofficeStyle.css">
    <style>
        .ban-container { max-width: 500px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-top: 5px solid #e74c3c; }
        h2 { color: #e74c3c; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        textarea, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 12px; background: #e74c3c; color: white; border: none; font-weight: bold; cursor: pointer; border-radius: 4px; }
        button:hover { background: #c0392b; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #777; text-decoration: none; }
    </style>
</head>
<body>
    <div class="ban-container">
        <h2><i class="fa-solid fa-gavel"></i> Bannir l'utilisateur</h2>
        <form action="../../Controller/UtilisateurController.php" method="POST">
            <input type="hidden" name="action" value="admin_ban_submit">
            <input type="hidden" name="id_utilisateur" value="<?php echo $targetId; ?>">

            <div class="form-group">
                <label>Raison du bannissement</label>
                <textarea name="raison" rows="4" placeholder="Pourquoi bannir cet utilisateur ?" required></textarea>
            </div>

            <div class="form-group">
                <label>Durée du bannissement</label>
                <select name="duree">
                    <option value="1">24 Heures</option>
                    <option value="3">3 Jours</option>
                    <option value="7">1 Semaine</option>
                    <option value="30">1 Mois</option>
                    <option value="365">1 An</option>
                    <option value="3650">Permanent (10 ans)</option>
                </select>
            </div>

            <button type="submit">Confirmer le bannissement</button>
        </form>
        <a href="backoffice.php" class="back-link">Annuler</a>
    </div>
</body>
>>>>>>> origin/Taherwork
</html>