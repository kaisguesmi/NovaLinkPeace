<?php
session_start();
$data = $_SESSION['public_profile_data'] ?? null;

if (!$data) {
    header("Location: index.php");
    exit();
}

// Détermine le nom et la description selon le type
$nom = $data['nom_complet'] ?? $data['nom_organisation'] ?? 'Inconnu';
$desc = $data['bio'] ?? $data['adresse'] ?? '';
$roleDisplay = $data['role_display'] ?? 'Membre';

// 1. Initiales (Solution de repli)
$initials = strtoupper(substr($nom, 0, 1));

// 2. Gestion de la Photo (NOUVEAU CODE)
$avatarContent = $initials; // Par défaut, on met les initiales

if (!empty($data['photo_profil'])) {
    // Le chemin vers le dossier uploads (on remonte d'un dossier depuis FrontOffice)
    $photoPath = '../uploads/' . htmlspecialchars($data['photo_profil']);
    // On remplace les initiales par l'image
    $avatarContent = "<img src='$photoPath' alt='$nom' class='public-profile-img'>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil de <?php echo htmlspecialchars($nom); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .page-container { max-width: 800px; margin: 120px auto; }
        .profile-header { text-align: center; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        
        /* Style du cercle de l'avatar */
        .avatar-large { 
            width: 120px; 
            height: 120px; 
            background: var(--vert-doux, #7bd389); /* Couleur de fond si pas d'image */
            color: white; 
            font-size: 40px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            border-radius: 50%; 
            margin: 0 auto 20px auto; 
            overflow: hidden; /* Important pour rogner l'image en rond */
            border: 4px solid white; /* Petit bord blanc joli */
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        /* Style de l'image à l'intérieur du cercle */
        .public-profile-img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* L'image remplit tout le cercle sans être écrasée */
        }

        .role-tag { background: var(--bleu-pastel, #5dade2); color: white; padding: 5px 15px; border-radius: 20px; font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;}
        .desc-box { margin-top: 25px; font-size: 18px; color: #555; line-height: 1.6; max-width: 600px; margin-left: auto; margin-right: auto;}
        .join-date { margin-top: 30px; color: #aaa; font-size: 14px; }
    </style>
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <div class="page-container">
        <div class="profile-header">
            <!-- On affiche ici soit l'image, soit la lettre -->
            <div class="avatar-large"><?php echo $avatarContent; ?></div>
            
            <h1><?php echo htmlspecialchars($nom); ?></h1>
            <span class="role-tag"><?php echo $roleDisplay; ?></span>

            <div class="desc-box">
                <?php if($roleDisplay == 'Organisation'): ?>
                    <p><i class="fa-solid fa-location-dot" style="color:#e74c3c;"></i> <?php echo htmlspecialchars($desc); ?></p>
                <?php else: ?>
                    <p><i>"<?php echo htmlspecialchars($desc) ?: 'Aucune biographie disponible.'; ?>"</i></p>
                <?php endif; ?>
            </div>
            
            <div class="join-date">
                <i class="fa-regular fa-calendar"></i> Membre PeaceLink depuis <?php echo date('F Y', strtotime($data['date_inscription'])); ?>
            </div>
        </div>
    </div>
</body>
</html>