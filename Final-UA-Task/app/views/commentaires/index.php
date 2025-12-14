<?php $config = require __DIR__ . '/../../../config/config.php'; $base = rtrim($config['app']['base_url'], '/'); ?>
<section class="mission-section">
    <div class="mission-container">
        <h2>Commentaires</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Contenu</th>
                    <th>Auteur</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td><?= htmlspecialchars($comment['contenu']) ?></td>
                        <td><?= htmlspecialchars($comment['email']) ?></td>
                        <td><?= $comment['date_publication'] ?></td>
                        <td>
                            <a href="<?= $base ?>/?controller=commentaires&action=edit&id=<?= $comment['id_commentaire'] ?>">Modifier</a>
                            <a href="<?= $base ?>/?controller=commentaires&action=delete&id=<?= $comment['id_commentaire'] ?>">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

