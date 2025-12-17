<?php
session_start();

require_once __DIR__ . '/../Model/Database.php';
require_once __DIR__ . '/../Model/Histoire.php';
require_once __DIR__ . '/../Model/Utilisateur.php';
require_once __DIR__ . '/../helpers/content_filter.php';

$database = new Database();
$db = $database->getConnection();
$histoireModel = new Histoire($db);
$utilisateurModel = new Utilisateur($db);

actionRouter($histoireModel);

function ensureLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../View/FrontOffice/login.php');
        exit();
    }
}

function ensureAdmin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: ../View/FrontOffice/login.php');
        exit();
    }
}

function actionRouter($histoireModel) {
    $action = $_POST['action'] ?? $_GET['action'] ?? 'list';

    switch ($action) {
        case 'reclamer':
            handleReclamer($histoireModel);
            break;
        case 'publier':
            handlePublier($histoireModel);
            break;
        case 'react':
            handleReact($histoireModel);
            break;
        case 'commenter':
            handleCommenter($histoireModel);
            break;
        case 'accepter_reclamation':
            handleAccepterReclamation($histoireModel);
            break;
        case 'refuser_reclamation':
            handleRefuserReclamation($histoireModel);
            break;
        case 'admin_delete_story':
            handleAdminDeleteStory($histoireModel);
            break;
        default:
            header('Location: ../View/FrontOffice/histoires.php');
            exit();
    }
}

function resolveCurrentUserId() {
    global $db;
    if (!isset($_SESSION['user_id'])) {
        return 0;
    }

    // For admin logins stored outside Utilisateur, create/reuse a shadow user
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $util = new Utilisateur($db);
        $email = $_SESSION['email'] ?? ('admin+' . $_SESSION['user_id'] . '@peacelink.local');
        $existing = $util->findByEmail($email);
        if ($existing) {
            return (int)$existing['id_utilisateur'];
        }
        $pwd = bin2hex(random_bytes(8));
        $data = [
            'email' => $email,
            'mot_de_passe' => $pwd,
            'role' => 'client',
            'nom_complet' => 'Admin',
            'bio' => ''
        ];
        $createdId = $util->create($data);
        return $createdId ? (int)$createdId : 0;
    }

    return (int)$_SESSION['user_id'];
}

function handleReclamer($histoireModel) {
    ensureLoggedIn();

    $idAuteur = resolveCurrentUserId();
    $idHistoire = (int) ($_POST['id_histoire'] ?? 0);
    $description = filter_content(trim($_POST['description'] ?? ''));
    $causes = $_POST['causes'] ?? [];

    $story = $histoireModel->getStoryById($idHistoire);
    if ($story && (int)$story['id_auteur'] === (int)$idAuteur) {
        $_SESSION['error_msg'] = "Vous ne pouvez pas réclamer votre propre histoire.";
        header('Location: ../View/FrontOffice/histoires.php');
        exit();
    }

    if ($idHistoire <= 0 || $description === '') {
        $_SESSION['error_msg'] = "Veuillez fournir un motif pour la réclamation.";
        header('Location: ../View/FrontOffice/histoires.php');
        exit();
    }

    $success = $histoireModel->addReclamation($idAuteur, $idHistoire, $description, (array)$causes);
    $_SESSION[$success ? 'success_msg' : 'error_msg'] = $success
        ? "Réclamation envoyée. Un administrateur va l'examiner."
        : "Erreur lors de l'envoi de la réclamation.";

    header('Location: ../View/FrontOffice/histoires.php');
    exit();
}

function handlePublier($histoireModel) {
    ensureLoggedIn();

    $idAuteur = resolveCurrentUserId();
    $titre = filter_content(trim($_POST['titre'] ?? ''));
    $contenu = filter_content(trim($_POST['contenu'] ?? ''));

    if ($titre === '' || $contenu === '') {
        $_SESSION['error_msg'] = "Titre et contenu sont requis.";
        header('Location: ../View/FrontOffice/histoires.php');
        exit();
    }

    $success = $histoireModel->addStory($idAuteur, $titre, $contenu);
    $_SESSION[$success ? 'success_msg' : 'error_msg'] = $success
        ? "Votre histoire a été publiée."
        : "Erreur lors de la publication.";

    header('Location: ../View/FrontOffice/histoires.php');
    exit();
}

function handleCommenter($histoireModel) {
    ensureLoggedIn();
    $idHistoire = (int)($_POST['id_histoire'] ?? 0);
    $contenu = filter_content(trim($_POST['contenu'] ?? ''));
    if ($idHistoire <= 0 || $contenu === '') {
        $_SESSION['error_msg'] = "Commentaire vide.";
        header('Location: ../View/FrontOffice/histoires.php');
        exit();
    }
    $userId = resolveCurrentUserId();
    if ($userId <= 0) {
        header('Location: ../View/FrontOffice/login.php');
        exit();
    }
    $ok = $histoireModel->addComment($idHistoire, $userId, $contenu);
    $_SESSION[$ok ? 'success_msg' : 'error_msg'] = $ok ? "Commentaire publié." : "Erreur lors de l'ajout du commentaire.";
    header('Location: ../View/FrontOffice/histoires.php');
    exit();
}

function handleAccepterReclamation($histoireModel) {
    ensureAdmin();
    $idReclamation = (int) ($_POST['id_reclamation'] ?? 0);
    if ($idReclamation <= 0) {
        header('Location: ../View/BackOffice/reclamations.php');
        exit();
    }

    $reclamation = $histoireModel->getReclamation($idReclamation);
    if ($reclamation && $reclamation['id_histoire_cible']) {
        $histoireModel->deleteStory($reclamation['id_histoire_cible']);
    }
    $histoireModel->updateReclamationStatus($idReclamation, 'acceptee');
    $_SESSION['success_msg'] = "Réclamation acceptée et histoire supprimée.";
    header('Location: ../View/BackOffice/reclamations.php');
    exit();
}

function handleRefuserReclamation($histoireModel) {
    ensureAdmin();
    $idReclamation = (int) ($_POST['id_reclamation'] ?? 0);
    if ($idReclamation <= 0) {
        header('Location: ../View/BackOffice/reclamations.php');
        exit();
    }

    $histoireModel->updateReclamationStatus($idReclamation, 'refusee');
    $_SESSION['success_msg'] = "Réclamation refusée.";
    header('Location: ../View/BackOffice/reclamations.php');
    exit();
}

function handleAdminDeleteStory($histoireModel) {
    ensureAdmin();
    $idStory = (int)($_POST['id_histoire'] ?? 0);
    if ($idStory <= 0) {
        $_SESSION['error_msg'] = "ID histoire invalide.";
        header('Location: ../View/BackOffice/backoffice.php#stories');
        exit();
    }

    $ok = $histoireModel->deleteStory($idStory);
    $_SESSION[$ok ? 'success_msg' : 'error_msg'] = $ok
        ? "Histoire supprimée."
        : "Erreur lors de la suppression.";

    header('Location: ../View/BackOffice/backoffice.php#stories');
    exit();
}

function handleReact($histoireModel) {
    ensureLoggedIn();
    $idHistoire = (int)($_POST['id_histoire'] ?? 0);
    $type = $_POST['type'] ?? '';
    $allowed = ['like','dislike','love','laugh','angry'];
    if ($idHistoire <= 0 || !in_array($type, $allowed, true)) {
        header('Location: ../View/FrontOffice/histoires.php');
        exit();
    }

    $userId = resolveCurrentUserId();
    if ($userId <= 0) {
        header('Location: ../View/FrontOffice/login.php');
        exit();
    }
    $histoireModel->toggleReaction($idHistoire, $userId, $type);
    header('Location: ../View/FrontOffice/histoires.php');
    exit();
}
?>
