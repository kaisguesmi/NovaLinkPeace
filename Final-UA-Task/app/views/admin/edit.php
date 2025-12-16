<section class="table-card">
    <h3>Modifier l'offre</h3>
    <form method="post" action="?controller=admin&action=update" class="form-card" id="admin-offer-edit-form">
        <input type="hidden" name="id" value="<?= $offre['id_offre'] ?>">
        <label>Titre
            <input type="text" name="titre" value="<?= htmlspecialchars($offre['titre']) ?>">
        </label>
        <label>Description
            <textarea name="description" rows="4"><?= htmlspecialchars($offre['description']) ?></textarea>
        </label>
        <label>Statut
            <select name="statut">
                <option value="draft" <?= $offre['statut'] === 'draft' ? 'selected' : '' ?>>Brouillon</option>
                <option value="published" <?= $offre['statut'] === 'published' ? 'selected' : '' ?>>Publiée</option>
            </select>
        </label>
        <button class="btn-primary">Mettre à jour</button>
    </form>
</section>

