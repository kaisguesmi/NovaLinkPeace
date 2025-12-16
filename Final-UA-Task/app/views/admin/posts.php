<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Gestion des publications</h1>
    </div>

    <?php if (empty($posts)): ?>
        <div class="alert alert-info">
            Aucune publication trouvée.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($posts as $post): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($post['image_url'])): ?>
                            <img src="<?= $base . '/assets/uploads/' . htmlspecialchars($post['image_url']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($post['title'] ?? 'Image de la publication') ?>"
                                 style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <?= htmlspecialchars($post['title'] ?? 'Sans titre') ?>
                            </h5>
                            
                            <p class="text-muted small mb-2">
                                Publié par <strong><?= htmlspecialchars($post['author_name'] ?? 'Utilisateur inconnu') ?></strong><br>
                                <small>Le <?= date('d/m/Y à H:i', strtotime($post['created_at'])) ?></small>
                            </p>
                            
                            <div class="card-text mb-3 flex-grow-1">
                                <?= nl2br(htmlspecialchars(mb_substr($post['content'] ?? '', 0, 200))) ?>
                                <?= (mb_strlen($post['content'] ?? '') > 200) ? '...' : '' ?>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <span class="badge bg-<?= $post['status'] === 'approved' ? 'success' : ($post['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                    <?= ucfirst($post['status'] ?? 'inconnu') ?>
                                </span>
                                
                                <div class="btn-group">
                                    <a href="<?= $base ?>/?controller=post&action=show&id=<?= $post['id_post'] ?>" 
                                       class="btn btn-sm btn-outline-primary"
                                       target="_blank">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                    
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="confirmDelete(<?= $post['id_post'] ?>)">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($post['comment_count'])): ?>
                            <div class="card-footer text-muted">
                                <i class="far fa-comment"></i> 
                                <?= (int)$post['comment_count'] ?> commentaire<?= $post['comment_count'] > 1 ? 's' : '' ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmDelete(postId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette publication ? L\'auteur en sera notifié.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= $base ?>/?controller=admin&action=deletePost';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'post_id';
        input.value = postId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
    return false;
}
</script>
