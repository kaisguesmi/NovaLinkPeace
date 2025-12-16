<?php $config = require __DIR__ . '/../../../config/config.php'; $base = rtrim($config['app']['base_url'], '/'); ?>
<section class="mission-section">
    <div class="mission-container">
        <h2>Modifier le commentaire</h2>
        <form method="post" action="<?= $base ?>/?controller=commentaires&action=update" class="form-card" id="story-comment-edit-form">
            <input type="hidden" name="id" value="<?= $comment['id_commentaire'] ?>">
            <textarea name="contenu" rows="4"><?= htmlspecialchars($comment['contenu']) ?></textarea>
            <button class="btn-hero-primary">Mettre à jour</button>
        </form>
    </div>
</section>

