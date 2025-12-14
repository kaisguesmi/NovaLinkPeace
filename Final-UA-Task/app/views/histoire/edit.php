<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<div class="page-header">
    <div>
        <h1>Modifier l'histoire</h1>
        <p class="page-subtitle">Mettez à jour votre histoire</p>
    </div>
    <a href="<?= $base ?>/?controller=histoire&action=show&id=<?= $story['id_histoire'] ?>" class="btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
</div>

<div class="profile-card">
    <form method="post" action="<?= $base ?>/?controller=histoire&action=update" class="profile-fields" id="story-edit-form">
        <input type="hidden" name="id" value="<?= $story['id_histoire'] ?>">
        
        <div class="form-group">
            <label for="titre">Titre *</label>
            <input type="text" 
                   id="titre" 
                   name="titre" 
                   value="<?= htmlspecialchars($story['titre']) ?>" 
                   class="form-control">
        </div>
        
        <div class="form-group">
            <label for="contenu">Contenu *</label>
            <textarea id="contenu" 
                      name="contenu" 
                      rows="6" 
                      class="form-control"><?= htmlspecialchars($story['contenu']) ?></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-save"></i> Mettre à jour
            </button>
            <a href="<?= $base ?>/?controller=histoire&action=show&id=<?= $story['id_histoire'] ?>" class="btn-secondary">
                Annuler
            </a>
        </div>
    </form>
</div>
