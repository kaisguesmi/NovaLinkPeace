<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'] ?? '', '/');
?>

<div class="page-header">
    <div>
        <h1>Mes réclamations</h1>
        <p class="page-subtitle">Suivez vos signalements et leurs statuts.</p>
    </div>
    <a class="btn-secondary" href="<?= $base ?>/?controller=histoire&action=index">Retour</a>
</div>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Objet</th>
                <th>Statut</th>
                <th>Score AI</th>
                <th>Analyse</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($reclamations)): ?>
            <tr><td colspan="6">Aucune réclamation.</td></tr>
        <?php else: ?>
            <?php foreach ($reclamations as $r): ?>
            <tr>
                <td>#<?= (int)$r['id_reclamation'] ?></td>
                <td><?= htmlspecialchars($r['histoire_titre'] ?? $r['commentaire_contenu'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($r['statut']) ?></td>
                <td><?= $r['ai_score'] !== null ? htmlspecialchars(number_format((float)$r['ai_score'],1)) . ' / 100' : 'N/A' ?></td>
                <td><?= htmlspecialchars($r['ai_analysis'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($r['created_at'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>