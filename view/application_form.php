<?php include 'templates/header.php'; ?>
<div class="page-header">
    <h1>Postuler à la mission : "<?= htmlspecialchars($offer['title']) ?>"</h1>
</div>
<div class="profile-card">
    <form id="application-form" action="index.php?action=submit_application" method="POST" novalidate>
        <input type="hidden" name="offer_id" value="<?= $offer['id'] ?>">
        <div class="profile-fields">
            <div class="form-group">
                <label for="candidate_name">Votre Nom et Prénom</label>
                <input type="text" id="candidate_name" name="candidate_name" placeholder="Ex: Camille Dupont">
                <div class="error-message"></div>
            </div>
            <div class="form-group">
                <label for="candidate_email">Votre Adresse Email</label>
                <input type="email" id="candidate_email" name="candidate_email" placeholder="Ex: camille@email.com">
                <div class="error-message"></div>
            </div>
            <div class="form-group">
                <label for="motivation">Vos Motivations</label>
                <textarea id="motivation" name="motivation" rows="6" placeholder="Décrivez votre expérience..."></textarea>
                <div class="error-message"></div>
            </div>
            <div class="form-actions">
                <a href="index.php" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane"></i> Envoyer</button>
            </div>
        </div>
    </form>
</div>
<?php include 'templates/footer.php'; ?>