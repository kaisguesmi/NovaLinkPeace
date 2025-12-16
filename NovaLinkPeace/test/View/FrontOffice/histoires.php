<?php
session_start();
require_once __DIR__ . '/../../Model/Database.php';
require_once __DIR__ . '/../../Model/Histoire.php';

$database = new Database();
$db = $database->getConnection();
$histoireModel = new Histoire($db);

$histoires = $histoireModel->getAllStories();
$reactionsByStory = [];
$commentsByStory = [];
if (!empty($histoires)) {
    foreach ($histoires as $h) {
        $reactionsByStory[$h['id_histoire']] = $histoireModel->getReactions($h['id_histoire']);
        $commentsByStory[$h['id_histoire']] = $histoireModel->getComments($h['id_histoire']);
    }
}
$causes = $histoireModel->getCauses();
$isLogged = isset($_SESSION['user_id']);
$successMsg = $_SESSION['success_msg'] ?? '';
$errorMsg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histoires - PeaceLink</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .stories-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 30px; }
        .story-card { background:#fff; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.08); padding:20px; display:flex; flex-direction:column; }
        .story-meta { color:#777; font-size:13px; margin-bottom:10px; }
        .story-title { margin:0 0 10px; color:#2c3e50; }
        .story-content { color:#555; line-height:1.6; }
        .btn-primary { background:#5dade2; color:#fff; border:none; padding:10px 14px; border-radius:10px; cursor:pointer; }
        .btn-outline { background:#fff; border:1px solid #5dade2; color:#5dade2; padding:10px 14px; border-radius:10px; cursor:pointer; }
        .alert { padding:12px 14px; border-radius:8px; margin-top:15px; }
        .alert-success { background:#d4edda; color:#155724; }
        .alert-error { background:#f8d7da; color:#721c24; }
        /* Modal */
        .modal-backdrop { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); align-items:center; justify-content:center; z-index:9999; }
        .modal { background:#fff; padding:20px; border-radius:12px; width:90%; max-width:520px; box-shadow:0 12px 40px rgba(0,0,0,0.25); }
        .modal h3 { margin-top:0; }
        .modal textarea { width:100%; min-height:100px; padding:10px; border:1px solid #ddd; border-radius:8px; }
        .modal .actions { display:flex; gap:10px; justify-content:flex-end; margin-top:15px; }
        .modal .cause-list { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:8px; margin:10px 0; }
        /* Chat assistant */
        #assistant-widget-container { position: fixed; bottom: 20px; right: 20px; z-index: 9999; display:flex; flex-direction:column; align-items:flex-end; gap:10px; }
        #assistant-toggle-btn { background:#5dade2; color:#fff; border:none; padding:10px 14px; border-radius:50px; cursor:pointer; box-shadow:0 8px 20px rgba(0,0,0,0.15); }
        #assistant-chat-popup { display:none; width:320px; max-width:90vw; background:#fff; border-radius:12px; box-shadow:0 12px 30px rgba(0,0,0,0.18); overflow:hidden; flex-direction:column; }
        #assistant-chat-header { display:flex; justify-content:space-between; align-items:center; padding:10px 12px; background:#5dade2; color:#fff; }
        #assistant-chat-messages { max-height:320px; overflow-y:auto; padding:12px; display:flex; flex-direction:column; gap:8px; }
        .assistant-message { padding:8px 10px; border-radius:10px; font-size:14px; background:#f5f7fb; }
        .assistant-message.from-user { align-self:flex-end; background:#e8f4ff; }
        .assistant-message.from-bot { align-self:flex-start; background:#f0f0f0; }
        #assistant-chat-input-area { display:flex; gap:8px; padding:10px; border-top:1px solid #eee; }
        #assistant-user-input { flex:1; resize:none; border:1px solid #ddd; border-radius:8px; padding:8px; min-height:50px; max-height:90px; }
        #assistant-send-btn { background:#5dade2; color:#fff; border:none; padding:8px 12px; border-radius:8px; cursor:pointer; }
    </style>
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main class="container" style="max-width:1100px; margin:40px auto; padding:0 20px;">
        <h1>Histoires de la communauté</h1>
        <p>Découvrez les histoires partagées par les clients et experts. Vous pouvez signaler une histoire si elle vous semble inappropriée.</p>

        <div style="margin-top:15px; display:flex; gap:12px; flex-wrap:wrap;">
            <?php if ($isLogged): ?>
                <button class="btn-primary" onclick="openPublishModal()">
                    <i class="fa-solid fa-pen"></i> Publier une histoire
                </button>
            <?php else: ?>
                <a class="btn-primary" href="login.php">Connectez-vous pour publier</a>
            <?php endif; ?>
        </div>

        <?php if ($successMsg): ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
        <?php endif; ?>
        <?php if ($errorMsg): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <div class="stories-grid">
            <?php if (empty($histoires)): ?>
                <p>Aucune histoire pour le moment.</p>
            <?php else: ?>
                <?php foreach ($histoires as $story): ?>
                    <article class="story-card">
                        <div class="story-meta">
                            Par <?= htmlspecialchars($story['auteur_nom']) ?> · <?= htmlspecialchars($story['date_publication']) ?>
                        </div>
                        <h3 class="story-title"><?= htmlspecialchars($story['titre']) ?></h3>
                        <div class="story-content"><?= nl2br(htmlspecialchars($story['contenu'])) ?></div>
                        <div style="margin-top:12px;">
                            <strong>Commentaires</strong>
                            <div style="margin-top:8px; display:flex; flex-direction:column; gap:8px;">
                                <?php $comments = $commentsByStory[$story['id_histoire']] ?? []; ?>
                                <?php if (empty($comments)): ?>
                                    <div style="color:#777; font-size:14px;">Aucun commentaire.</div>
                                <?php else: ?>
                                    <?php foreach ($comments as $c): ?>
                                        <div style="background:#f7f9fc; border-radius:8px; padding:8px 10px;">
                                            <div style="font-size:12px; color:#666;">Par <?= htmlspecialchars($c['auteur_nom']) ?> · <?= htmlspecialchars($c['date_publication']) ?></div>
                                            <div style="margin-top:4px; font-size:14px; color:#333;"><?= nl2br(htmlspecialchars($c['contenu'])) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div style="margin-top:8px;">
                                <?php if ($isLogged): ?>
                                    <form method="POST" action="../../Controller/HistoireController.php" style="display:flex; flex-direction:column; gap:6px;">
                                        <input type="hidden" name="action" value="commenter">
                                        <input type="hidden" name="id_histoire" value="<?= (int)$story['id_histoire'] ?>">
                                        <textarea name="contenu" required placeholder="Ajouter un commentaire..." style="width:100%; min-height:70px; border:1px solid #ddd; border-radius:8px; padding:8px;"></textarea>
                                        <button type="submit" class="btn-primary" style="align-self:flex-end;">Publier le commentaire</button>
                                    </form>
                                <?php else: ?>
                                    <a class="btn-outline" href="login.php">Connectez-vous pour commenter</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="margin-top:15px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                            <?php
                                $reac = $reactionsByStory[$story['id_histoire']] ?? [];
                                $uReac = $isLogged ? $histoireModel->getUserReaction($story['id_histoire'], $_SESSION['user_id']) : null;
                                $buttons = [
                                    ['like','👍'],
                                    ['love','❤️'],
                                    ['laugh','😂'],
                                    ['angry','😡'],
                                    ['dislike','👎'],
                                ];
                            ?>
                            <?php foreach ($buttons as [$type,$emoji]): ?>
                                <form method="POST" action="../../Controller/HistoireController.php" style="display:inline;">
                                    <input type="hidden" name="action" value="react">
                                    <input type="hidden" name="id_histoire" value="<?= (int)$story['id_histoire'] ?>">
                                    <input type="hidden" name="type" value="<?= $type ?>">
                                    <button type="submit" class="btn-outline" style="padding:6px 10px; border-radius:8px; <?= ($uReac === $type) ? 'background:#5dade2;color:#fff;border:1px solid #5dade2;' : '' ?>">
                                        <?= $emoji ?> <?= isset($reac[$type]) ? (int)$reac[$type] : 0 ?>
                                    </button>
                                </form>
                            <?php endforeach; ?>
                            <button class="btn-outline" onclick="openModal(<?= (int)$story['id_histoire'] ?>, '<?= htmlspecialchars(addslashes($story['titre'])) ?>')">
                                <i class="fa-solid fa-flag"></i> Réclamer
                            </button>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Forum Assistant Chat Widget -->
    <div id="assistant-widget-container">
        <button id="assistant-toggle-btn" type="button">Assistant</button>
        <div id="assistant-chat-popup">
            <div id="assistant-chat-header">
                <span>Forum Assistant</span>
                <button id="assistant-close-btn" type="button" style="background:none;border:none;color:#fff;font-size:18px;cursor:pointer;">&times;</button>
            </div>
            <div id="assistant-chat-messages"></div>
            <div id="assistant-chat-input-area">
                <textarea id="assistant-user-input" rows="2" placeholder="Ask how to use the forum..."></textarea>
                <button id="assistant-send-btn" type="button">Send</button>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="modal-backdrop">
        <div class="modal">
            <h3 id="modal-title">Réclamer une histoire</h3>
            <?php if (!$isLogged): ?>
                <p>Vous devez être connecté pour déposer une réclamation.</p>
                <div class="actions">
                    <a class="btn-primary" href="login.php">Se connecter</a>
                </div>
            <?php else: ?>
                <form method="POST" action="../../Controller/HistoireController.php">
                    <input type="hidden" name="action" value="reclamer">
                    <input type="hidden" name="id_histoire" id="id_histoire_modal">
                    <label for="description">Motif détaillé</label>
                    <textarea name="description" id="description" required placeholder="Expliquez pourquoi vous signalez cette histoire..."></textarea>

                    <p style="margin-top:10px; font-weight:600;">Causes</p>
                    <div class="cause-list">
                        <?php foreach ($causes as $cause): ?>
                            <label><input type="checkbox" name="causes[]" value="<?= (int)$cause['id_cause'] ?>"> <?= htmlspecialchars($cause['libelle']) ?></label>
                        <?php endforeach; ?>
                    </div>

                    <div class="actions">
                        <button type="button" class="btn-outline" onclick="closeModal()">Annuler</button>
                        <button type="submit" class="btn-primary">Envoyer</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal-backdrop" id="publish-backdrop">
        <div class="modal">
            <h3>Publier une histoire</h3>
            <?php if (!$isLogged): ?>
                <p>Vous devez être connecté pour publier.</p>
                <div class="actions">
                    <a class="btn-primary" href="login.php">Se connecter</a>
                </div>
            <?php else: ?>
                <form method="POST" action="../../Controller/HistoireController.php">
                    <input type="hidden" name="action" value="publier">
                    <label for="titre">Titre</label>
                    <input type="text" name="titre" id="titre" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">

                    <label for="contenu" style="margin-top:10px; display:block;">Contenu</label>
                    <textarea name="contenu" id="contenu" required placeholder="Racontez votre histoire..." style="min-height:140px;"></textarea>

                    <div class="actions">
                        <button type="button" class="btn-outline" onclick="closePublishModal()">Annuler</button>
                        <button type="submit" class="btn-primary">Publier</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Chat assistant (frontend)
        (function(){
            var toggleBtn = document.getElementById('assistant-toggle-btn');
            var chatPopup = document.getElementById('assistant-chat-popup');
            var closeBtn = document.getElementById('assistant-close-btn');
            var messagesContainer = document.getElementById('assistant-chat-messages');
            var userInput = document.getElementById('assistant-user-input');
            var sendBtn = document.getElementById('assistant-send-btn');
            var endpoint = '/integration/test/chatbot.php';

            function toggleChat(open) {
                if (open === true) chatPopup.style.display = 'flex';
                else if (open === false) chatPopup.style.display = 'none';
                else chatPopup.style.display = (chatPopup.style.display === 'flex') ? 'none' : 'flex';
                if (chatPopup.style.display === 'flex') userInput.focus();
            }

            function appendMessage(text, sender) {
                var msg = document.createElement('div');
                msg.classList.add('assistant-message');
                msg.classList.add(sender === 'user' ? 'from-user' : 'from-bot');
                msg.textContent = text;
                messagesContainer.appendChild(msg);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                return msg;
            }

            function sendMessage() {
                var message = userInput.value.trim();
                if (!message) return;
                appendMessage(message, 'user');
                userInput.value = '';
                userInput.style.height = 'auto';
                var typing = appendMessage('Assistant is typing...', 'bot');
                sendBtn.disabled = true;
                fetch(endpoint, {
                    method: 'POST',
                    headers: {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
                    body: 'message=' + encodeURIComponent(message)
                }).then(function(res){
                    if (!res.ok) throw new Error('net');
                    return res.json();
                }).then(function(data){
                    messagesContainer.removeChild(typing);
                    var reply = (data && data.reply) ? data.reply : 'Sorry, no reply right now.';
                    appendMessage(reply, 'bot');
                }).catch(function(){
                    messagesContainer.removeChild(typing);
                    appendMessage('Sorry, something went wrong contacting the assistant.', 'bot');
                }).finally(function(){ sendBtn.disabled = false; });
            }

            userInput.addEventListener('input', function(){
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 90) + 'px';
            });
            userInput.addEventListener('keydown', function(e){
                if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
            });
            sendBtn.addEventListener('click', sendMessage);
            toggleBtn.addEventListener('click', function(){ toggleChat(); });
            closeBtn.addEventListener('click', function(){ toggleChat(false); });
        })();

        function openModal(id, titre) {
            document.getElementById('id_histoire_modal').value = id;
            document.getElementById('modal-title').innerText = 'Réclamer: ' + titre;
            document.getElementById('modal-backdrop').style.display = 'flex';
        }
        function closeModal() {
            document.getElementById('modal-backdrop').style.display = 'none';
        }

        function openPublishModal() {
            document.getElementById('publish-backdrop').style.display = 'flex';
        }
        function closePublishModal() {
            document.getElementById('publish-backdrop').style.display = 'none';
        }
    </script>
</body>
</html>
