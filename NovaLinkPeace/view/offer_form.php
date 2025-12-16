<?php include 'templates/header.php'; ?>

<?php
    $is_edit = isset($offer);
    $form_action = $is_edit ? 'index.php?action=update&id=' . $offer['id'] : 'index.php?action=store';
    
    // Valeurs par défaut ou récupérées de la BDD
    $title_val = $is_edit ? htmlspecialchars($offer['title']) : '';
    $desc_val = $is_edit ? htmlspecialchars($offer['description']) : '';
    $max_val = $is_edit ? $offer['max_candidates'] : 5;
    $key_val = $is_edit ? htmlspecialchars($offer['keywords']) : '';
?>

<div class="page-header">
    <h1><?= $is_edit ? 'Modifier l\'offre' : 'Créer une nouvelle offre' ?></h1>
</div>

<div class="profile-card">
    <form id="offer-form" action="<?= $form_action ?>" method="POST" novalidate>
        
        <div class="profile-fields">
            
            <!-- TITRE -->
            <div class="form-group">
                <label for="title">Titre de la mission <span style="color:var(--rouge-alerte)">*</span></label>
                <input type="text" id="title" name="title" value="<?= $title_val ?>" placeholder="Ex: Développeur PHP Fullstack" required>
                <div class="error-message"></div>
            </div>

            <!-- MAX CANDIDATS -->
            <div class="form-group">
                <label for="max_candidates">Quota de candidats</label>
                <div style="display:flex; align-items:center; gap:10px;">
                    <input type="number" id="max_candidates" name="max_candidates" 
                           value="<?= $max_val ?>" min="1" max="100" style="width:100px;">
                    <span style="font-size:13px; color:#777;">personnes max.</span>
                </div>
            </div>

            <!-- KEYWORDS (ATS + IA Générative) -->
            <div class="form-group">
                <label for="keywords">Mots-clés (Compétences)</label>
                <p style="font-size:12px; color:#888; margin-bottom:5px;">Utilisés pour le filtrage automatique ET pour la génération de texte.</p>
                <input type="text" id="keywords" name="keywords" value="<?= $key_val ?>" placeholder="Ex: PHP, SQL, Rigueur, Anglais">
            </div>

            <!-- DESCRIPTION AVEC BOUTON IA -->
            <div class="form-group">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <label for="description" style="margin-bottom:0;">Description complète <span style="color:var(--rouge-alerte)">*</span></label>
                    
                    <!-- BOUTON GÉNÉRATEUR IA -->
                    <button type="button" id="btn-generate-ai" class="btn btn-primary" style="padding: 5px 12px; font-size: 12px; background-color: var(--violet-admin); border:none;">
                        <i class="fas fa-magic"></i> Générer avec l'IA
                    </button>
                </div>
                
                <textarea id="description" name="description" rows="12" required placeholder="Remplissez manuellement ou cliquez sur le bouton magique..."><?= $desc_val ?></textarea>
                <div class="error-message"></div>
            </div>

            <!-- BOUTONS -->
            <div class="form-actions">
                <a href="index.php?role=admin" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            </div>
            
        </div>
    </form>
</div>

<?php include 'templates/footer.php'; ?>