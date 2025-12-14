<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <?php if (!empty($user) && ($user['is_admin'] ?? false)): ?>
                <div class="mb-4">
                    <a href="<?= $base ?>/?controller=post&action=create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Post
                    </a>
                </div>
            <?php endif; ?>

            <?php if (empty($posts)): ?>
                <div class="alert alert-info">
                    No posts found.
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="card mb-4">
                        <?php if (!empty($post['image_url'])): ?>
                            <img src="<?= $base . '/assets/uploads/' . htmlspecialchars($post['image_url']) ?>" class="card-img-top" alt="Post image">
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="<?= $base ?>/?controller=post&action=show&id=<?= $post['id_post'] ?>">
                                    <?= htmlspecialchars($post['title'] ?? 'Untitled Post') ?>
                                </a>
                            </h5>
                            
                            <p class="text-muted small mb-2">
                                Posted by <?= htmlspecialchars($post['author_name'] ?? 'Unknown') ?> 
                                on <?= date('F j, Y', strtotime($post['created_at'])) ?>
                            </p>
                            
                            <div class="card-text mb-3">
                                <?= nl2br(htmlspecialchars(mb_substr($post['content'] ?? '', 0, 300))) ?>
                                <?= (mb_strlen($post['content'] ?? '') > 300) ? '...' : '' ?>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="<?= $base ?>/?controller=post&action=show&id=<?= $post['id_post'] ?>" class="btn btn-sm btn-outline-primary">
                                    Read More <i class="fas fa-arrow-right"></i>
                                </a>
                                
                                <?php if (!empty($user) && ($user['is_admin'] ?? false)): ?>
                                    <div class="btn-group">
                                        <a href="<?= $base ?>/?controller=post&action=edit&id=<?= $post['id_post'] ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="#" onclick="confirmDelete(<?= $post['id_post'] ?>)" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($user) && ($user['is_admin'] ?? false)): ?>
<script>
function confirmDelete(postId) {
    if (confirm('Are you sure you want to delete this post?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= $base ?>/?controller=post&action=delete';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'id';
        input.value = postId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
    return false;
}
</script>
<?php endif; ?>
