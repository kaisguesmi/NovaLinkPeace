<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../../Model/Database.php';
require_once __DIR__ . '/../../Model/Message.php';

$database = new Database();
$db = $database->getConnection();
$messageModel = new Message($db);

$idExpert = (int)$_SESSION['user_id'];
$conversations = $messageModel->getExpertConversations($idExpert);
$allStories = $messageModel->getAllStoriesForExperts();

// Récupérer les messages pour chaque conversation
$conversationsWithMessages = [];
foreach ($conversations as $conv) {
    $messages = $messageModel->getConversationMessages($idExpert, $conv['id_client']);
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
    <title>Messagerie Expert - PeaceLink</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fb; }
        .messages-container { max-width: 1400px; margin: 40px auto; padding: 0 20px; }
        .page-header { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); margin-bottom: 30px; }
        .page-header h1 { color: #2c3e50; margin: 0 0 10px 0; }
        .page-header p { color: #7f8c8d; margin: 0; }
        
        .content-grid { display: grid; grid-template-columns: 350px 1fr; gap: 30px; }
        
        /* Histoires disponibles */
        .stories-sidebar { background: white; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); padding: 20px; max-height: calc(100vh - 250px); overflow-y: auto; }
        .stories-sidebar h2 { color: #2c3e50; font-size: 1.2em; margin: 0 0 20px 0; }
        .story-item { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px; cursor: pointer; transition: all 0.3s; }
        .story-item:hover { background: #e9ecef; transform: translateX(5px); }
        .story-author { font-weight: bold; color: #2c3e50; margin-bottom: 5px; }
        .story-title { color: #5dade2; font-size: 0.95em; margin-bottom: 5px; }
        .story-excerpt { color: #7f8c8d; font-size: 0.85em; }
        .btn-contact-story { background: #5dade2; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; margin-top: 10px; width: 100%; }
        .btn-contact-story:hover { background: #3498db; }
        
        /* Conversations */
        .conversations-main { display: flex; flex-direction: column; gap: 20px; }
        .conversation-card { background: white; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); padding: 25px; }
        .conversation-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e1e8ed; }
        .client-info { display: flex; align-items: center; gap: 15px; }
        .client-avatar { width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #5dade2, #3498db); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2em; }
        .client-name { color: #2c3e50; font-size: 1.3em; font-weight: bold; }
        
        .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 0.85em; font-weight: bold; }
        .status-ouverte { background: #d4edda; color: #155724; }
        .status-fermee { background: #f8d7da; color: #721c24; }
        .message-count { color: #7f8c8d; font-size: 0.9em; margin-left: 10px; }
        
        /* Messages */
        .messages-list { display: flex; flex-direction: column; gap: 15px; margin-bottom: 20px; max-height: 400px; overflow-y: auto; padding: 15px; background: #f8f9fa; border-radius: 8px; }
        .message { display: flex; gap: 12px; max-width: 75%; }
        .message-expert { align-self: flex-end; flex-direction: row-reverse; }
        .message-client { align-self: flex-start; }
        .message-avatar { width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, #5dade2, #3498db); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.9em; flex-shrink: 0; }
        .message-expert .message-avatar { background: linear-gradient(135deg, #2ecc71, #27ae60); }
        .message-content { background: white; padding: 12px 16px; border-radius: 12px; border: 1px solid #e1e8ed; }
        .message-expert .message-content { background: #5dade2; color: white; border: none; }
        .message-text { margin-bottom: 5px; line-height: 1.4; }
        .message-time { font-size: 0.75em; color: #95a5a6; }
        .message-expert .message-time { color: rgba(255,255,255,0.8); }
        
        /* Formulaire de réponse */
        .reply-form { display: flex; gap: 12px; align-items: center; }
        .reply-form textarea { flex: 1; padding: 12px; border: 2px solid #e1e8ed; border-radius: 20px; resize: none; min-height: 50px; font-family: inherit; }
        .reply-form textarea:focus { outline: none; border-color: #5dade2; }
        .reply-form button { background: #5dade2; color: white; border: none; padding: 12px 24px; border-radius: 20px; font-weight: bold; cursor: pointer; transition: all 0.3s; }
        .reply-form button:hover { background: #3498db; transform: translateY(-2px); }
        .reply-form button:disabled { background: #95a5a6; cursor: not-allowed; transform: none; }
        
        .empty-state { text-align: center; padding: 60px 20px; color: #95a5a6; }
        .empty-state i { font-size: 4em; margin-bottom: 20px; }
        
        /* Modal contact */
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); align-items: center; justify-content: center; z-index: 9999; }
        .modal { background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 12px 40px rgba(0,0,0,0.25); }
        .modal h3 { margin: 0 0 20px 0; color: #2c3e50; }
        .modal textarea { width: 100%; min-height: 120px; padding: 12px; border: 2px solid #e1e8ed; border-radius: 8px; font-family: inherit; }
        .modal .actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 15px; }
        .modal .btn-cancel { background: #95a5a6; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
        .modal .btn-send { background: #5dade2; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .modal .btn-send:hover { background: #3498db; }
    </style>
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <div class="messages-container">
        <div class="page-header">
            <h1><i class="fas fa-comments"></i> Messagerie Expert</h1>
            <p>Contactez les clients via leurs histoires ou continuez vos conversations</p>
        </div>

        <div class="content-grid">
            <!-- Histoires disponibles -->
            <div class="stories-sidebar">
                <h2><i class="fas fa-book"></i> Histoires</h2>
                <?php if (empty($allStories)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Aucune histoire disponible</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($allStories as $story): ?>
                        <div class="story-item">
                            <div class="story-author">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($story['auteur_nom']) ?>
                            </div>
                            <div class="story-title"><?= htmlspecialchars($story['titre']) ?></div>
                            <div class="story-excerpt">
                                <?= htmlspecialchars(substr($story['contenu'], 0, 80)) ?>...
                            </div>
                            <button class="btn-contact-story" 
                                    onclick="openContactModal(<?= $story['auteur_id'] ?>, '<?= htmlspecialchars($story['auteur_nom'], ENT_QUOTES) ?>', <?= $story['id_histoire'] ?>)">
                                <i class="fas fa-paper-plane"></i> Contacter
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Conversations -->
            <div class="conversations-main">
                <h2 style="color: #2c3e50; margin: 0 0 20px 0;">
                    <i class="fas fa-inbox"></i> Mes Conversations
                </h2>
                
                <?php if (empty($conversationsWithMessages)): ?>
                    <div class="conversation-card">
                        <div class="empty-state">
                            <i class="fas fa-comments"></i>
                            <p>Aucune conversation en cours</p>
                            <p style="font-size: 0.9em;">Contactez un client via une histoire pour démarrer une conversation</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($conversationsWithMessages as $convData): 
                        $conv = $convData['conversation'];
                        $messages = $convData['messages'];
                    ?>
                        <div class="conversation-card">
                            <div class="conversation-header">
                                <div class="client-info">
                                    <div class="client-avatar">
                                        <?= strtoupper(substr($conv['client_nom'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="client-name"><?= htmlspecialchars($conv['client_nom']) ?></div>
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

                            <form class="reply-form" onsubmit="sendReply(event, <?= $conv['id_client'] ?>, <?= $conv['id_conversation'] ?>)">
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
    </div>

    <!-- Modal Contact -->
    <div class="modal-backdrop" id="contactModal">
        <div class="modal">
            <h3>Contacter <span id="modalClientName"></span></h3>
            <form id="contactForm">
                <input type="hidden" id="modalClientId" name="id_client">
                <input type="hidden" id="modalHistoireId" name="id_histoire">
                <textarea name="contenu" placeholder="Écrivez votre message..." required></textarea>
                <div class="actions">
                    <button type="button" class="btn-cancel" onclick="closeContactModal()">Annuler</button>
                    <button type="submit" class="btn-send">Envoyer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openContactModal(clientId, clientName, histoireId) {
            document.getElementById('modalClientId').value = clientId;
            document.getElementById('modalHistoireId').value = histoireId;
            document.getElementById('modalClientName').textContent = clientName;
            document.getElementById('contactModal').style.display = 'flex';
        }

        function closeContactModal() {
            document.getElementById('contactModal').style.display = 'none';
            document.getElementById('contactForm').reset();
        }

        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'send_message');
            
            const btn = this.querySelector('.btn-send');
            btn.disabled = true;
            btn.textContent = 'Envoi...';
            
            fetch('../../Controller/MessageController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeContactModal();
                    location.reload();
                } else {
                    alert('Erreur: ' + (data.error || 'Impossible d\'envoyer le message'));
                    btn.disabled = false;
                    btn.textContent = 'Envoyer';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue');
                btn.disabled = false;
                btn.textContent = 'Envoyer';
            });
        });

        function sendReply(event, clientId, conversationId) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            formData.append('action', 'reply_message');
            formData.append('id_client', clientId);
            formData.append('id_expert', <?= $idExpert ?>);
            
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
