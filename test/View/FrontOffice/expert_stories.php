<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
    header("Location: login.php");
    exit();
}

$stories = $_SESSION['expert_stories'] ?? [];
unset($_SESSION['expert_stories']);

$successMsg = $_SESSION['success_msg'] ?? '';
unset($_SESSION['success_msg']);

$errorMsg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['error_msg']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histoires des Clients - Expert</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #667eea;
            font-size: 2em;
        }

        .btn-back {
            background: #6c757d;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .stories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .story-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            transition: all 0.3s;
            position: relative;
        }

        .story-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        }

        .story-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2em;
            margin-right: 15px;
        }

        .author-info h3 {
            color: #333;
            font-size: 1.1em;
            margin-bottom: 5px;
        }

        .story-date {
            color: #888;
            font-size: 0.85em;
        }

        .story-title {
            color: #667eea;
            font-size: 1.3em;
            margin-bottom: 12px;
            font-weight: bold;
        }

        .story-content {
            color: #555;
            line-height: 1.6;
            margin-bottom: 20px;
            max-height: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .story-actions {
            display: flex;
            gap: 10px;
        }

        .btn-contact {
            flex: 1;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-contact:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        /* Modal pour envoyer un message */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .modal-header h2 {
            color: #667eea;
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            color: #888;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }

        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 1em;
            resize: vertical;
            min-height: 150px;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-send {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            font-size: 1.1em;
            transition: all 0.3s;
        }

        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .empty-state i {
            font-size: 4em;
            color: #ccc;
            margin-bottom: 20px;
        }

        .empty-state h2 {
            color: #888;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-book-open"></i> Histoires des Clients</h1>
            <div>
                <a href="../../Controller/MessageController.php?action=expert_conversations" class="btn-back" style="margin-right: 10px;">
                    <i class="fas fa-comments"></i> Mes Conversations
                </a>
                <a href="index.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>

        <?php if ($successMsg): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($successMsg) ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errorMsg) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($stories)): ?>
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <h2>Aucune histoire disponible</h2>
                <p>Les clients n'ont pas encore publié d'histoires.</p>
            </div>
        <?php else: ?>
            <div class="stories-grid">
                <?php foreach ($stories as $story): ?>
                    <div class="story-card">
                        <div class="story-header">
                            <div class="author-avatar">
                                <?= strtoupper(substr($story['auteur_nom'], 0, 1)) ?>
                            </div>
                            <div class="author-info">
                                <h3><?= htmlspecialchars($story['auteur_nom']) ?></h3>
                                <span class="story-date">
                                    <i class="far fa-clock"></i> 
                                    <?= date('d/m/Y', strtotime($story['date_publication'])) ?>
                                </span>
                            </div>
                        </div>
                        
                        <h2 class="story-title"><?= htmlspecialchars($story['titre']) ?></h2>
                        
                        <p class="story-content">
                            <?= nl2br(htmlspecialchars(substr($story['contenu'], 0, 200))) ?>
                            <?= strlen($story['contenu']) > 200 ? '...' : '' ?>
                        </p>
                        
                        <div class="story-actions">
                            <button class="btn-contact" onclick="openModal(<?= $story['auteur_id'] ?>, '<?= htmlspecialchars($story['auteur_nom'], ENT_QUOTES) ?>', <?= $story['id_histoire'] ?>)">
                                <i class="fas fa-paper-plane"></i> Contacter
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal pour envoyer un message -->
    <div class="modal" id="messageModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-envelope"></i> Envoyer un message</h2>
                <button class="btn-close" onclick="closeModal()">×</button>
            </div>
            
            <form id="messageForm">
                <input type="hidden" id="clientId" name="id_client">
                <input type="hidden" id="histoireId" name="id_histoire">
                <input type="hidden" name="action" value="send_message">
                
                <div class="form-group">
                    <label>À : <span id="clientName"></span></label>
                </div>
                
                <div class="form-group">
                    <label for="messageContent">Votre message :</label>
                    <textarea id="messageContent" name="contenu" required placeholder="Écrivez votre message ici..."></textarea>
                </div>
                
                <button type="submit" class="btn-send">
                    <i class="fas fa-paper-plane"></i> Envoyer
                </button>
            </form>
        </div>
    </div>

    <script>
        function openModal(clientId, clientName, histoireId) {
            document.getElementById('clientId').value = clientId;
            document.getElementById('clientName').textContent = clientName;
            document.getElementById('histoireId').value = histoireId;
            document.getElementById('messageModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('messageModal').classList.remove('active');
            document.getElementById('messageForm').reset();
        }

        document.getElementById('messageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../../Controller/MessageController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Message envoyé avec succès !');
                    closeModal();
                    location.reload();
                } else {
                    alert('Erreur: ' + (data.error || 'Impossible d\'envoyer le message'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue');
            });
        });

        // Fermer le modal en cliquant en dehors
        document.getElementById('messageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
