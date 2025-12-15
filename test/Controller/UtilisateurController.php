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
        handleAjaxGetPhoto($utilisateur, $db);
        break;
    
    case 'login_with_face':
        handleLoginWithFace($utilisateur, $db);
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
        // Tous les rôles restent sur la page principale après inscription
        header("Location: ../View/FrontOffice/index.php");
        exit();

    } else {
        // En cas d'échec (ex: email déjà pris)
        $_SESSION['errors'] = ["Une erreur est survenue (Email déjà utilisé ?)."];
        header("Location: ../View/FrontOffice/inscription.php");
        exit();
    }
}

function handleLogin($utilisateur) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['mot_de_passe'] ?? '');

    if ($email === '' || $password === '') {
        $_SESSION['error_login'] = "Veuillez remplir tous les champs.";
        header("Location: ../View/FrontOffice/login.php");
        exit();
    }

    // --- CAS ADMIN AUTONOME (table admin sans lien utilisateur) ---
    $adminFound = $utilisateur->findAdminByEmail($email);
    if ($adminFound && password_verify($password, $adminFound['mot_de_passe_hash'])) {
        $_SESSION['user_id'] = $adminFound['id_admin'];
        $_SESSION['email'] = $adminFound['email'];
        $_SESSION['role'] = 'admin';
        $_SESSION['username'] = 'Administrateur';
        // Rester sur la page principale
        header("Location: ../View/FrontOffice/index.php");
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
        elseif ($role === 'expert') {
            $expertDetails = $utilisateur->findExpertById($userFound['id_utilisateur']);
            $_SESSION['username'] = $expertDetails['nom_complet'];
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

    // 1. Mise à jour des textes (Nom, Bio, Adresse...)
    if ($role === 'client') {
        $nom_complet = $_POST['nom_complet'] ?? '';
        $bio = $_POST['bio'] ?? '';
        if ($utilisateur->updateClient($id, $nom_complet, $bio)) {
            $_SESSION['username'] = $nom_complet;
        }
    } 
    elseif ($role === 'expert') {
        $nom_complet = $_POST['nom_complet'] ?? '';
        $bio = $_POST['bio'] ?? '';
        $specialite = $_POST['specialite'] ?? '';
        if ($utilisateur->updateExpert($id, $nom_complet, $bio, $specialite)) {
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

    // 2. GESTION DE LA PHOTO (Nouveau)
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['photo_profil']['tmp_name'];
        $fileName = $_FILES['photo_profil']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Extensions autorisées
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Création d'un nom unique pour éviter les doublons
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            
            // Dossier de destination (Assure-toi que ce dossier existe !)
            $uploadFileDir = __DIR__ . '/../View/uploads/';
            
            // Créer le dossier s'il n'existe pas
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $dest_path = $uploadFileDir . $newFileName;

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                // Sauvegarde en BDD
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
    } elseif ($role === 'expert') {
        $profileData = $utilisateur->findExpertById($id);
        $profileData['role_display'] = 'Expert ⭐';
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
    require_once __DIR__ . '/../../../model/EmailService.php';
    
    $email = $_POST['email'] ?? '';

    // Vérifie si l'email existe
    $user = $utilisateur->findByEmail($email);

    if ($user) {
        // 1. Générer le token
        $token = bin2hex(random_bytes(32));
        $utilisateur->setResetToken($email, $token);

        // 2. Préparer le lien
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $server = $_SERVER['HTTP_HOST'];
        $folder = "/integration/NovaLinkPeace/test";
        
        $resetLink = $protocol . "://" . $server . $folder . "/View/FrontOffice/reset_password.php?token=" . $token;

        // 3. Récupérer le nom de l'utilisateur
        $userName = $user['username'] ?? 'Utilisateur';

        // 4. Envoyer l'email professionnel avec EmailService
        if (EmailService::sendPasswordResetEmail($email, $userName, $resetLink)) {
            $_SESSION['info_mail'] = "✅ Un email de réinitialisation a été envoyé à <strong>$email</strong>. Vérifiez votre boîte de réception et vos spams !";
        } else {
            $_SESSION['info_mail'] = "❌ Erreur technique : L'email n'a pas pu être envoyé. Vérifiez la configuration XAMPP.";
        }
        
        header("Location: ../View/FrontOffice/forgot_password.php");
        exit();

    } else {
        // Pour des raisons de sécurité, on affiche le même message
        $_SESSION['info_mail'] = "✅ Si cet email existe dans notre système, un lien de réinitialisation a été envoyé.";
        header("Location: ../View/FrontOffice/forgot_password.php");
        exit();
    }
}

// 2. Traite le changement de mot de passe
function handleResetPasswordSubmit($utilisateur) {
    $token = $_POST['token'] ?? '';
    $new_pass = trim($_POST['new_password'] ?? '');
    $confirm_pass = trim($_POST['confirm_password'] ?? '');

    if (strlen($new_pass) < 6) {
        $_SESSION['error_msg'] = "Le mot de passe doit contenir au moins 6 caractères.";
        header("Location: ../View/FrontOffice/reset_password.php?token=$token");
        exit();
    }

    if ($new_pass !== $confirm_pass) {
        $_SESSION['error_msg'] = "Les mots de passe ne correspondent pas.";
        header("Location: ../View/FrontOffice/reset_password.php?token=$token");
        exit();
    }

    // Vérifier le token
    $user = $utilisateur->getUserByResetToken($token);

    if ($user) {
        // Mettre à jour le mot de passe
        $updated = $utilisateur->updatePasswordAfterReset($user['id_utilisateur'], $new_pass);

        if (!$updated) {
            $_SESSION['error_login'] = "Une erreur est survenue lors de la mise à jour du mot de passe. Veuillez réessayer.";
            header("Location: ../View/FrontOffice/reset_password.php?token=$token");
            exit();
        }

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

// =========================================================
// 🎭 GESTION FACE ID
// =========================================================

/**
 * Récupère la photo de profil d'un utilisateur pour Face ID
 * Retourne JSON avec le chemin de la photo
 */
function handleAjaxGetPhoto($utilisateur, $db) {
    // Vider tous les buffers existants
    while (ob_get_level()) ob_end_clean();
    
    // Désactiver l'affichage des erreurs
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    
    // Log pour débogage
    error_log("=== AJAX GET PHOTO ===");
    error_log("POST data: " . print_r($_POST, true));
    
    try {
        header('Content-Type: application/json');

        $email = trim($_POST['email'] ?? '');
        $adminPhotoFilename = 'admin_face_id.jpg'; // fichier attendu dans View/uploads

        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Email requis']);
            exit();
        }

        // Identifier le compte (Utilisateur ou Admin autonome)
        $role = null;
        $userId = null;
        $photoFile = null;
        $displayName = null;

        $user = $utilisateur->findByEmail($email);
        if ($user) {
            $userId = (int) $user['id_utilisateur'];
            $role = $utilisateur->getUserRole($userId);
        } else {
            $admin = $utilisateur->findAdminByEmail($email);
            if ($admin) {
                $role = 'admin';
                $userId = (int) $admin['id_admin'];
            }
        }

        if (!$role) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable']);
            exit();
        }

        // Autoriser clients, experts et administrateurs
        if (!in_array($role, ['client', 'expert', 'admin'], true)) {
            echo json_encode(['success' => false, 'message' => 'Face ID disponible pour les clients, experts et administrateurs']);
            exit();
        }

        // Récupérer la photo selon le rôle
        if ($role === 'admin') {
            $photoFile = $adminPhotoFilename;
            $displayName = 'Administrateur';
        } elseif ($role === 'expert') {
            $stmt = $db->prepare("
                SELECT u.photo_profil, e.nom_complet AS display_name
                FROM Utilisateur u
                JOIN Expert e ON u.id_utilisateur = e.id_utilisateur
                WHERE u.id_utilisateur = ?
            ");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$userData || empty($userData['photo_profil'])) {
                echo json_encode(['success' => false, 'message' => 'Aucune photo de profil trouvée pour cet expert.']);
                exit();
            }

            $photoFile = $userData['photo_profil'];
            $displayName = $userData['display_name'] ?? $email;
        } else { // client par défaut
            $stmt = $db->prepare("
                SELECT u.photo_profil, c.nom_complet AS display_name
                FROM Utilisateur u
                JOIN Client c ON u.id_utilisateur = c.id_utilisateur
                WHERE u.id_utilisateur = ?
            ");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$userData || empty($userData['photo_profil'])) {
                echo json_encode(['success' => false, 'message' => 'Aucune photo de profil trouvée. Veuillez ajouter une photo dans votre profil.']);
                exit();
            }

            $photoFile = $userData['photo_profil'];
            $displayName = $userData['display_name'] ?? $email;
        }

        // Vérifier que le fichier existe
        $photoPath = __DIR__ . '/../View/uploads/' . $photoFile;
        error_log("Chemin photo: " . $photoPath);
        error_log("Fichier existe: " . (file_exists($photoPath) ? 'OUI' : 'NON'));

        if (!file_exists($photoPath)) {
            $message = ($role === 'admin')
                ? 'Photo Face ID admin introuvable dans View/uploads (' . $photoFile . ').'
                : 'Fichier photo introuvable';
            echo json_encode(['success' => false, 'message' => $message]);
            exit();
        }

        echo json_encode([
            'success' => true,
            'photo' => $photoFile,
            'nom_complet' => $displayName
        ]);
        exit();
        
    } catch (Exception $e) {
        error_log("Exception dans handleAjaxGetPhoto: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
        exit();
    }
}

/**
 * Connexion avec Face ID après reconnaissance faciale réussie
 */
function handleLoginWithFace($utilisateur, $db) {
    // Vider tous les buffers existants et en démarrer un nouveau
    while (ob_get_level()) ob_end_clean();
    
    // Désactiver l'affichage des erreurs (elles iront dans error_log)
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    
    error_log("=== LOGIN WITH FACE ===");
    error_log("POST data: " . print_r($_POST, true));
    
    try {
        header('Content-Type: application/json');

        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            error_log("Erreur: Email vide");
            echo json_encode(['success' => false, 'message' => 'Email requis']);
            exit();
        }

        // Identifier le compte (Utilisateur ou Admin autonome)
        $role = null;
        $userId = null;
        $username = null;

        $user = $utilisateur->findByEmail($email);
        if ($user) {
            $userId = (int) $user['id_utilisateur'];
            $role = $utilisateur->getUserRole($userId);
            error_log("Utilisateur trouvé: ID " . $userId . " | rôle " . $role);
        } else {
            $admin = $utilisateur->findAdminByEmail($email);
            if ($admin) {
                $role = 'admin';
                $userId = (int) $admin['id_admin'];
                error_log("Compte admin autonome trouvé: ID " . $userId);
            }
        }

        if (!$role) {
            error_log("Erreur: Utilisateur introuvable pour email: " . $email);
            echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable']);
            exit();
        }

        if (!in_array($role, ['client', 'expert', 'admin'], true)) {
            error_log("Erreur: Rôle non autorisé pour Face ID: " . $role);
            echo json_encode(['success' => false, 'message' => 'Face ID disponible pour les clients, experts et administrateurs']);
            exit();
        }

        // Préparer les infos de session selon le rôle
        switch ($role) {
            case 'expert':
                $expertDetails = $utilisateur->findExpertById($userId);
                $username = $expertDetails['nom_complet'] ?? 'Expert';
                break;
            case 'client':
                $clientDetails = $utilisateur->findClientById($userId);
                $username = $clientDetails['nom_complet'] ?? 'Client';
                break;
            case 'admin':
                $username = 'Administrateur';
                break;
        }

        $_SESSION['user_id'] = $userId;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;
        $_SESSION['username'] = $username;

        error_log("Session créée - user_id: " . $_SESSION['user_id']);
        error_log("Session role: " . $_SESSION['role']);

        // Déterminer la redirection selon le rôle
        $redirect = "index.php";
        if ($role === 'admin') {
            $redirect = "../BackOffice/backoffice.php";
        }

        error_log("Redirection vers: " . $redirect);

        echo json_encode([
            'success' => true,
            'message' => 'Connexion réussie avec Face ID !',
            'redirect' => $redirect
        ]);
        exit();
        
    } catch (Exception $e) {
        error_log("Exception dans handleLoginWithFace: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
        exit();
    }
}
?>