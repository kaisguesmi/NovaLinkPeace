<section class="table-card">
    <h3>Mettre à jour la réclamation #<?= $reclamation['id_reclamation'] ?></h3>
    <form method="post" action="?controller=reclamation&action=update" class="form-card" id="reclamation-edit-form">
        <input type="hidden" name="id" value="<?= $reclamation['id_reclamation'] ?>">
        <label>Statut
            <select name="statut">
                <option value="nouvelle" <?= $reclamation['statut'] === 'nouvelle' ? 'selected' : '' ?>>Nouvelle</option>
                <option value="en_cours" <?= $reclamation['statut'] === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                <option value="resolue" <?= $reclamation['statut'] === 'resolue' ? 'selected' : '' ?>>Résolue</option>
            </select>
        </label>
        <label>Causes
            <select name="causes[]" multiple>
                <?php foreach ($causes as $cause): ?>
                    <option value="<?= $cause['id_cause'] ?>"><?= htmlspecialchars($cause['libelle']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="btn-primary">Mettre à jour</button>
    </form>
</section>

