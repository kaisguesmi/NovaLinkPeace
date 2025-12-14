<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<div class="page-header">
    <div>
        <h1>Raconter une nouvelle histoire</h1>
        <p class="page-subtitle">Partagez votre expérience avec la communauté</p>
    </div>
    <a href="<?= $base ?>/?controller=histoire&action=index" class="btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
</div>

<div class="profile-card">
    <form method="post" action="<?= $base ?>/?controller=histoire&action=store" class="profile-fields" id="story-create-form">
        <div class="form-group">
            <label for="titre">Titre *</label>
            <input type="text" 
                   id="titre" 
                   name="titre" 
                   class="form-control"
                   placeholder="Donnez un titre à votre histoire">
        </div>
        
        <div class="form-group">
            <label for="contenu">Contenu *</label>
            <textarea id="contenu" 
                      name="contenu" 
                      rows="6" 
                      class="form-control"
                      placeholder="Racontez votre histoire..."></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-paper-plane"></i> Partager
            </button>
            <a href="<?= $base ?>/?controller=histoire&action=index" class="btn-secondary">
                Annuler
            </a>
        </div>
    </form>
</div>
