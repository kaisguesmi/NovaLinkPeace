<?php $config = require __DIR__ . '/../../../config/config.php'; $base = rtrim($config['app']['base_url'], '/'); ?>
<section class="mission-section">
    <div class="mission-container">
        <h2>Modifier le commentaire</h2>
        <form method="post" action="<?= $base ?>/?controller=comment&action=update" class="form-card" id="comment-edit-form">
            <input type="hidden" name="id" value="<?= $comment['id_comment'] ?>">
            <textarea name="content" rows="4"><?= htmlspecialchars($comment['content']) ?></textarea>
            <button class="btn-hero-primary">Mettre à jour</button>
        </form>
    </div>
</section>
