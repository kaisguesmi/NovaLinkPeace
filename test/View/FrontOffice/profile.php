<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . '/../../Model/Database.php';
include_once __DIR__ . '/../../Model/Utilisateur.php';

$database = new Database();
$db = $database->getConnection();
$utilisateur = new Utilisateur($db);

// 1. Détection du rôle
$role = $_SESSION['role'] ?? 'client';
$userData = [];

// 2. Récupération des données selon le rôle
if ($role === 'organisation') {
    $userData = $utilisateur->findOrganisationById($_SESSION['user_id']);
    // Normalisation des variables pour l'affichage
    $displayName = $userData['nom_organisation'];
    $displayInfoLabel = "Adress";
    $displayInfoContent = $userData['adresse'];
} else {
    // Par défaut : Client
    $userData = $utilisateur->findClientById($_SESSION['user_id']);
    $displayName = $userData['nom_complet'];
    $displayInfoLabel = "Bio";
    $displayInfoContent = $userData['bio'];
}

// 3. Générer les initiales
$initials = '';
$parts = explode(' ', $displayName);
$initials .= mb_substr($parts[0], 0, 1);
if (count($parts) > 1) {
    $initials .= mb_substr(end($parts), 0, 1);
}
$initials = strtoupper($initials);

// 4. Formater la date
$date = date_create($userData['date_inscription']);
$formatted_date = date_format($date, 'F Y'); 
$avatarHTML = '';
if (!empty($userData['photo_profil'])) {
    // Si l'utilisateur a une photo, on l'affiche
    // Attention au chemin : profile.php est dans View/FrontOffice, uploads est dans View/uploads
    $photoPath = '../uploads/' . htmlspecialchars($userData['photo_profil']);
    $avatarHTML = "<img src='$photoPath' alt='Profil' class='profile-img-real'>";
} else {
    // Sinon, on affiche les initiales
    $avatarHTML = $initials;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - PeaceLink</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .page-container { max-width: 900px; margin: 120px auto 40px auto; }
        
        /* Reuse your existing CSS */
        .profile-view-container {
            display: flex; align-items: center; gap: 30px; padding: 30px;
            background-color: var(--blanc-pur); border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
        }
        .profile-avatar {
            flex-shrink: 0; width: 120px; height: 120px; border-radius: 50%;
            background: linear-gradient(135deg, var(--bleu-pastel), var(--vert-doux));
            color: var(--blanc-pur); display: flex; align-items: center; justify-content: center;
            font-size: 48px; font-weight: 600; font-family: var(--font-titre);
        }
        .profile-info h1 { margin: 0 0 10px 0; color: var(--bleu-nuit); }
        .profile-info .info-content { font-size: 16px; color: #555; margin-bottom: 15px; }
        .profile-info .meta { color: #888; font-size: 14px; margin-bottom: 20px; }
        
        .edit-profile-btn { 
            padding: 8px 18px; background-color: var(--bleu-pastel); color: white;
            border: none; border-radius: 8px; cursor: pointer; 
        }
        .profile-edit-container {
            display: none; margin-top: 20px; padding: 40px;
            background-color: var(--blanc-pur); border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; }
        .success-message { color: var(--vert-doux); font-weight: bold; margin-bottom: 15px; }
        .badge-role { 
            background-color: #eee; padding: 4px 8px; border-radius: 4px; 
            font-size: 12px; text-transform: uppercase; letter-spacing: 1px; 
        }
        .profile-img-real {
        width: 100%;
        height: 100%;
        object-fit: cover; /* L'image remplit le rond sans être déformée */
        border-radius: 50%;
    }
    /* On s'assure que le conteneur reste rond */
    .profile-avatar {
        overflow: hidden; /* Important pour rogner l'image carrée en rond */
        background: #ddd; /* Fond gris si l'image charge mal */
    }
    .btn-ai {
    background: linear-gradient(135deg, #8e44ad, #9b59b6); /* Violet mystique */
    color: white;
    border: none;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 12px;
    cursor: pointer;
    margin-bottom: 8px;
    transition: transform 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.btn-ai:hover { transform: scale(1.05); box-shadow: 0 2px 5px rgba(142, 68, 173, 0.4); }
    </style>
</head>
<body>
    
    <?php include 'partials/header.php'; ?>

    <main class="page-container">

        <!-- ========= VUE DU PROFIL ========= -->
        <div id="profile-view" class="profile-view-container">
            <div class="profile-avatar">
                <?php echo $avatarHTML; ?>
            </div>
            <div class="profile-info">
                <h1>
                    <?php echo htmlspecialchars($displayName); ?>
                    <!-- Petit badge pour indiquer le rôle -->
                    <span class="badge-role"><?php echo $role; ?></span>
                </h1>
                
                <p class="info-content">
                    <strong><?php echo $displayInfoLabel; ?> :</strong> <br>
                    <?php echo htmlspecialchars($displayInfoContent) ?: 'Non renseigné.'; ?>
                </p>

                <div class="meta">
                    <span>Membre depuis <?php echo $formatted_date; ?></span>
                    <?php if($role === 'organisation' && isset($userData['statut_verification'])): ?>
                         | Statut: <?php echo htmlspecialchars($userData['statut_verification']); ?>
                    <?php endif; ?>
                </div>
                
                <button id="show-edit-form-btn" class="edit-profile-btn">Edit profile</button>
            </div>
        </div>
        
        <!-- ========= FORMULAIRE D'ÉDITION ========= -->
        <div id="profile-edit" class="profile-edit-container">
            <h2>Edit my information</h2>
            
            <?php if (isset($_SESSION['success_update'])): ?>
                <p class="success-message"><?php echo $_SESSION['success_update']; ?></p>
                <?php unset($_SESSION['success_update']); ?>
            <?php endif; ?>

            <form id="profile-form" action="../../Controller/UtilisateurController.php" method="POST" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="action" value="updateProfile">
            <form id="profile-form" action="../../Controller/UtilisateurController.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="updateProfile">
                
                <!-- === NOUVEAU CHAMP EMAIL (Commun) === -->
                <div class="form-group">
                    <label for="email">Adresse Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                </div>
                
                <!-- CHAMPS SPECIFIQUES CLIENT -->
                <?php if ($role === 'client'): ?>
                    <div class="form-group">
                        <label for="nom_complet">Full Name</label>
                        <input type="text" id="nom_complet" name="nom_complet" value="<?php echo htmlspecialchars($userData['nom_complet']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="bio">Biographie</label>

                        <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($userData['bio']); ?></textarea>
                    </div>
                
                <!-- CHAMPS SPECIFIQUES ORGANISATION -->
                <?php elseif ($role === 'organisation'): ?>
                    <div class="form-group">
                        <label for="nom_organisation">Organization Name</label>
                        <input type="text" id="nom_organisation" name="nom_organisation" value="<?php echo htmlspecialchars($userData['nom_organisation']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="adresse">Adress</label>
                        <input type="text" id="adresse" name="adresse" value="<?php echo htmlspecialchars($userData['adresse']); ?>">
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="photo_profil">Photo de profil</label>
                    <input type="file" id="photo_profil" name="photo_profil" accept="image/*">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary" style="padding:10px 20px; cursor:pointer;">Save</button>
                    <button type="button" class="btn-primary" id="cancel-edit-btn" style="background:#ccc; padding:10px 20px; cursor:pointer; border:none;">Cancel</button>
                </div>
            </form>
        </div>

    </main>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileView = document.getElementById('profile-view');
            const profileEditForm = document.getElementById('profile-edit');
            const showEditBtn = document.getElementById('show-edit-form-btn');
            const cancelEditBtn = document.getElementById('cancel-edit-btn');

            showEditBtn.addEventListener('click', function() {
                profileView.style.display = 'none';
                profileEditForm.style.display = 'block';
            });

            cancelEditBtn.addEventListener('click', function() {
                profileEditForm.style.display = 'none';
                profileView.style.display = 'flex';
            });
        });
    </script>
</body>
</html>