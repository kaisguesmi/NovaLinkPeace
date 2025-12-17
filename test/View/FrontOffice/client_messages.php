<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit();
}

$conversations = $_SESSION['client_conversations'] ?? [];
unset($_SESSION['client_conversations']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Messages - Client</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f5f7fb;
        }

        .messages-wrapper {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .messages-header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .messages-header h1 {
            color: #2c3e50;
            font-size: 2em;
            margin: 0;
        }

        .btn-back {
            background: #5dade2;
            color: white;
            padding: 12px 24px;
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
            box-shadow: 0 5px 15px rgba(93, 173, 226, 0.4);
        }

        .conversations-list {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
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

        .expert-info {
            display: flex;
            align-items: center;
            flex: 1;
        }

        .expert-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #5dade2, #3498db);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.5em;
            margin-right: 20px;
        }

        .expert-details h3 {
            color: #333;
            font-size: 1.2em;
            margin-bottom: 5px;
        }

        .expert-specialite {
            color: #888;
            font-size: 0.9em;
        }

        .conversation-meta {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .unread-badge {
            background: #ff4444;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85em;
        }

        .btn-view {
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

        .btn-view:hover {
            background: #3498db;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(93, 173, 226, 0.4);
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
        }
    </style>
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <div class="messages-wrapper">
        <div class="messages-header">
            <h1><i class="fas fa-envelope-open"></i> Mes Messages</h1>
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>

        <?php if (empty($conversations)): ?>
            <div class="conversations-list">
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h2>Aucun message</h2>
                    <p>Vous n'avez pas encore reçu de messages d'experts.</p>
                    <p style="margin-top: 10px; color: #999;">Les experts peuvent vous contacter après avoir lu vos histoires.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="conversations-list">
                <?php foreach ($conversations as $conv): ?>
                    <a href="../../Controller/MessageController.php?action=view_conversation&id_expert=<?= $conv['id_expert'] ?>" 
                       class="conversation-item">
                        <div class="expert-info">
                            <div class="expert-avatar">
                                <?= strtoupper(substr($conv['expert_nom'], 0, 1)) ?>
                            </div>
                            <div class="expert-details">
                                <h3><?= htmlspecialchars($conv['expert_nom']) ?></h3>
                                <p class="expert-specialite">
                                    <i class="fas fa-star"></i> 
                                    <?= htmlspecialchars($conv['expert_specialite'] ?? 'Expert') ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="conversation-meta">
                            <?php if ($conv['unread_count'] > 0): ?>
                                <span class="unread-badge">
                                    <?= $conv['unread_count'] ?> nouveau<?= $conv['unread_count'] > 1 ? 'x' : '' ?>
                                </span>
                            <?php endif; ?>
                            
                            <span class="btn-view">
                                <i class="fas fa-comments"></i> Voir
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'partials/footer.php'; ?>
</body>
</html>
