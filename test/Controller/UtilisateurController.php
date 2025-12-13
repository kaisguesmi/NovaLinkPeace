<?php
session_start();

include_once __DIR__ . '/../Model/Database.php';
include_once __DIR__ . '/../Model/Utilisateur.php';

// Initialisation
$database = new Database();
$db = $database->getConnection();
$utilisateur = new Utilisateur($db);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- ROUTEUR ---
switch ($action) {
    case 'register':
        handleRegister($utilisateur);
        break;

    case 'login':
        handleLogin($utilisateur);
        break;

    case 'logout':
        handleLogout();
        break;
        
    case 'updateProfile':
        handleUpdateProfile($utilisateur);
        break;
    case 'admin_validate_org':
        handleAdminValidateOrg($utilisateur);
        break;
    
    case 'admin_delete_user':
        handleAdminDeleteUser($utilisateur);
        break;
    case 'search':
        handleSearch($utilisateur);
        break;

    case 'show_public_profile':
        handleShowPublicProfile($utilisateur);
        break;
    case 'admin_edit_form':     // 1. Afficher le formulaire
        handleAdminEditForm($utilisateur);
        break;

    case 'admin_update_user':   // 2. Traiter la modification
        handleAdminUpdateUser($utilisateur);
        break;
    case 'forgot_password_request':
        handleForgotPasswordRequest($utilisateur);
        break;

    case 'reset_password_submit':
        handleResetPasswordSubmit($utilisateur);
        break;
    case 'admin_ban_form':    // Afficher le formulaire de ban
        handleAdminBanForm($utilisateur);
        break;

    case 'admin_ban_submit':  // Traiter le bannissement
        handleAdminBanSubmit($utilisateur);
        break;
    case 'admin_unban_user':
        handleAdminUnbanUser($utilisateur);
        break;
    case 'ajax_get_photo':
        handleAjaxGetPhoto($utilisateur);
        break;
    case 'login_with_face':
    handleLoginWithFace($utilisateur);
    break;

    default:
        header("Location: ../View/FrontOffice/index.php");
        exit();
}



function handleRegister($utilisateur) {
    // 1. Récupération des données
     $role = $_POST['role'] ?? 'client';
    
    // --- SECURITÉ AJOUTÉE ---
    // Si le rôle n'est ni client ni organisation, on force 'client' par défaut.
    // Cela empêche quelqu'un d'envoyer role='admin' via une requête forcée.
    if ($role !== 'client' && $role !== 'organisation') {
        $role = 'client';
    }
    // ------------------------

    $email = $_POST['email'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    $data = [
        'role' => $role,
        'email' => $email,
        'mot_de_passe' => $mot_de_passe
    ];

    // Gestion des champs spécifiques
    if ($role === 'client') {
        $data['nom_complet'] = $_POST['nom_complet'] ?? '';
        $data['bio'] = $_POST['bio'] ?? '';
        $nom_session = $data['nom_complet'];

    } elseif ($role === 'organisation') {
        $data['nom_organisation'] = $_POST['nom_organisation'] ?? '';
        $data['adresse'] = $_POST['adresse'] ?? '';
        $nom_session = $data['nom_organisation'];
    }

    // 2. Appel au Modèle pour créer l'utilisateur
    $newUserId = $utilisateur->create($data);

    if ($newUserId) {
        // --- CONNEXION AUTOMATIQUE ---
        
        // On démarre la session si ce n'est pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // On remplit les variables de session
        $_SESSION['user_id'] = $newUserId;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;
        $_SESSION['username'] = $nom_session;

        // Message de bienvenue
        $_SESSION['success_message'] = "Bienvenue ! Votre compte a été créé avec succès.";

        // --- REDIRECTION INTELLIGENTE ---
        if ($role === 'admin') {
            header("Location: ../View/BackOffice/backoffice.php");
        } else {
            // Client et Organisation vont sur l'accueil
            header("Location: ../View/FrontOffice/index.php");
        }
        exit();

    } else {
        // En cas d'échec (ex: email déjà pris)
        $_SESSION['errors'] = ["Une erreur est survenue (Email déjà utilisé ?)."];
        header("Location: ../View/FrontOffice/inscription.php");
        exit();
    }
}

function handleLogin($utilisateur) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['mot_de_passe'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['error_login'] = "Veuillez remplir tous les champs.";
        header("Location: ../View/FrontOffice/login.php");
        exit();
    }

    // 1. Vérifier email et mot de passe (Table Utilisateur)
    $userFound = $utilisateur->findByEmail($email);

    // C'est ICI que tout se joue :
    if ($userFound && password_verify($password, $userFound['mot_de_passe_hash'])) {
        
        // ============================================================
        // DEBUT DU BLOC : VÉRIFICATION DU BANNISSEMENT
        // ============================================================
        
        // On récupère les infos de ban pour cet utilisateur
        $banInfo = $utilisateur->getBanInfo($userFound['id_utilisateur']);

        // Si la colonne 'est_banni' vaut 1
        if ($banInfo && $banInfo['est_banni'] == 1) {
            $dateFin = new DateTime($banInfo['date_fin_bannissement']);
            $maintenant = new DateTime();

            // Si la date de fin est encore dans le futur (DateFin > Maintenant)
            if ($dateFin > $maintenant) {
                // IL EST ENCORE BANNI -> On bloque tout !
                
                // On stocke les infos pour l'affichage de la page d'erreur
                $_SESSION['banned_reason'] = $banInfo['raison_bannissement'];
                $_SESSION['banned_until'] = $banInfo['date_fin_bannissement'];
                
                // On redirige vers la page spéciale "Banned"
                header("Location: ../View/FrontOffice/banned_page.php");
                exit(); // On arrête le script ici, il ne sera pas connecté.
            } else {
                // LE TEMPS EST ÉCOULÉ -> On le débannit
                $utilisateur->unbanUser($userFound['id_utilisateur']);
                // Le code continue en dessous, donc il sera connecté normalement.
            }
        }
        // ============================================================
        // FIN DU BLOC BANNISSEMENT
        // ============================================================


        // 2. Connexion réussie (Si on arrive ici, c'est qu'il n'est pas banni)
        $_SESSION['user_id'] = $userFound['id_utilisateur'];
        $_SESSION['email'] = $userFound['email'];

        // 3. DÉTECTION DU RÔLE
        $role = $utilisateur->getUserRole($userFound['id_utilisateur']);
        $_SESSION['role'] = $role;

        // 4. Redirection selon le rôle
        if ($role === 'organisation') {
            $orgaDetails = $utilisateur->findOrganisationById($userFound['id_utilisateur']);
            $_SESSION['username'] = $orgaDetails['nom_organisation'];
            header("Location: ../View/FrontOffice/index.php"); 
        } 
        elseif ($role === 'client') {
            $clientDetails = $utilisateur->findClientById($userFound['id_utilisateur']);
            $_SESSION['username'] = $clientDetails['nom_complet'];
            header("Location: ../View/FrontOffice/index.php");
        } 
        elseif ($role === 'admin') {
             $_SESSION['username'] = "Administrateur";
             header("Location: ../View/BackOffice/backoffice.php"); 
        } 
        else {
             header("Location: ../View/FrontOffice/index.php");
        }
        exit();

    } else {
        $_SESSION['error_login'] = "Email ou mot de passe incorrect.";
        header("Location: ../View/FrontOffice/login.php");
        exit();
    }
}

function handleLogout() {
    session_unset();
    session_destroy();
    header("Location: ../View/FrontOffice/index.php");
    exit();
}

function handleUpdateProfile($utilisateur) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../View/FrontOffice/login.php");
        exit();
    }
    
    $id = $_SESSION['user_id'];
    $role = $_SESSION['role'] ?? 'client'; 

    // 1. Récupération de l'email
    $email = $_POST['email'] ?? '';

    // 2. Mise à jour de l'Email (Table Utilisateur)
    if (!empty($email)) {
        if ($utilisateur->updateEmail($id, $email)) {
            // Important : Mettre à jour la session pour que le changement soit immédiat
            $_SESSION['email'] = $email;
        } else {
            // Optionnel : Gérer l'erreur si l'email est déjà pris
            // $_SESSION['error_update'] = "Cet email est déjà utilisé.";
        }
    }

    // 3. Mise à jour des infos spécifiques (Client ou Orga)
    if ($role === 'client') {
        $nom_complet = $_POST['nom_complet'] ?? '';
        $bio = $_POST['bio'] ?? '';

        if ($utilisateur->updateClient($id, $nom_complet, $bio)) {
            $_SESSION['username'] = $nom_complet;
        }
    } 
    elseif ($role === 'organisation') {
        $nom_orga = $_POST['nom_organisation'] ?? '';
        $adresse = $_POST['adresse'] ?? '';

        if ($utilisateur->updateOrganisation($id, $nom_orga, $adresse)) {
            $_SESSION['username'] = $nom_orga;
        }
    }

    // 4. Gestion de la Photo (Code existant inchangé)
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['photo_profil']['tmp_name'];
        $fileName = $_FILES['photo_profil']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/../View/uploads/';
            if (!is_dir($uploadFileDir)) mkdir($uploadFileDir, 0755, true);
            $dest_path = $uploadFileDir . $newFileName;

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $utilisateur->updatePhoto($id, $newFileName);
            }
        }
    }

    $_SESSION['success_update'] = "Profil mis à jour avec succès !";
    header("Location: ../View/FrontOffice/profile.php");
    exit();
}

// --- FONCTIONS ADMIN ---

function checkAdminAccess() {
    // Sécurité : On vérifie si l'utilisateur est connecté ET si c'est un admin
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        // Tentative d'accès non autorisé
        header("Location: ../View/FrontOffice/login.php");
        exit();
    }
}

function handleAdminValidateOrg($utilisateur) {
    checkAdminAccess(); // Sécurité d'abord

    $id_org = $_POST['id_organisation'] ?? null;
    $id_admin = $_SESSION['user_id'];

    if ($id_org && $utilisateur->validateOrganisation($id_admin, $id_org)) {
        $_SESSION['success_msg'] = "L'organisation a été validée avec succès.";
    } else {
        $_SESSION['error_msg'] = "Erreur lors de la validation.";
    }

    header("Location: ../View/BackOffice/backoffice.php"); // Note le .php
    exit();
}

function handleAdminDeleteUser($utilisateur) {
    checkAdminAccess();

    $id_user = $_POST['id_utilisateur'] ?? null;

    // On empêche l'admin de se supprimer lui-même par erreur
    if ($id_user == $_SESSION['user_id']) {
        $_SESSION['error_msg'] = "Vous ne pouvez pas supprimer votre propre compte ici.";
        header("Location: ../View/BackOffice/backoffice.php");
        exit();
    }

    if ($id_user && $utilisateur->deleteUser($id_user)) {
        $_SESSION['success_msg'] = "Utilisateur supprimé avec succès.";
    } else {
        $_SESSION['error_msg'] = "Erreur lors de la suppression.";
    }

    header("Location: ../View/BackOffice/backoffice.php");
    exit();
}
function handleSearch($utilisateur) {
    $keyword = $_GET['q'] ?? '';
    
    if (empty($keyword)) {
        header("Location: ../View/FrontOffice/index.php");
        exit();
    }

    // On récupère les résultats
    $results = $utilisateur->searchGlobal($keyword);
    
    // On stocke les résultats en session pour les afficher dans la vue
    $_SESSION['search_results'] = $results;
    $_SESSION['search_keyword'] = $keyword;

    header("Location: ../View/FrontOffice/search_results.php");
    exit();
}

function handleShowPublicProfile($utilisateur) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        header("Location: ../View/FrontOffice/index.php");
        exit();
    }

    // On doit deviner le rôle pour savoir quelle méthode appeler
    // On utilise ta méthode getUserRole()
    $role = $utilisateur->getUserRole($id);
    $profileData = null;

    if ($role === 'client') {
        $profileData = $utilisateur->findClientById($id);
        $profileData['role_display'] = 'Client';
    } elseif ($role === 'organisation') {
        $profileData = $utilisateur->findOrganisationById($id);
        $profileData['role_display'] = 'Organisation';
    }

    if ($profileData) {
        $_SESSION['public_profile_data'] = $profileData;
        header("Location: ../View/FrontOffice/public_profile.php");
    } else {
        // Profil introuvable
        header("Location: ../View/FrontOffice/index.php");
    }
    exit();
}
// Afficher le formulaire de modification pour l'admin
function handleAdminEditForm($utilisateur) {
    checkAdminAccess(); // Sécurité

    $id = $_GET['id'] ?? null;
    if (!$id) {
        header("Location: ../View/BackOffice/backoffice.php");
        exit();
    }

    // On récupère le rôle pour savoir quelles données chercher
    $role = $utilisateur->getUserRole($id);
    $userData = null;

    if ($role === 'client') {
        $userData = $utilisateur->findClientById($id);
    } elseif ($role === 'organisation') {
        $userData = $utilisateur->findOrganisationById($id);
    }

    if ($userData) {
        // On passe les données à la vue via la session (simple et efficace)
        $_SESSION['edit_user_data'] = $userData;
        $_SESSION['edit_user_role'] = $role;
        header("Location: ../View/BackOffice/edit_user.php");
    } else {
        $_SESSION['error_msg'] = "Utilisateur introuvable.";
        header("Location: ../View/BackOffice/backoffice.php");
    }
    exit();
}

// Traiter la mise à jour par l'admin
function handleAdminUpdateUser($utilisateur) {
    checkAdminAccess();

    $id = $_POST['id_utilisateur'];
    $role = $_POST['role_user'];
    $email = $_POST['email'];
    $newPassword = $_POST['new_password']; // Peut être vide

    // 1. Mise à jour des infos de connexion (Email + MDP si rempli)
    $utilisateur->updateUserCredentials($id, $email, $newPassword);

    // 2. Mise à jour des infos spécifiques
    if ($role === 'client') {
        $nom = $_POST['nom_complet'];
        $bio = $_POST['bio'];
        $utilisateur->updateClient($id, $nom, $bio);
    } 
    elseif ($role === 'organisation') {
        $nomOrga = $_POST['nom_organisation'];
        $adresse = $_POST['adresse'];
        // Note : On utilise ta fonction existante updateOrganisation
        $utilisateur->updateOrganisation($id, $nomOrga, $adresse);
    }

    $_SESSION['success_msg'] = "L'utilisateur a été modifié avec succès.";
    header("Location: ../View/BackOffice/backoffice.php");
    exit();
}
// 1. Traite la demande (Génère le token)
function handleForgotPasswordRequest($utilisateur) {
    $email = $_POST['email'] ?? '';

    // Vérifie si l'email existe
    $user = $utilisateur->findByEmail($email);

    if ($user) {
        // 1. Générer le token
        $token = bin2hex(random_bytes(32));
        $utilisateur->setResetToken($email, $token);

        // 2. Préparer le lien (CORRIGÉ ICI)
        $server = $_SERVER['HTTP_HOST']; 
        
        // C'est ici que j'ai mis le bon nom de ton dossier : "test"
        $folder = "/test"; 
        
        $resetLink = "http://" . $server . $folder . "/View/FrontOffice/reset_password.php?token=" . $token;

        // 3. Préparer l'email
        $to = $email;
        $subject = "Réinitialisation de votre mot de passe - PeaceLink";
        
        $message = "
        <html>
        <head>
          <title>Mot de passe oublié</title>
        </head>
        <body>
          <h2>Bonjour,</h2>
          <p>Vous avez demandé à réinitialiser votre mot de passe sur PeaceLink.</p>
          <p>Cliquez sur le lien ci-dessous pour changer votre mot de passe :</p>
          <p>
            <a href='$resetLink' style='background-color:#5dade2; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Réinitialiser mon mot de passe</a>
          </p>
          <p>Ou copiez ce lien : $resetLink</p>
          <p>Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.</p>
        </body>
        </html>
        ";

        // En-têtes obligatoires
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        
        // --- IMPORTANT : METS TON ADRESSE GMAIL ICI (Celle du sendmail.ini) ---
        $headers .= "From: PeaceLink <ton_adresse_gmail_ici@gmail.com>" . "\r\n";

        // 4. Envoyer le mail
        if (mail($to, $subject, $message, $headers)) {
            $_SESSION['info_mail'] = "Un email de réinitialisation a été envoyé à $email. Vérifiez vos spams !";
        } else {
            $_SESSION['info_mail'] = "Erreur technique : L'email n'a pas pu être envoyé. Vérifiez la config XAMPP.";
        }
        
        header("Location: ../View/FrontOffice/forgot_password.php");
        exit();

    } else {
        $_SESSION['info_mail'] = "Si cet email existe, un lien a été envoyé.";
        header("Location: ../View/FrontOffice/forgot_password.php");
        exit();
    }
}

// 2. Traite le changement de mot de passe
function handleResetPasswordSubmit($utilisateur) {
    $token = $_POST['token'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if ($new_pass !== $confirm_pass) {
        $_SESSION['error_msg'] = "Les mots de passe ne correspondent pas.";
        header("Location: ../View/FrontOffice/reset_password.php?token=$token");
        exit();
    }

    // Vérifier le token
    $user = $utilisateur->getUserByResetToken($token);

    if ($user) {
        // Mettre à jour le mot de passe
        $utilisateur->updatePasswordAfterReset($user['id_utilisateur'], $new_pass);
        
        $_SESSION['success_login'] = "Mot de passe modifié avec succès ! Connectez-vous.";
        header("Location: ../View/FrontOffice/login.php");
        exit();
    } else {
        $_SESSION['error_login'] = "Ce lien de réinitialisation est invalide ou expiré.";
        header("Location: ../View/FrontOffice/login.php");
        exit();
    }
    
    
    
    
}
function handleAdminBanForm($utilisateur) {
    checkAdminAccess();
    $id = $_GET['id'] ?? null;
    if ($id) {
        // On récupère juste l'email pour afficher qui on bannit
        $user = $utilisateur->getBanInfo($id); // Ou une méthode simple getById
        $_SESSION['ban_target_id'] = $id; 
        header("Location: ../View/BackOffice/ban_user.php");
    } else {
        header("Location: ../View/BackOffice/backoffice.php");
    }
    exit();
}

function handleAdminBanSubmit($utilisateur) {
    checkAdminAccess();
    
    $id = $_POST['id_utilisateur'];
    $raison = $_POST['raison'];
    $duree = $_POST['duree']; // en jours

    if ($utilisateur->banUser($id, $raison, $duree)) {
        $_SESSION['success_msg'] = "L'utilisateur a été banni pour $duree jours.";
    } else {
        $_SESSION['error_msg'] = "Erreur lors du bannissement.";
    }

    header("Location: ../View/BackOffice/backoffice.php");
    exit();
}
function handleAdminUnbanUser($utilisateur) {
    checkAdminAccess(); // Sécurité
    
    $id = $_POST['id_utilisateur']; // On récupère l'ID

    // On utilise la méthode unbanUser qui existe déjà dans ton Modèle
    if ($utilisateur->unbanUser($id)) {
        $_SESSION['success_msg'] = "L'utilisateur a été débanni avec succès.";
    } else {
        $_SESSION['error_msg'] = "Erreur lors du débannissement.";
    }

    header("Location: ../View/BackOffice/backoffice.php");
    exit();
}

function handleAjaxGetPhoto($utilisateur) {
    header('Content-Type: application/json');
    $email = $_POST['email'] ?? '';

    // On cherche l'utilisateur
    $user = $utilisateur->findByEmail($email);

    if ($user) {
        // On cherche sa photo (Client ou Orga)
        // Note: On réutilise tes fonctions existantes
        $role = $utilisateur->getUserRole($user['id_utilisateur']);
        $photo = null;

        if ($role === 'client') {
            $data = $utilisateur->findClientById($user['id_utilisateur']);
            $photo = $data['photo_profil'];
        } elseif ($role === 'organisation') {
            $data = $utilisateur->findOrganisationById($user['id_utilisateur']);
            $photo = $data['photo_profil'];
        }

        if ($photo) {
            echo json_encode(['success' => true, 'photo' => $photo]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Pas de photo de profil']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Email inconnu']);
    }
    exit();
}
function handleLoginWithFace($utilisateur) {
    // On dit au navigateur qu'on répond en JSON
    header('Content-Type: application/json');

    $email = $_POST['email'] ?? '';
    
    // 1. On cherche l'utilisateur par son email
    // (On fait confiance au JS qui a déjà vérifié que c'était le bon visage)
    $userFound = $utilisateur->findByEmail($email);
    
    if ($userFound) {
        // 2. On démarre la session si besoin
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 3. On remplit la session (Exactement comme dans handleLogin classique)
        $_SESSION['user_id'] = $userFound['id_utilisateur'];
        $_SESSION['email'] = $userFound['email'];

        // Récupération du rôle
        $role = $utilisateur->getUserRole($userFound['id_utilisateur']);
        $_SESSION['role'] = $role;

        // Récupération du nom pour l'affichage (UX)
        $redirectUrl = "../FrontOffice/index.php"; // Par défaut

        if ($role === 'organisation') {
            $details = $utilisateur->findOrganisationById($userFound['id_utilisateur']);
            $_SESSION['username'] = $details['nom_organisation'];
        } 
        elseif ($role === 'client') {
            $details = $utilisateur->findClientById($userFound['id_utilisateur']);
            $_SESSION['username'] = $details['nom_complet'];
        }
        elseif ($role === 'admin') {
            $_SESSION['username'] = "Administrateur";
            $redirectUrl = "../BackOffice/backoffice.php"; // Admin va au dashboard
        }

        // 4. On répond SUCCÈS au JavaScript
        echo json_encode([
            'success' => true,
            'redirect' => $redirectUrl
        ]);
        exit();

    } else {
        // Erreur (ne devrait pas arriver si l'email était bon au début)
        echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
        exit();
    }
}
?>