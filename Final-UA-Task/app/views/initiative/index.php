<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<div class="page-header">
    <div>
        <h1>Initiatives</h1>
        <p class="page-subtitle">Découvrez et participez aux initiatives locales</p>
    </div>
    <?php if (!empty($user)): ?>
        <a href="<?= $base ?>/?controller=initiative&action=create" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Créer une initiative
        </a>
    <?php endif; ?>
</div>

<?php if ($flash): ?>
    <div class="flash-message" style="background-color: var(--vert-doux); color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <?= htmlspecialchars($flash) ?>
    </div>
<?php endif; ?>

<?php if (empty($initiatives)): ?>
    <div class="empty-state" style="text-align: center; padding: 40px; background: var(--blanc-pur); border-radius: var(--border-radius); box-shadow: var(--card-shadow);">
        <i class="fa-solid fa-inbox" style="font-size: 48px; color: var(--gris-moyen); margin-bottom: 15px;"></i>
        <p style="color: #888;">Aucune initiative pour le moment.</p>
    </div>
<?php else: ?>
    <div class="stories-grid">
        <?php foreach ($initiatives as $initiative): ?>
            <div class="story-card">
                <div class="story-header">
                    <span><i class="fa-solid fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($initiative['date_evenement'])) ?></span>
                    <span class="status-badge <?= $initiative['statut'] === 'approuvee' ? 'active' : 'inactive' ?>">
                        <?= htmlspecialchars($initiative['statut']) ?>
                    </span>
                </div>
                <div class="story-content">
                    <h3><?= htmlspecialchars($initiative['nom']) ?></h3>
                    <p><?= nl2br(htmlspecialchars($initiative['description'])) ?></p>
                    <p style="margin-top: 15px; color: #888; font-size: 14px;">
                        <i class="fa-solid fa-user"></i> Créateur : <?= htmlspecialchars($initiative['nom_complet'] ?? 'Anonyme') ?>
                    </p>
                </div>
                <div class="story-actions">
                    <a href="<?= $base ?>/?controller=initiative&action=show&id=<?= $initiative['id_initiative'] ?>" class="btn-primary">
                        <i class="fa-solid fa-eye"></i> Voir les détails
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
