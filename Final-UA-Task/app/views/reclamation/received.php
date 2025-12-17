<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<section class="table-card" style="margin-top:20px;">
    <div class="page-header" style="margin-bottom:16px;">
        <div>
            <h2>Signalements sur mes histoires</h2>
            <p class="page-subtitle">Suivez les réclamations reçues et leur statut admin</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a class="btn-secondary" href="<?= $base ?>/?controller=histoire&action=index">Retour aux stories</a>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Histoire</th>
                <th>Statut</th>
                <th>Description</th>
                <th>AI</th>
                <th>Créée</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($reclamations)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:16px;">Aucune réclamation sur vos histoires.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($reclamations as $r): ?>
                    <tr>
                        <td>#<?= (int)$r['id_reclamation'] ?></td>
                        <td><?= htmlspecialchars($r['histoire_titre'] ?? 'N/A') ?></td>
                        <td>
                            <?php
                                $statusLabelMap = [
                                    'nouvelle' => 'En attente',
                                    'en_cours' => 'En cours',
                                    'resolue' => 'Résolue',
                                    'acceptee' => 'Acceptée',
                                    'refusee' => 'Refusée',
                                ];
                                $status = $r['statut'] ?? 'nouvelle';
                                $label = $statusLabelMap[$status] ?? $status;
                                $badgeColor = [
                                    'acceptee' => '#1e88e5',
                                    'resolue' => '#27ae60',
                                    'refusee' => '#e74c3c',
                                    'en_cours' => '#f39c12',
                                    'nouvelle' => '#9b59b6',
                                ][$status] ?? '#9b59b6';
                            ?>
                            <span class="status-badge" style="background-color: <?= $badgeColor ?>1a; color: <?= $badgeColor ?>;">
                                <?= htmlspecialchars($label) ?>
                            </span>
                        </td>
                        <td style="max-width:360px;">
                            <?= htmlspecialchars(mb_strimwidth($r['description_personnalisee'] ?? '', 0, 160, '…')) ?><br>
                            <small style="color:#6b7280;">Par <?= htmlspecialchars($r['auteur_email'] ?? 'inconnu') ?></small>
                        </td>
                        <td>
                            <?php if (!empty($r['ai_score'])): ?>
                                <div style="font-weight:700; color:#0f172a;">Score <?= number_format((float)$r['ai_score'],1) ?>/100</div>
                                <div style="font-size:12px; color:#6b7280; max-width:200px;">"<?= htmlspecialchars(mb_strimwidth($r['ai_analysis'] ?? '', 0, 120, '…')) ?>"</div>
                            <?php else: ?>
                                <span style="color:#6b7280;">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($r['created_at'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>
