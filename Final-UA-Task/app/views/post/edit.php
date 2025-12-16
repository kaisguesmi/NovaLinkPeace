<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<div class="page-header">
    <div>
        <h1>Modifier le post</h1>
        <p class="page-subtitle">Mettez à jour votre publication</p>
    </div>
    <a href="<?= $base ?>/?controller=histoire&action=index" class="btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
</div>

<div class="profile-card">
    <form method="post" action="<?= $base ?>/?controller=post&action=update" class="profile-fields" id="post-edit-form">
        <input type="hidden" name="id" value="<?= $post['id_post'] ?>">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <div class="form-group">
            <label for="title">Titre (optionnel)</label>
            <input type="text"
                   id="title"
                   name="title"
                   class="form-control"
                   value="<?= htmlspecialchars($post['title'] ?? '') ?>"
                   placeholder="Ajoutez un titre...">
        </div>

        <div class="form-group">
            <label for="content">Contenu *</label>
            <textarea id="content"
                      name="content"
                      rows="6"
                      class="form-control"><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-save"></i> Mettre à jour
            </button>
            <a href="<?= $base ?>/?controller=histoire&action=index" class="btn-secondary">
                Annuler
            </a>
        </div>
    </form>
</div>
