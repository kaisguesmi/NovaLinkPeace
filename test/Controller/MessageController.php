<?php
// test/Controller/MessageController.php
session_start();

require_once __DIR__ . '/../Model/Database.php';
require_once __DIR__ . '/../Model/Message.php';

$database = new Database();
$db = $database->getConnection();
$messageModel = new Message($db);

$action = $_POST['action'] ?? $_GET['action'] ?? 'list';

// Router
switch ($action) {
    case 'expert_stories':
        handleExpertStories($messageModel);
        break;
        
    case 'send_message':
        handleSendMessage($messageModel);
        break;
        
    case 'client_conversations':
        handleClientConversations($messageModel);
        break;
        
    case 'view_conversation':
        handleViewConversation($messageModel);
        break;
        
    case 'get_unread_count':
        handleGetUnreadCount($messageModel);
        break;
        
    case 'expert_conversations':
        handleExpertConversations($messageModel);
        break;
    
    case 'reply_message':
        handleReplyMessage($messageModel);
        break;
        
    default:
        header("Location: ../View/FrontOffice/index.php");
        exit();
}

/**
 * Afficher les histoires pour que l'expert puisse contacter les clients
 */
function handleExpertStories($messageModel) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
        $_SESSION['error_msg'] = "Accès réservé aux experts.";
        header("Location: ../View/FrontOffice/login.php");
        exit();
    }
    
    $stories = $messageModel->getAllStoriesForExperts();
    $_SESSION['expert_stories'] = $stories;
    
    header("Location: ../View/FrontOffice/expert_stories.php");
    exit();
}

/**
 * Envoyer un message d'un expert vers un client
 */
function handleSendMessage($messageModel) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
        echo json_encode(['success' => false, 'error' => 'Non autorisé']);
        exit();
    }
    
    $idClient = (int)($_POST['id_client'] ?? 0);
    $contenu = trim($_POST['contenu'] ?? '');
    $idHistoire = isset($_POST['id_histoire']) ? (int)$_POST['id_histoire'] : null;
    
    if ($idClient <= 0 || $contenu === '') {
        echo json_encode(['success' => false, 'error' => 'Données invalides']);
        exit();
    }
    
    $idExpert = (int)$_SESSION['user_id'];
    
    $success = $messageModel->sendMessage($idExpert, $idClient, $contenu, $idHistoire);
    
    if ($success) {
        $_SESSION['success_msg'] = "Message envoyé avec succès !";
        echo json_encode(['success' => true, 'redirect' => '../View/FrontOffice/expert_messages_unified.php']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'envoi ou limite de 5 messages atteinte']);
    }
    exit();
}

/**
 * Afficher les conversations d'un client
 */
function handleClientConversations($messageModel) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
        $_SESSION['error_msg'] = "Accès réservé aux clients.";
        header("Location: ../View/FrontOffice/login.php");
        exit();
    }
    
    // Rediriger directement vers la page unifiée
    header("Location: ../View/FrontOffice/client_messages_unified.php");
    exit();
}

/**
 * Voir une conversation spécifique
 */
function handleViewConversation($messageModel) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../View/FrontOffice/login.php");
        exit();
    }
    
    $role = $_SESSION['role'];
    $userId = (int)$_SESSION['user_id'];
    
    if ($role === 'expert') {
        $idExpert = $userId;
        $idClient = (int)($_GET['id_client'] ?? 0);
        
        if ($idClient <= 0) {
            $_SESSION['error_msg'] = "Client invalide.";
            header("Location: ../View/FrontOffice/expert_stories.php");
            exit();
        }
        
        $messages = $messageModel->getConversationMessages($idExpert, $idClient);
        $_SESSION['conversation_messages'] = $messages;
        $_SESSION['conversation_client_id'] = $idClient;
        
        header("Location: ../View/FrontOffice/conversation.php");
        
    } elseif ($role === 'client') {
        $idClient = $userId;
        $idExpert = (int)($_GET['id_expert'] ?? 0);
        
        if ($idExpert <= 0) {
            $_SESSION['error_msg'] = "Expert invalide.";
            header("Location: ../View/FrontOffice/client_messages.php");
            exit();
        }
        
        // Marquer les messages comme lus
        $idConv = $messageModel->getOrCreateConversation($idExpert, $idClient);
        $messageModel->markAsRead($idConv, $idClient);
        
        $messages = $messageModel->getConversationMessages($idExpert, $idClient);
        $_SESSION['conversation_messages'] = $messages;
        $_SESSION['conversation_expert_id'] = $idExpert;
        
        header("Location: ../View/FrontOffice/conversation.php");
        
    } else {
        header("Location: ../View/FrontOffice/index.php");
    }
    
    exit();
}

/**
 * Récupérer le nombre de messages non lus (AJAX)
 */
function handleGetUnreadCount($messageModel) {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
        echo json_encode(['count' => 0]);
        exit();
    }
    
    $idClient = (int)$_SESSION['user_id'];
    $count = $messageModel->getUnreadCount($idClient);
    
    echo json_encode(['count' => $count]);
    exit();
}

/**
 * Afficher les conversations d'un expert
 */
function handleExpertConversations($messageModel) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
        $_SESSION['error_msg'] = "Accès réservé aux experts.";
        header("Location: ../View/FrontOffice/login.php");
        exit();
    }
    
    // Rediriger directement vers la page unifiée
    header("Location: ../View/FrontOffice/expert_messages_unified.php");
    exit();
}

/**
 * Répondre dans une conversation (client ou expert)
 */
function handleReplyMessage($messageModel) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Non autorisé']);
        exit();
    }
    
    $role = $_SESSION['role'];
    $userId = (int)$_SESSION['user_id'];
    $contenu = trim($_POST['contenu'] ?? '');
    
    if ($contenu === '') {
        echo json_encode(['success' => false, 'error' => 'Message vide']);
        exit();
    }
    
    if ($role === 'expert') {
        $idExpert = $userId;
        $idClient = (int)($_POST['id_client'] ?? 0);
        
        if ($idClient <= 0) {
            echo json_encode(['success' => false, 'error' => 'Client invalide']);
            exit();
        }
        
        $success = $messageModel->sendMessage($idExpert, $idClient, $contenu, null);
        
    } elseif ($role === 'client') {
        $idClient = $userId;
        $idExpert = (int)($_POST['id_expert'] ?? 0);
        
        if ($idExpert <= 0) {
            echo json_encode(['success' => false, 'error' => 'Expert invalide']);
            exit();
        }
        
        $success = $messageModel->sendMessage($idExpert, $idClient, $contenu, null);
        
    } else {
        echo json_encode(['success' => false, 'error' => 'Rôle non autorisé']);
        exit();
    }
    
    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'envoi']);
    }
    exit();
}
?>
