<?php $config = require __DIR__ . '/../../../config/config.php'; $base = rtrim($config['app']['base_url'], '/'); ?>
<section class="mission-section">
    <div class="mission-container">
        <h2>Nouveau commentaire</h2>
        <form method="post" action="<?= $base ?>/?controller=comment&action=store" class="form-card" id="comment-create-form">
            <input type="hidden" name="post_id" value="<?= $post['id_post'] ?>">
            <textarea name="content" rows="4" placeholder="Votre commentaire"></textarea>
            <button class="btn-hero-primary">Publier</button>
        </form>
    </div>
</section>
