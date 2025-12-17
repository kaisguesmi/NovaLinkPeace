<section class="mission-section">
    <div class="mission-container">
        <h2>Signaler un contenu</h2>
        <form method="post" action="<?= $this->baseUrl('?controller=reclamation&action=store') ?>" class="form-card" id="reclamation-create-form">
            <input type="hidden" name="id_histoire_cible" value="<?= htmlspecialchars($target['histoire'] ?? '') ?>">
            <input type="hidden" name="id_commentaire_cible" value="<?= htmlspecialchars($target['commentaire'] ?? '') ?>">
            <input type="text" name="website" value="" style="position:absolute;left:-9999px" tabindex="-1" autocomplete="off">
            <label>Causes
                <select name="causes[]" multiple>
                    <?php foreach ($causes as $cause): ?>
                        <option value="<?= $cause['id_cause'] ?>"><?= htmlspecialchars($cause['libelle']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Description
                <textarea name="description_personnalisee" rows="5"></textarea>
            </label>
            <button class="btn-hero-primary">Envoyer</button>
        </form>
    </div>
</section>

