<?php
$allPosts = $allPosts ?? [];
?>

<section class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p class="page-subtitle">Surveillez les contributions PeaceLink.</p>
    </div>
    <a class="btn-primary" href="?controller=admin&action=create"><i class="fa-solid fa-plus"></i> Nouvelle offre</a>
</section>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-book-open"></i></div>
        <div>
            <p class="stat-label">Stories</p>
            <h2 class="stat-value"><?= count($stories) ?></h2>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-hand-holding-heart"></i></div>
        <div>
            <p class="stat-label">Initiatives</p>
            <h2 class="stat-value"><?= count($initiatives) ?></h2>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-file-signature"></i></div>
        <div>
            <p class="stat-label">Candidatures</p>
            <h2 class="stat-value"><?= count($candidatures) ?></h2>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-flag"></i></div>
        <div>
            <p class="stat-label">Réclamations</p>
            <h2 class="stat-value"><?= count($reclamations) ?></h2>
        </div>
    </div>
</div>

<div class="table-card">
    <h3>Réclamations</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Auteur</th>
                <th>Objet</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reclamations as $reclamation): ?>
                <tr>
                    <td><?= htmlspecialchars($reclamation['auteur_email']) ?></td>
                    <td><?= htmlspecialchars($reclamation['histoire_titre'] ?? $reclamation['commentaire_contenu'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($reclamation['statut']) ?></td>
                    <td>
                        <a class="btn-hero-secondary" href="?controller=reclamation&action=edit&id=<?= $reclamation['id_reclamation'] ?>">Traiter</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="table-card">
    <h3>Publications (vue tableau de bord)</h3>
    <?php if (empty($allPosts)): ?>
        <p style="padding: 16px;">Aucune publication à afficher.</p>
    <?php else: ?>
        <div class="posts-feed">
            <?php foreach ($allPosts as $post): ?>
                <?php $isAdminDashboard = true; include __DIR__ . '/../partials/post-card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Admin-only helpers for the publications feed
function deletePost(postId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette publication ? L\'auteur en sera notifié.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?controller=admin&action=deletePost&from=index';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'post_id';
        input.value = postId;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteCommentAdmin(commentId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ? L\'auteur en sera notifié.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?controller=admin&action=deleteComment&from=index';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'comment_id';
        input.value = commentId;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
    return false;
}

function toggleComments(postId) {
    var commentsSection = document.getElementById('comments-' + postId);
    if (commentsSection) {
        commentsSection.style.display = commentsSection.style.display === 'none' ? 'block' : 'none';
    }
}

// Simple no-op for reactions in admin view (visual only)
function toggleReaction(postId, type) {
    return false;
}

document.addEventListener('DOMContentLoaded', function () {
    var labelsToHide = ['Stories', 'Comments', 'Initiatives', 'Users', 'Settings'];
    // Look for links in the admin sidebar and hide their parent items
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
