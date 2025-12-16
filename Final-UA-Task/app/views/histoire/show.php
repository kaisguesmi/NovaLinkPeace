<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<div class="page-header">
    <div>
        <h1><?= htmlspecialchars($story['titre']) ?></h1>
        <p class="page-subtitle">Histoire détaillée</p>
    </div>
    <a href="<?= $base ?>/?controller=histoire&action=index" class="btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
</div>

<div class="profile-card">
    <div class="story-content">
        <div style="margin-bottom: 20px;">
            <?php
            $status = $story['status'] ?? 'pending';
            $statusLabel = [
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
            ][$status] ?? $status;
            $statusClass = $status === 'approved' ? 'status-approved' : ($status === 'rejected' ? 'status-rejected' : 'status-pending');
            ?>
            <span class="status-badge <?= $statusClass ?>" style="margin-bottom: 15px; display: inline-block;">
                <?= htmlspecialchars($statusLabel) ?>
            </span>
            <?php if ($status === 'rejected' && !empty($story['rejection_reason'])): ?>
                <div style="margin-top: 10px; font-size: 14px; color: #b91c1c;">
                    <?= nl2br(htmlspecialchars($story['rejection_reason'])) ?>
                </div>
            <?php endif; ?>
        </div>
        
        <p style="font-size: 15px; line-height: 1.7; margin-bottom: 25px; white-space: pre-wrap;">
            <?= nl2br(htmlspecialchars($story['contenu'])) ?>
        </p>
        
        <div style="margin-top: 30px; display: flex; gap: 10px;">
            <a href="<?= $base ?>/?controller=histoire&action=edit&id=<?= $story['id_histoire'] ?>" class="btn-primary">
                <i class="fa-solid fa-pen"></i> Modifier
            </a>
            <a href="<?= $base ?>/?controller=histoire&action=delete&id=<?= $story['id_histoire'] ?>" 
               class="btn-danger"
               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette histoire ?')">
                <i class="fa-solid fa-trash"></i> Supprimer
            </a>
        </div>
    </div>
</div>

<div class="table-card" style="margin-top: 30px;">
    <h3>Réagir</h3>
    <form method="post" action="<?= $base ?>/?controller=histoire&action=react" style="display: flex; gap: 10px; margin-top: 15px;">
        <input type="hidden" name="id_histoire" value="<?= $story['id_histoire'] ?>">
        <button type="submit" name="emoji" value="❤️" class="reaction-btn">
            ❤️
        </button>
        <button type="submit" name="emoji" value="👏" class="reaction-btn">
            👏
        </button>
        <button type="submit" name="emoji" value="🙏" class="reaction-btn">
            🙏
        </button>
    </form>
</div>

<?php if (!empty($story['commentaires'])): ?>
    <div class="table-card" style="margin-top: 30px;">
        <h3>Commentaires (<?= count($story['commentaires']) ?>)</h3>
        <div class="comments-list">
            <?php foreach ($story['commentaires'] as $comment): ?>
                <div class="comment-item">
                    <div class="comment-content">
                        <div class="comment-header">
                            <span class="comment-author"><?= htmlspecialchars($comment['email']) ?></span>
                            <span class="comment-time"><?= date('d/m/Y H:i', strtotime($comment['date_publication'])) ?></span>
                        </div>
                        <p class="comment-text"><?= nl2br(htmlspecialchars($comment['contenu'])) ?></p>
                        <div style="margin-top: 10px; display: flex; gap: 10px;">
                            <a href="<?= $base ?>/?controller=commentaires&action=edit&id=<?= $comment['id_commentaire'] ?>" 
                               class="action-btn" 
                               style="font-size: 14px;">
                                <i class="fa-solid fa-pen"></i> Modifier
                            </a>
                            <a href="<?= $base ?>/?controller=commentaires&action=delete&id=<?= $comment['id_commentaire'] ?>" 
                               class="action-btn danger" 
                               style="font-size: 14px;"
                               onclick="return confirm('Supprimer ce commentaire ?')">
                                <i class="fa-solid fa-trash"></i> Supprimer
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="table-card" style="margin-top: 30px;">
    <h3>Ajouter un commentaire</h3>
    <form method="post" action="<?= $base ?>/?controller=commentaires&action=store" class="comment-form-wrapper" style="margin-top: 15px;" id="story-show-comment-form">
        <input type="hidden" name="id_histoire" value="<?= $story['id_histoire'] ?>">
        <div class="comment-input-wrapper">
            <textarea name="contenu" 
                      rows="3" 
                      class="comment-input" 
                      placeholder="Exprimez votre soutien..."
                      style="resize: vertical; min-height: 80px;"></textarea>
        </div>
        <div style="margin-top: 10px;">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-paper-plane"></i> Publier mon message
            </button>
        </div>
    </form>
</div>
