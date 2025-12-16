<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
$formError = $formError ?? null;
$old = $old ?? ['title' => '', 'content' => ''];
?>

<div class="page-header">
    <div>
        <h1>Nouveau post</h1>
        <p class="page-subtitle">Partagez une nouvelle publication</p>
    </div>
    <a href="<?= $base ?>/?controller=histoire&action=index" class="btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
</div>

<div class="profile-card">
    <form method="post" action="<?= $base ?>/?controller=post&action=store" class="profile-fields" id="post-create-page-form">
        <div class="form-group">
            <label for="title">Titre (optionnel)</label>
            <input type="text"
                   id="title"
                   name="title"
                   class="form-control"
                   placeholder="Ajoutez un titre..."
                   value="<?= htmlspecialchars($old['title'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="content">Contenu *</label>
            <textarea id="content"
                      name="content"
                      rows="6"
                      class="form-control"
                      placeholder="Quoi de neuf ?"><?= htmlspecialchars($old['content'] ?? '') ?></textarea>
            <?php if (!empty($formError)): ?>
                <span class="error-message" style="display: inline-block; opacity: 1;">
                    <?= htmlspecialchars($formError) ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-paper-plane"></i> Publier
            </button>
            <a href="<?= $base ?>/?controller=histoire&action=index" class="btn-secondary">
                Annuler
            </a>
        </div>
    </form>
</div>
