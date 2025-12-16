<section class="table-card">
    <h3>Signalements reçus</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Auteur</th>
                <th>Statut</th>
                <th>Objet</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reclamations as $reclamation): ?>
                <tr>
                    <td>#<?= $reclamation['id_reclamation'] ?></td>
                    <td><?= htmlspecialchars($reclamation['auteur_email']) ?></td>
                    <td><?= htmlspecialchars($reclamation['statut']) ?></td>
                    <td><?= htmlspecialchars($reclamation['histoire_titre'] ?? $reclamation['commentaire_contenu'] ?? 'N/A') ?></td>
                    <td>
                        <a class="btn-hero-secondary" href="?controller=reclamation&action=edit&id=<?= $reclamation['id_reclamation'] ?>">Traiter</a>
                        <a class="btn-danger" href="?controller=reclamation&action=delete&id=<?= $reclamation['id_reclamation'] ?>">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

