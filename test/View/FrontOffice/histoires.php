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
        /* --- VARIABLES & RESET --- */
        :root {
            --primary-color: #5dade2;
            --primary-dark: #3498db;
            --secondary-color: #f0f2f5;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --white: #ffffff;
            --shadow-soft: 0 10px 25px rgba(0,0,0,0.05);
            --shadow-hover: 0 15px 35px rgba(0,0,0,0.1);
            --radius: 16px;
            --transition: all 0.3s ease;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6; /* Fond très léger */
            background-image: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
            color: var(--text-dark);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        /* --- LAYOUT --- */
        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        h1 {
            font-size: 2.5rem;
            color: var(--text-dark);
            margin-bottom: 10px;
            font-weight: 700;
            text-align: center;
        }

        p { color: var(--text-light); text-align: center; max-width: 700px; margin: 0 auto 30px auto; }

        /* --- BUTTONS --- */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--white);
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(93, 173, 226, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(93, 173, 226, 0.5);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #ddd;
            color: var(--text-light);
            padding: 8px 16px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 13px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-outline:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: rgba(93, 173, 226, 0.05);
        }

        /* --- ALERTS --- */
        .alert { padding: 15px 20px; border-radius: 12px; margin: 20px 0; font-weight: 500; display: flex; align-items: center; }
        .alert-success { background: #d4edda; color: #155724; border-left: 5px solid #28a745; }
        .alert-error { background: #f8d7da; color: #721c24; border-left: 5px solid #dc3545; }

        /* --- STORIES GRID --- */
        .stories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        /* --- STORY CARD --- */
        .story-card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow-soft);
            padding: 25px;
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.03);
            position: relative;
            overflow: hidden;
        }

        .story-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .story-meta {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .story-meta::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: var(--primary-color);
            border-radius: 50%;
        }

        .story-title {
            margin: 0 0 15px 0;
            color: var(--text-dark);
            font-size: 1.4rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .story-content {
            color: #555;
            font-size: 15px;
            line-height: 1.7;
            margin-bottom: 20px;
            flex-grow: 1;
        }

        /* --- COMMENTS SECTION --- */
        .comments-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            margin-top: auto; /* Push to bottom */
        }

        .comment-item {
            background: var(--white);
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 8px;
            border: 1px solid #eee;
            font-size: 13px;
        }

        .comment-meta { font-size: 11px; color: #aaa; margin-bottom: 3px; font-weight: 600; }
        .comment-text { color: #444; }

        textarea {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 12px;
            font-family: inherit;
            resize: vertical;
            transition: border-color 0.3s;
            background: #fafafa;
        }

        textarea:focus { outline: none; border-color: var(--primary-color); background: var(--white); }

        /* --- ACTIONS BAR (Reactions) --- */
        .story-actions {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* --- MODAL --- */
        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-backdrop[style*="display: flex"] { opacity: 1; }

        .modal {
            background: var(--white);
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 550px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            transform: scale(0.9);
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .modal-backdrop[style*="display: flex"] .modal { transform: scale(1); }

        .modal h3 { margin-top: 0; color: var(--text-dark); font-size: 1.5rem; }
        
        .modal .cause-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
        }

        .modal label { font-size: 14px; color: #555; cursor: pointer; display: flex; align-items: center; gap: 8px;}
        
        /* --- CHAT ASSISTANT --- */
        #assistant-widget-container { position: fixed; bottom: 30px; right: 30px; z-index: 10000; display:flex; flex-direction:column; align-items:flex-end; gap:15px; }
        
        #assistant-toggle-btn {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 5px 20px rgba(93, 173, 226, 0.5);
            font-size: 12px;
            font-weight: bold;
            transition: transform 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #assistant-toggle-btn:hover { transform: scale(1.1) rotate(5deg); }

        #assistant-chat-popup {
            display: none;
            width: 350px;
            height: 450px;
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            flex-direction: column;
            border: 1px solid rgba(0,0,0,0.05);
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        #assistant-chat-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--white);
            padding: 15px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #assistant-chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            background: #f9f9f9;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .assistant-message { padding: 10px 14px; border-radius: 12px; font-size: 14px; max-width: 80%; line-height: 1.4; }
        .assistant-message.from-user { align-self: flex-end; background: var(--primary-color); color: white; border-bottom-right-radius: 2px; }
        .assistant-message.from-bot { align-self: flex-start; background: #e9ecef; color: #333; border-bottom-left-radius: 2px; }

        #assistant-chat-input-area { padding: 15px; background: white; border-top: 1px solid #eee; display: flex; gap: 10px; }
        #assistant-user-input { flex: 1; border: 1px solid #ddd; border-radius: 20px; padding: 10px 15px; font-size: 14px; max-height: 50px; }
        #assistant-send-btn { border-radius: 50%; width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center; }

        /* Responsive */
        @media (max-width: 768px) {
            .stories-grid { grid-template-columns: 1fr; }
            .modal { width: 95%; margin: 10px; }
            #assistant-chat-popup { width: 90vw; height: 60vh; bottom: 80px; }
        }
    </style>
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <!-- On force une marge de 150px en haut -->
    <main class="container" style="margin-top: 150px !important;">
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
