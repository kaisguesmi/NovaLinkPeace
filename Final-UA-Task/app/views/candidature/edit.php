<section class="table-card">
    <h3>Mettre à jour la candidature #<?= $candidature['id_candidature'] ?></h3>
    <form method="post" action="?controller=candidature&action=update" class="form-card" id="candidature-edit-form">
        <input type="hidden" name="id" value="<?= $candidature['id_candidature'] ?>">
        <label>Statut
            <select name="statut">
                <option value="pending" <?= $candidature['statut'] === 'pending' ? 'selected' : '' ?>>En attente</option>
                <option value="approved" <?= $candidature['statut'] === 'approved' ? 'selected' : '' ?>>Approuvée</option>
                <option value="rejected" <?= $candidature['statut'] === 'rejected' ? 'selected' : '' ?>>Rejetée</option>
            </select>
        </label>
        <button class="btn-primary">Mettre à jour</button>
    </form>
</section>

