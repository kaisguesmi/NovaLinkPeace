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

        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;margin-bottom:20px;">
            <?php if (!empty($reclamationRecap)): ?>
                <?php
                    $statusLabelMap = [
                        'nouvelle' => 'En attente',
                        'en_cours' => 'En cours',
                        'resolue' => 'Résolue',
                        'acceptee' => 'Acceptée',
                        'refusee' => 'Refusée',
                    ];
                    $status = $reclamationRecap['statut'] ?? 'nouvelle';
                    $statusLabel = $statusLabelMap[$status] ?? $status;
                    $badgeColor = [
                        'acceptee' => '#1e88e5',
                        'resolue' => '#27ae60',
                        'refusee' => '#e74c3c',
                        'en_cours' => '#f39c12',
                        'nouvelle' => '#9b59b6',
                    ][$status] ?? '#9b59b6';
                    $count = (int)($reclamationRecap['count_total'] ?? 1);
                ?>
                <button type="button" class="btn-secondary" style="border:1px solid #e1e8f0;border-radius:10px;padding:10px 14px;display:flex;align-items:center;gap:10px;background:#f7f9fc;cursor:default;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:10px;background:<?= $badgeColor ?>;color:#fff;font-weight:700;">
                        <?= $count ?>
                    </span>
                    <span style="display:flex;flex-direction:column;align-items:flex-start;line-height:1.3;">
                        <strong style="font-size:14px;">Réclamation<?= $count>1?'s':'' ?> sur cette histoire</strong>
                        <small style="color:#4b5563;">Dernier statut : <?= htmlspecialchars($statusLabel) ?></small>
                    </span>
                </button>
                <?php if (!empty($reclamationRecap['ai_analysis'])): ?>
                    <div style="padding:12px 14px;border:1px solid #e1e8f0;border-radius:10px;background:#fff;max-width:340px;">
                        <div style="font-size:13px;color:#6b7280;margin-bottom:6px;">Analyse AI</div>
                        <div style="font-size:14px;color:#111827;line-height:1.4;">"<?= htmlspecialchars($reclamationRecap['ai_analysis']) ?>"</div>
                        <?php if (isset($reclamationRecap['ai_score'])): ?>
                            <div style="margin-top:6px;font-size:13px;color:#374151;">Score : <?= htmlspecialchars(number_format((float)$reclamationRecap['ai_score'],1)) ?> / 100</div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <button type="button" class="btn-primary" style="border-radius:10px;padding:10px 14px;display:flex;align-items:center;gap:10px;cursor:default;">
                    <i class="fa-solid fa-shield-check"></i>
                    <span>Cette histoire n'a aucune réclamation.</span>
                </button>
            <?php endif; ?>
        </div>
        
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
