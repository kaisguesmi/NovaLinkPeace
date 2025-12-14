<?php
// Ensure variables exist to avoid notices
$pendingPosts    = $pendingPosts ?? [];
$pendingComments = $pendingComments ?? [];
$approvedPosts   = $approvedPosts ?? [];
$rejectedPosts   = $rejectedPosts ?? [];
$approvedComments = $approvedComments ?? [];
$rejectedComments = $rejectedComments ?? [];
$allPosts = $allPosts ?? [];
?>

<section class="page-header">
    <div>
        <h1>Modération des contenus</h1>
        <p class="page-subtitre">Gérez les publications et commentaires de la communauté.</p>
    </div>
</section>

<?php include __DIR__ . '/../includes/flash_messages.php'; ?>

<div class="table-card">
    <h3>Publications en attente</h3>
    <?php if (empty($pendingPosts)): ?>
        <p style="padding: 16px;">Aucune publication en attente de modération.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingPosts as $post): ?>
                    <tr>
                        <td><?= htmlspecialchars($post['titre'] ?? 'Sans titre') ?></td>
                        <td><?= htmlspecialchars($post['nom_complet'] ?? $post['id_auteur']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($post['date_creation'])) ?></td>
                        <td class="action-buttons">
                            <form method="post" action="?controller=admin&action=approvePost" style="display:inline-block; margin-right: 8px;">
                                <input type="hidden" name="post_id" value="<?= (int) $post['id_post'] ?>">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-check"></i> Approuver
                                </button>
                            </form>
                            <form method="post" action="?controller=admin&action=rejectPost" style="display:inline-block; margin-right: 8px;">
                                <input type="hidden" name="post_id" value="<?= (int) $post['id_post'] ?>">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fa fa-times"></i> Rejeter
                                </button>
                            </form>
                            <form method="post" action="?controller=admin&action=deletePost" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement cette publication ?');">
                                <input type="hidden" name="post_id" value="<?= (int) $post['id_post'] ?>">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fa fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="table-card">
    <h3>Flux des publications (vue utilisateur)</h3>
    <?php if (empty($allPosts)): ?>
        <p style="padding: 16px;">Aucune publication à afficher.</p>
    <?php else: ?>
        <div class="posts-feed">
            <?php foreach ($allPosts as $post): ?>
                <?php include __DIR__ . '/../partials/post-card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="table-card">
    <h3>Publications approuvées</h3>
    <?php if (empty($approvedPosts)): ?>
        <p style="padding: 16px;">Aucune publication approuvée pour le moment.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($approvedPosts as $post): ?>
                    <tr>
                        <td><?= htmlspecialchars($post['titre'] ?? 'Sans titre') ?></td>
                        <td><?= htmlspecialchars($post['nom_complet'] ?? $post['id_auteur']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($post['date_creation'])) ?></td>
                        <td class="action-buttons">
                            <form method="post" action="?controller=admin&action=deletePost" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ? L\'auteur en sera notifié.');">
                                <input type="hidden" name="post_id" value="<?= (int) $post['id_post'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="table-card">
    <h3>Publications rejetées</h3>
    <?php if (empty($rejectedPosts)): ?>
        <p style="padding: 16px;">Aucune publication rejetée.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Date</th>
                    <th>Raison</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rejectedPosts as $post): ?>
                    <tr>
                        <td><?= htmlspecialchars($post['titre'] ?? 'Sans titre') ?></td>
                        <td><?= htmlspecialchars($post['nom_complet'] ?? $post['id_auteur']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($post['date_creation'])) ?></td>
                        <td><?= htmlspecialchars($post['moderation_notes'] ?? '-') ?></td>
                        <td class="action-buttons">
                            <form method="post" action="?controller=admin&action=deletePost" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement cette publication ?');">
                                <input type="hidden" name="post_id" value="<?= (int) $post['id_post'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="table-card">
    <h3>Commentaires approuvés</h3>
    <?php if (empty($approvedComments)): ?>
        <p style="padding: 16px;">Aucun commentaire approuvé pour le moment.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Contenu</th>
                    <th>Publication</th>
                    <th>Auteur</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($approvedComments as $comment): ?>
                    <tr>
                        <td><?= htmlspecialchars(substr($comment['content'], 0, 50)) . (strlen($comment['content']) > 50 ? '...' : '') ?></td>
                        <td><?= htmlspecialchars($comment['post_titre'] ?? 'Post #' . $comment['post_id']) ?></td>
                        <td><?= htmlspecialchars($comment['author_name'] ?? $comment['user_id']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></td>
                        <td class="action-buttons">
                            <form method="post" action="?controller=admin&action=deleteComment" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ? L\'auteur en sera notifié.');">
                                <input type="hidden" name="comment_id" value="<?= (int) $comment['id_comment'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="table-card">
    <h3>Commentaires rejetés</h3>
    <?php if (empty($rejectedComments)): ?>
        <p style="padding: 16px;">Aucun commentaire rejeté.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Contenu</th>
                    <th>Publication</th>
                    <th>Auteur</th>
                    <th>Date</th>
                    <th>Raison</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rejectedComments as $comment): ?>
                    <tr>
                        <td><?= htmlspecialchars(substr($comment['content'], 0, 50)) . (strlen($comment['content']) > 50 ? '...' : '') ?></td>
                        <td><?= htmlspecialchars($comment['post_titre'] ?? 'Post #' . $comment['post_id']) ?></td>
                        <td><?= htmlspecialchars($comment['author_name'] ?? $comment['user_id']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></td>
                        <td><?= htmlspecialchars($comment['moderation_notes'] ?? '-') ?></td>
                        <td class="action-buttons">
                            <form method="post" action="?controller=admin&action=deleteComment" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement ce commentaire ?');">
                                <input type="hidden" name="comment_id" value="<?= (int) $comment['id_comment'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var labelsToHide = ['Stories', 'Comments', 'Initiatives', 'Users', 'Settings'];

    var links = document.querySelectorAll('nav a, .sidebar a, .nav a');
    links.forEach(function (link) {
        var text = link.textContent.trim();
        if (labelsToHide.indexOf(text) !== -1) {
            var item = link.closest('li, .nav-item');
            if (item) {
                item.style.display = 'none';
            } else {
                link.style.display = 'none';
            }
        }
    });
});
</script>
