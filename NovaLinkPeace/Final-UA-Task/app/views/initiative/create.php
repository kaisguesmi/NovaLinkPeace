<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<div class="page-header">
    <div>
        <h1>Créer une initiative</h1>
        <p class="page-subtitle">Proposez une nouvelle initiative à la communauté</p>
    </div>
    <a href="<?= $base ?>/?controller=initiative&action=index" class="btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
</div>

<div class="profile-card">
    <form method="post" action="<?= $base ?>/?controller=initiative&action=store" class="profile-fields" id="initiative-create-form">
        <div class="form-group">
            <label for="nom">Nom de l'initiative *</label>
            <input type="text" 
                   id="nom" 
                   name="nom" 
                   class="form-control"
                   placeholder="Ex: Nettoyage du parc local">
        </div>
        
        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" 
                      name="description" 
                      rows="5" 
                      class="form-control"
                      placeholder="Décrivez votre initiative..."></textarea>
        </div>
        
        <div class="form-group">
            <label for="date_evenement">Date de l'évènement *</label>
            <input type="datetime-local" 
                   id="date_evenement" 
                   name="date_evenement" 
                   class="form-control">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-check"></i> Soumettre
            </button>
            <a href="<?= $base ?>/?controller=initiative&action=index" class="btn-secondary">
                Annuler
            </a>
        </div>
    </form>
</div>
