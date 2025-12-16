<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<div class="page-header">
    <div>
        <h1>Modifier l'initiative</h1>
        <p class="page-subtitle">Mettez à jour les informations de votre initiative</p>
    </div>
    <a href="<?= $base ?>/?controller=initiative&action=show&id=<?= $initiative['id_initiative'] ?>" class="btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
</div>

<div class="profile-card">
    <form method="post" action="<?= $base ?>/?controller=initiative&action=update" class="profile-fields" id="initiative-edit-form">
        <input type="hidden" name="id" value="<?= $initiative['id_initiative'] ?>">
        
        <div class="form-group">
            <label for="nom">Nom de l'initiative *</label>
            <input type="text" 
                   id="nom" 
                   name="nom" 
                   value="<?= htmlspecialchars($initiative['nom']) ?>" 
                   class="form-control">
        </div>
        
        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" 
                      name="description" 
                      rows="5" 
                      class="form-control"><?= htmlspecialchars($initiative['description']) ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="date_evenement">Date de l'évènement *</label>
            <input type="datetime-local" 
                   id="date_evenement" 
                   name="date_evenement" 
                   value="<?= date('Y-m-d\TH:i', strtotime($initiative['date_evenement'])) ?>" 
                   class="form-control">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-save"></i> Mettre à jour
            </button>
            <a href="<?= $base ?>/?controller=initiative&action=show&id=<?= $initiative['id_initiative'] ?>" class="btn-secondary">
                Annuler
            </a>
        </div>
    </form>
</div>
