<?php $config = require __DIR__ . '/../../../config/config.php'; $base = rtrim($config['app']['base_url'], '/'); ?>
<section class="mission-section">
    <div class="mission-container">
        <h2>Offres solidaires</h2>
        <div class="mission-grid">
            <?php foreach ($offres as $offre): ?>
                <article class="mission-card">
                    <h3><?= htmlspecialchars($offre['titre']) ?></h3>
                    <p><?= nl2br(htmlspecialchars(substr($offre['description'], 0, 150))) ?></p>
                    <p>Statut : <?= htmlspecialchars($offre['statut']) ?></p>
                    <a class="btn-hero-secondary" href="<?= $base ?>/?controller=candidature&action=create&id_offre=<?= $offre['id_offre'] ?>">Postuler</a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if (!empty($candidatures)): ?>
<section class="mission-section">
    <div class="mission-container">
        <h2>Mes candidatures</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Offre</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($candidatures as $cand): ?>
                    <tr>
                        <td><?= htmlspecialchars($cand['offre_titre']) ?></td>
                        <td><?= htmlspecialchars($cand['statut']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>

