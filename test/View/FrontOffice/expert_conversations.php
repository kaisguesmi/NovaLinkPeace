<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
    header("Location: login.php");
    exit();
}

$conversations = $_SESSION['expert_conversations'] ?? [];
unset($_SESSION['expert_conversations']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Conversations - Expert</title>
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
            max-width: 1000px;
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

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .conversations-list {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .conversation-item {
            padding: 25px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .conversation-item:hover {
            background: #f8f9fa;
        }

        .conversation-item:last-child {
            border-bottom: none;
        }

        .client-info {
            display: flex;
            align-items: center;
            flex: 1;
        }

        .client-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.5em;
            margin-right: 20px;
        }

        .client-details h3 {
            color: #333;
            font-size: 1.2em;
            margin-bottom: 8px;
        }

        .last-message {
            color: #888;
            font-size: 0.9em;
            max-width: 400px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .btn-view {
            background: linear-gradient(135deg, #667eea, #764ba2);
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

        .btn-view:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
        }

        .empty-state i {
            font-size: 5em;
            color: #e0e0e0;
            margin-bottom: 20px;
        }

        .empty-state h2 {
            color: #888;
            margin-bottom: 15px;
        }

        .empty-state p {
            color: #aaa;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-comments"></i> Mes Conversations</h1>
            <div class="header-actions">
                <a href="../../Controller/MessageController.php?action=expert_stories" class="btn btn-primary">
                    <i class="fas fa-book-open"></i> Voir les histoires
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>

        <?php if (empty($conversations)): ?>
            <div class="conversations-list">
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h2>Aucune conversation</h2>
                    <p>Vous n'avez pas encore de conversations avec des clients.</p>
                    <a href="../../Controller/MessageController.php?action=expert_stories" class="btn btn-primary">
                        <i class="fas fa-book-open"></i> Découvrir les histoires
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="conversations-list">
                <?php foreach ($conversations as $conv): ?>
                    <a href="../../Controller/MessageController.php?action=view_conversation&id_client=<?= $conv['id_client'] ?>" 
                       class="conversation-item">
                        <div class="client-info">
                            <div class="client-avatar">
                                <?= strtoupper(substr($conv['client_nom'], 0, 1)) ?>
                            </div>
                            <div class="client-details">
                                <h3><?= htmlspecialchars($conv['client_nom']) ?></h3>
                                <?php if ($conv['dernier_message']): ?>
                                    <p class="last-message">
                                        <i class="far fa-comment-dots"></i> 
                                        <?= htmlspecialchars(substr($conv['dernier_message'], 0, 80)) ?>
                                        <?= strlen($conv['dernier_message']) > 80 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <span class="btn-view">
                            <i class="fas fa-eye"></i> Voir
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
