<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../../Model/Database.php';
require_once __DIR__ . '/../../Model/Message.php';

$database = new Database();
$db = $database->getConnection();
$messageModel = new Message($db);

$idClient = (int)$_SESSION['user_id'];
$conversations = $messageModel->getClientConversations($idClient);

// Récupérer les messages pour chaque conversation et marquer comme lus
$conversationsWithMessages = [];
foreach ($conversations as $conv) {
    $messages = $messageModel->getConversationMessages($conv['id_expert'], $idClient);
    $messageModel->markAsRead($conv['id_conversation'], $idClient);
    $conversationsWithMessages[] = [
        'conversation' => $conv,
        'messages' => $messages
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Messages - PeaceLink</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fb; }
        .messages-container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        .page-header { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); margin-bottom: 30px; }
        .page-header h1 { color: #2c3e50; margin: 0 0 10px 0; }
        .page-header p { color: #7f8c8d; margin: 0; }
        
        /* Conversations */
        .conversations-list { display: flex; flex-direction: column; gap: 20px; }
        .conversation-card { background: white; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); padding: 25px; }
        .conversation-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e1e8ed; }
        .expert-info { display: flex; align-items: center; gap: 15px; }
        .expert-avatar { width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #5dade2, #3498db); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2em; }
        .expert-name { color: #2c3e50; font-size: 1.3em; font-weight: bold; }
        .expert-specialite { color: #7f8c8d; font-size: 0.9em; }
        
        .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 0.85em; font-weight: bold; }
        .status-ouverte { background: #d4edda; color: #155724; }
        .status-fermee { background: #f8d7da; color: #721c24; }
        .message-count { color: #7f8c8d; font-size: 0.9em; margin-left: 10px; }
        
        /* Messages */
        .messages-list { display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; max-height: 400px; overflow-y: auto; padding: 15px; background: #f8f9fa; border-radius: 8px; }
        .message { display: flex; gap: 12px; max-width: 80%; }
        .message-expert { align-self: flex-end; flex-direction: row-reverse; }
        .message-client { align-self: flex-start; }
        .message-avatar { width: 35px; height: 35px; border-radius: 50%; background: #94a3b8; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.9em; flex-shrink: 0; }
        .message-expert .message-avatar { background: #2563eb; }
        .message-content { padding: 12px 16px; border-radius: 14px; border: 1px solid #e5e7eb; background: #e5e7eb; color: #1f2937; }
        .message-expert .message-content { background: #2563eb; color: #fff; border: none; }
        .message-text { margin-bottom: 5px; line-height: 1.45; }
        .message-time { font-size: 0.75em; color: #6b7280; }
        .message-expert .message-time { color: rgba(255,255,255,0.85); }
        
        /* Formulaire de réponse */
        .reply-form { display: flex; gap: 12px; align-items: center; }
        .reply-form textarea { flex: 1; padding: 12px; border: 2px solid #e1e8ed; border-radius: 20px; resize: none; min-height: 50px; font-family: inherit; }
        .reply-form textarea:focus { outline: none; border-color: #5dade2; }
        .reply-form button { background: #5dade2; color: white; border: none; padding: 12px 24px; border-radius: 20px; font-weight: bold; cursor: pointer; transition: all 0.3s; }
        .reply-form button:hover { background: #3498db; transform: translateY(-2px); }
        .reply-form button:disabled { background: #95a5a6; cursor: not-allowed; transform: none; }
        
        .empty-state { text-align: center; padding: 60px 20px; color: #95a5a6; }
        .empty-state i { font-size: 4em; margin-bottom: 20px; }
    </style>
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <div class="messages-container" style="margin-top: 150px !important;">
        <div class="page-header">
            <h1><i class="fas fa-envelope-open"></i> Mes Messages</h1>
            <p>Conversations avec les experts</p>
        </div>

        <div class="conversations-list">
            <?php if (empty($conversationsWithMessages)): ?>
                <div class="conversation-card">
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h2>Aucun message</h2>
                        <p>Vous n'avez pas encore reçu de messages d'experts.</p>
                        <p style="margin-top: 10px; font-size: 0.9em;">Les experts peuvent vous contacter après avoir lu vos histoires.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($conversationsWithMessages as $convData): 
                    $conv = $convData['conversation'];
                    $messages = $convData['messages'];
                    $idExpert = $conv['id_expert'];
                ?>
                    <div class="conversation-card">
                        <div class="conversation-header">
                            <div class="expert-info">
                                <div class="expert-avatar">
                                    <?= strtoupper(substr($conv['expert_nom'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="expert-name"><?= htmlspecialchars($conv['expert_nom']) ?></div>
                                    <div class="expert-specialite">
                                        <i class="fas fa-star"></i> 
                                        <?= htmlspecialchars($conv['expert_specialite'] ?? 'Expert') ?>
                                    </div>
                                    <span class="message-count">
                                        <i class="fas fa-envelope"></i> 
                                        <?= $conv['message_count'] ?> messages
                                    </span>
                                </div>
                            </div>
                            <span class="status-badge status-<?= $conv['statut'] ?>">
                                <?= $conv['statut'] === 'ouverte' ? 'Ouverte' : 'Fermée' ?>
                            </span>
                        </div>

                        <div class="messages-list" id="messages-<?= $conv['id_conversation'] ?>">
                            <?php foreach ($messages as $msg): ?>
                                <?php
                                    $senderRole = $msg['sender_role'] ?? (($msg['id_expert'] == $idExpert) ? 'expert' : 'client');
                                    $senderName = $senderRole === 'expert' ? ($msg['expert_nom'] ?? 'E') : ($msg['client_nom'] ?? 'C');
                                    $senderInitial = strtoupper(substr($senderName, 0, 1));
                                ?>
                                <div class="message <?= $senderRole === 'expert' ? 'message-expert' : 'message-client' ?>">
                                    <div class="message-avatar">
                                        <?= htmlspecialchars($senderInitial) ?>
                                    </div>
                                    <div class="message-content">
                                        <div class="message-text"><?= nl2br(htmlspecialchars($msg['contenu'])) ?></div>
                                        <div class="message-time">
                                            <?= date('d/m/Y H:i', strtotime($msg['date_envoi'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <form class="reply-form" onsubmit="sendReply(event, <?= $idExpert ?>, <?= $conv['id_conversation'] ?>)">
                            <textarea name="contenu" placeholder="Écrivez votre message..." required></textarea>
                            <button type="submit">
                                <i class="fas fa-paper-plane"></i> Envoyer
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function sendReply(event, expertId, conversationId) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            formData.append('action', 'reply_message');
            formData.append('id_expert', expertId);
            formData.append('id_client', <?= $idClient ?>);
            
            const btn = form.querySelector('button');
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
        }

        // Scroll automatique vers le bas des messages
        document.querySelectorAll('.messages-list').forEach(list => {
            list.scrollTop = list.scrollHeight;
        });
    </script>

    <?php include 'partials/footer.php'; ?>
</body>
</html>
