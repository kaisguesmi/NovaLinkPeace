<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$messages = $_SESSION['conversation_messages'] ?? [];
unset($_SESSION['conversation_messages']);

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Déterminer l'interlocuteur
if ($role === 'expert') {
    $clientId = $_SESSION['conversation_client_id'] ?? null;
    $expertId = $userId;
} else {
    $expertId = $_SESSION['conversation_expert_id'] ?? null;
    $clientId = $userId;
}

if (empty($messages)) {
    header("Location: index.php");
    exit();
}

$interlocuteur = $role === 'expert' ? $messages[0]['client_nom'] : $messages[0]['expert_nom'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation avec <?= htmlspecialchars($interlocuteur) ?></title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f5f7fb;
        }

        .conversation-wrapper {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .conversation-header {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .interlocuteur-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #5dade2, #3498db);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.3em;
        }

        .conversation-header h1 {
            color: #2c3e50;
            font-size: 1.5em;
            margin: 0;
        }

        .btn-back {
            background: #5dade2;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-back:hover {
            background: #3498db;
            transform: translateY(-2px);
        }

        .conversation-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 250px);
            overflow: hidden;
        }

        .messages-list {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
            background: #f8f9fa;
        }

        .message {
            display: flex;
            gap: 15px;
            max-width: 70%;
        }

        .message.sent {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .message.received {
            align-self: flex-start;
        }

        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #5dade2, #3498db);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }

        .message.sent .message-avatar {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
        }

        .message-content {
            background: #ffffff;
            padding: 15px 20px;
            border-radius: 15px;
            position: relative;
            border: 1px solid #e1e8ed;
        }

        .message.sent .message-content {
            background: #5dade2;
            color: white;
            border: none;
        }

        .message-text {
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .message-meta {
            font-size: 0.75em;
            opacity: 0.7;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .message.sent .message-meta {
            justify-content: flex-end;
        }

        .histoire-reference {
            background: rgba(255,255,255,0.15);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 0.9em;
            font-style: italic;
        }

        .message.received .histoire-reference {
            background: #e9ecef;
        }

        .message-form {
            padding: 20px 30px;
            border-top: 2px solid #e1e8ed;
            display: flex;
            gap: 15px;
            background: white;
        }

        .message-form textarea {
            flex: 1;
            padding: 15px;
            border: 2px solid #e1e8ed;
            border-radius: 25px;
            font-family: inherit;
            font-size: 1em;
            resize: none;
            min-height: 50px;
            max-height: 150px;
        }

        .message-form textarea:focus {
            outline: none;
            border-color: #5dade2;
        }

        .btn-send {
            background: #5dade2;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-send:hover {
            transform: scale(1.05);
        .btn-send:hover {
            background: #3498db;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(93, 173, 226, 0.4);
        }

        .btn-send:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Scrollbar personnalisée */
        .messages-list::-webkit-scrollbar {
            width: 8px;
        }

        .messages-list::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .messages-list::-webkit-scrollbar-thumb {
            background: #5dade2;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <div class="conversation-wrapper">
        <div class="conversation-header">
            <div class="header-left">
                <div class="interlocuteur-avatar">
                    <?= strtoupper(substr($interlocuteur, 0, 1)) ?>
                </div>
                <h1><?= htmlspecialchars($interlocuteur) ?></h1>
            </div>
            <a href="<?= $role === 'expert' ? '../../Controller/MessageController.php?action=expert_conversations' : '../../Controller/MessageController.php?action=client_conversations' ?>" 
               class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>

        <div class="conversation-container">
        <div class="messages-list" id="messagesList">
            <?php foreach ($messages as $msg): ?>
                <?php 
                    $isSent = ($role === 'expert' && $msg['id_expert'] == $userId) || 
                              ($role === 'client' && $msg['id_client'] == $userId);
                    $messageClass = $isSent ? 'sent' : 'received';
                    $authorName = $isSent ? 'Vous' : $interlocuteur;
                ?>
                <div class="message <?= $messageClass ?>">
                    <div class="message-avatar">
                        <?= strtoupper(substr($authorName, 0, 1)) ?>
                    </div>
                    <div class="message-content">
                        <?php if ($msg['id_histoire'] && $msg['histoire_titre']): ?>
                            <div class="histoire-reference">
                                <i class="fas fa-book"></i> À propos de : "<?= htmlspecialchars($msg['histoire_titre']) ?>"
                            </div>
                        <?php endif; ?>
                        
                        <div class="message-text">
                            <?= nl2br(htmlspecialchars($msg['contenu'])) ?>
                        </div>
                        
                        <div class="message-meta">
                            <i class="far fa-clock"></i>
                            <?= date('d/m/Y à H:i', strtotime($msg['date_envoi'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <form class="message-form" id="replyForm">
            <input type="hidden" name="action" value="reply_message">
            <input type="hidden" name="id_client" value="<?= $clientId ?>">
            <input type="hidden" name="id_expert" value="<?= $expertId ?>">
            
            <textarea 
                name="contenu" 
                placeholder="Écrivez votre message..." 
                required
                id="messageInput"
            ></textarea>
            
            <button type="submit" class="btn-send">
                <i class="fas fa-paper-plane"></i>
                Envoyer
            </button>
        </form>
    </div>

    <script>
        // Scroll automatique vers le bas
        const messagesList = document.getElementById('messagesList');
        messagesList.scrollTop = messagesList.scrollHeight;

        // Soumission du formulaire
        document.getElementById('replyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const btn = this.querySelector('.btn-send');
            btn.disabled = true;
            
            fetch('../../Controller/MessageController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur: ' + (data.error || 'Impossible d\'envoyer le message'));
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue');
                btn.disabled = false;
            });
        });

        // Auto-resize du textarea
        const textarea = document.getElementById('messageInput');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 150) + 'px';
        });
    </script>

    <?php include 'partials/footer.php'; ?>
</body>
</html>
