<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<div class="page-header">
    <div>
        <h1><?= htmlspecialchars($initiative['nom']) ?></h1>
        <p class="page-subtitle">Détails de l'initiative</p>
    </div>
    <a href="<?= $base ?>/?controller=initiative&action=index" class="btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
</div>

<div class="profile-card">
    <div class="story-content">
        <div style="margin-bottom: 20px;">
            <span class="status-badge <?= $initiative['statut'] === 'approuvee' ? 'active' : 'inactive' ?>" style="margin-bottom: 15px; display: inline-block;">
                <?= htmlspecialchars($initiative['statut']) ?>
            </span>
        </div>
        
        <h3 style="margin-bottom: 15px; color: var(--bleu-nuit);">Description</h3>
        <p style="font-size: 15px; line-height: 1.7; margin-bottom: 25px;">
            <?= nl2br(htmlspecialchars($initiative['description'])) ?>
        </p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px;">
            <div>
                <strong style="color: var(--gris-fonce);">Date de l'évènement</strong>
                <p style="margin-top: 5px;"><?= date('d/m/Y à H:i', strtotime($initiative['date_evenement'])) ?></p>
            </div>
            <div>
                <strong style="color: var(--gris-fonce);">Statut</strong>
                <p style="margin-top: 5px;"><?= htmlspecialchars($initiative['statut']) ?></p>
            </div>
        </div>
        
        <?php if (!empty($user)): ?>
            <form method="post" action="<?= $base ?>/?controller=participation&action=store" style="margin-top: 30px;">
                <input type="hidden" name="id_initiative" value="<?= $initiative['id_initiative'] ?>">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-hand-holding-heart"></i> Rejoindre l'initiative
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($participants)): ?>
    <div class="table-card" style="margin-top: 30px;">
        <h3>Participants (<?= count($participants) ?>)</h3>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Date d'inscription</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $participant): ?>
                        <tr>
                            <td><?= htmlspecialchars($participant['nom_complet'] ?? 'Anonyme') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($participant['date_inscription'])) ?></td>
                            <td>
                                <span class="status-badge <?= $participant['statut'] === 'approved' ? 'active' : 'inactive' ?>">
                                    <?= htmlspecialchars($participant['statut']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="table-card" style="margin-top: 30px;">
        <p style="text-align: center; color: #888; padding: 20px;">
            Aucun participant pour le moment.
        </p>
    </div>
<?php endif; ?>
