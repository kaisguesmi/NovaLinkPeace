<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
$isAdminDashboard = $isAdminDashboard ?? false;

// Format time helper function
if (!function_exists('getTimeAgo')) {
    function getTimeAgo(DateTime $date): string {
        $now = new DateTime();
        $diff = $now->diff($date);
        
        if ($diff->y > 0) return $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
        if ($diff->m > 0) return $diff->m . ' mois';
        if ($diff->d > 0) return $diff->d . ' jour' . ($diff->d > 1 ? 's' : '');
        if ($diff->h > 0) return $diff->h . ' heure' . ($diff->h > 1 ? 's' : '');
        if ($diff->i > 0) return $diff->i . ' min';
        return 'À l\'instant';
    }
}

// Format time
$createdAt = new DateTime($post['date_creation']);
$timeAgo = getTimeAgo($createdAt);

// Get user display name (never show email as the name)
if (($post['id_auteur'] ?? null) === ($user['id_utilisateur'] ?? null)) {
    // For the post owner, prefer profile full name
    $displayName = $user['nom_complet'] ?? 'Utilisateur';
} else {
    $displayName = $post['nom_complet'] ?? 'Utilisateur';
}
$avatar = $post['avatar'] ?? null;
$avatarInitials = strtoupper(substr($displayName, 0, 2));

// Get reactions summary
$reactions = $post['reactions'] ?? [];
$reactionCounts = [];
foreach ($reactions as $r) {
    $reactionCounts[$r['type']] = (int) $r['count'];
}
$totalReactions = array_sum($reactionCounts);

// Get comments
$comments = $post['comments'] ?? [];
$commentCount = count($comments);

// User's current reaction
$userReaction = $post['user_reaction'] ?? null;
?>

<div class="post-card" data-post-id="<?= $post['id_post'] ?>">
    <!-- Post Header -->
    <div class="post-header">
        <div class="post-author">
            <?php if ($avatar): ?>
                <img src="<?= $base ?>/assets/images/<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($displayName) ?>" class="post-avatar">
            <?php else: ?>
                <div class="post-avatar post-avatar-placeholder"><?= htmlspecialchars($avatarInitials) ?></div>
            <?php endif; ?>
            <div class="post-author-info">
                <span class="post-author-name"><?= htmlspecialchars($displayName) ?></span>
                <span class="post-time"><?= $timeAgo ?></span>
                <?php if (!empty($post['status'])): ?>
                    <?php
                    $status = $post['status'];
                    $statusLabelMap = [
                        'approved' => 'Approved',
                        'pending'  => 'Pending',
                        'rejected' => 'Rejected',
                    ];
                    $statusLabel = $statusLabelMap[$status] ?? ucfirst($status);
                    ?>
                    <span class="status-badge status-<?= htmlspecialchars($status) ?>"><?= htmlspecialchars($statusLabel) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php
        $isOwner = ($user['id_utilisateur'] ?? null) == ($post['id_auteur'] ?? null);
        $isAdmin = !empty($user['is_admin']);
        ?>
        <?php if ($isOwner || $isAdmin): ?>
            <div class="post-actions-menu">
                <?php if ($isOwner && !$isAdmin): ?>
                    <a href="<?= $base ?>/?controller=post&action=edit&id=<?= $post['id_post'] ?>" class="action-btn" title="Edit post">
                        <img src="<?= $base ?>/assets/images/icon-post-edit.png" alt="Edit post" class="action-icon" width="18" height="18" style="width:18px;height:18px;object-fit:contain;">
                    </a>
                <?php endif; ?>
                <button class="action-btn danger" onclick="if(confirm('Are you sure you want to delete this post?')) { deletePost(<?= $post['id_post'] ?>); }" title="Delete post">
                    <img src="<?= $base ?>/assets/images/icon-post-delete.png" alt="Delete post" class="action-icon" width="18" height="18" style="width:18px;height:18px;object-fit:contain;">
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Post Content -->
    <div class="post-content">
        <?php if (!empty($post['titre'])): ?>
            <h3 class="post-title"><?= htmlspecialchars($post['titre']) ?></h3>
        <?php endif; ?>
        <p class="post-text"><?= nl2br(htmlspecialchars($post['contenu'])) ?></p>
        <?php if (!empty($post['image'])): ?>
            <div class="post-image-wrapper">
                <img src="<?= $base ?>/assets/images/<?= htmlspecialchars($post['image']) ?>" alt="Post image" class="post-image">
            </div>
        <?php endif; ?>
    </div>

    <!-- Post Actions (Reactions & Comments) -->
    <div class="post-actions">
        <div class="post-reactions">
            <?php if ($totalReactions > 0): ?>
                <div class="reactions-summary">
                    <?php
                    $emojiMap = ['like' => '👍', 'love' => '❤️', 'laugh' => '😂', 'angry' => '😡'];
                    $displayed = [];
                    foreach ($reactionCounts as $type => $count) {
                        if ($count > 0) {
                            $displayed[] = $emojiMap[$type] . ' ' . $count;
                        }
                    }
                    echo implode(' ', $displayed);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="reaction-buttons">
                <button class="reaction-btn <?= $userReaction === 'like' ? 'active' : '' ?>" 
                        data-type="like" 
                        onclick="toggleReaction(<?= $post['id_post'] ?>, 'like')">
                    👍
                </button>
                <button class="reaction-btn <?= $userReaction === 'love' ? 'active' : '' ?>" 
                        data-type="love" 
                        onclick="toggleReaction(<?= $post['id_post'] ?>, 'love')">
                    ❤️
                </button>
                <button class="reaction-btn <?= $userReaction === 'laugh' ? 'active' : '' ?>" 
                        data-type="laugh" 
                        onclick="toggleReaction(<?= $post['id_post'] ?>, 'laugh')">
                    😂
                </button>
                <button class="reaction-btn <?= $userReaction === 'angry' ? 'active' : '' ?>" 
                        data-type="angry" 
                        onclick="toggleReaction(<?= $post['id_post'] ?>, 'angry')">
                    😡
                </button>
            </div>
        </div>
        
        <button class="comment-toggle-btn" onclick="toggleComments(<?= $post['id_post'] ?>)">
            <i class="fa-solid fa-comment"></i> Commenter
            <?php if ($commentCount > 0): ?>
                <span class="comment-count"><?= $commentCount ?></span>
            <?php endif; ?>
        </button>
    </div>

    <!-- Comments Section -->
    <div class="post-comments" id="comments-<?= $post['id_post'] ?>" style="display: none;">
        <div class="comments-list">
            <?php foreach ($comments as $comment): ?>
                <div class="comment-item">
                    <?php
                    $commentAvatar = $comment['avatar'] ?? null;
                    $commentUserId = $comment['user_id'] ?? null;

                    // Determine display name for commenter (never show email as the name)
                    if ($commentUserId === ($user['id_utilisateur'] ?? null)) {
                        // Own comment: always use profile full name if available
                        $commentName = $user['nom_complet'] ?? 'Utilisateur';
                    } elseif ($commentUserId === ($post['id_auteur'] ?? null)) {
                        // Comment written by the post owner (Creator):
                        // always use the post author's display name
                        $commentName = $displayName;
                    } else {
                        $commentName = $comment['nom_complet'] ?? 'Utilisateur';
                    }

                    $commentInitials = !empty($commentName) ? strtoupper(substr($commentName, 0, 2)) : 'U';
                    $commentTime = getTimeAgo(new DateTime($comment['created_at'] ?? 'now'));
                    ?>
                    <div class="comment-avatar">
                        <?php if ($commentAvatar): ?>
                            <img src="<?= $base ?>/assets/images/<?= htmlspecialchars($commentAvatar) ?>" alt="<?= htmlspecialchars($commentName) ?>">
                        <?php else: ?>
                            <div class="comment-avatar-placeholder"><?= htmlspecialchars($commentInitials) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="comment-content">
                        <div class="comment-header">
                            <div class="comment-author-info">
                                <span class="comment-author"><?= htmlspecialchars($commentName) ?></span>
                                <?php if (($comment['user_id'] ?? null) === $post['id_auteur']): ?>
                                    <span class="creator-badge" title="Creator of this post">
                                        <i class="fas fa-crown"></i> Creator
                                    </span>
                                <?php endif; ?>
                            </div>
                            <span class="comment-time"><?= $commentTime ?></span>
                        </div>
                        <p class="comment-text"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                        <div class="comment-actions">
                            <?php
                            $isCommentOwner = ($user['id_utilisateur'] ?? null) == ($comment['user_id'] ?? null);
                            $isAdmin = !empty($user['is_admin']);
                            ?>
                            <?php if ($isCommentOwner || $isAdmin): ?>
                                <?php if ($isCommentOwner && !$isAdmin): ?>
                                    <a href="<?= $base ?>/?controller=comment&action=edit&id=<?= $comment['id_comment'] ?>" class="action-btn" title="Edit comment">
                                        <img src="<?= $base ?>/assets/images/icon-comment-edit.png" alt="Edit comment" class="action-icon" width="18" height="18" style="width:18px;height:18px;object-fit:contain;">
                                    </a>
                                <?php endif; ?>

                                <?php if ($isAdminDashboard && $isAdmin): ?>
                                    <button type="button" class="action-btn danger" title="Delete comment" onclick="return deleteCommentAdmin(<?= $comment['id_comment'] ?>);">
                                        <img src="<?= $base ?>/assets/images/icon-comment-delete.png" alt="Delete comment" class="action-icon" width="18" height="18" style="width:18px;height:18px;object-fit:contain;">
                                    </button>
                                <?php else: ?>
                                    <a href="<?= $base ?>/?controller=comment&action=delete&id=<?= $comment['id_comment'] ?>" class="action-btn danger" title="Delete comment" onclick="return confirm('Are you sure you want to delete this comment?');">
                                        <img src="<?= $base ?>/assets/images/icon-comment-delete.png" alt="Delete comment" class="action-icon" width="18" height="18" style="width:18px;height:18px;object-fit:contain;">
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Comment Form -->
        <div class="comment-form-wrapper">
            <form class="comment-form" id="comment-form-<?= $post['id_post'] ?>">
                <input type="hidden" name="post_id" value="<?= $post['id_post'] ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="comment-input-wrapper">
                    <?php
                    $userAvatar = $user['avatar'] ?? null;
                    $userName = $user['nom_complet'] ?? $user['email'] ?? 'Utilisateur';
                    $userInitials = strtoupper(substr($userName, 0, 2));
                    ?>
                    <div class="comment-form-avatar">
                        <?php if ($userAvatar): ?>
                            <img src="<?= $base ?>/assets/images/<?= htmlspecialchars($userAvatar) ?>" alt="<?= htmlspecialchars($userName) ?>">
                        <?php else: ?>
                            <div class="comment-avatar-placeholder"><?= htmlspecialchars($userInitials) ?></div>
                        <?php endif; ?>
                    </div>
                    <input type="text" 
                           class="comment-input" 
                           placeholder="Écrivez un commentaire..." 
                           name="content"
                           required>
                    <button type="submit" class="comment-submit-btn">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>
        <script>
        document.getElementById('comment-form-<?= $post['id_post'] ?>').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            
            fetch('<?= $base ?>/?controller=comment&action=store', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear the input
                    form.querySelector('input[name="content"]').value = '';
                    
                    // Reload comments
                    const commentsContainer = document.querySelector(`#comments-${<?= $post['id_post'] ?>} .comments-list`);
                    if (commentsContainer) {
                        // You can either reload the entire post or just append the new comment
                        location.reload(); // Simple solution for now
                    }
                } else {
                    alert(data.message || 'Erreur lors de l\'ajout du commentaire');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue');
            });
        });
        </script>
    </div>
</div>

