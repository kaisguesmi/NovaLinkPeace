<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<section class="page-header">
    <div>
        <h1>Reject Story</h1>
        <p class="page-subtitle">Provide an optional rejection reason</p>
    </div>
</section>

<div class="table-card">
    <h3><?= htmlspecialchars($story['titre']) ?></h3>
    <p style="margin-bottom:20px; white-space: pre-wrap;">
        <?= nl2br(htmlspecialchars($story['contenu'])) ?>
    </p>

    <form method="post" action="<?= $base ?>/?controller=admin&action=rejectStory" id="admin-reject-story-form">
        <input type="hidden" name="id" value="<?= $story['id_histoire'] ?>">
        <div class="form-group">
            <label for="rejection_reason">Rejection reason (optional)</label>
            <textarea id="rejection_reason" name="rejection_reason" rows="4" class="form-control" placeholder="Explain why this story is rejected..."></textarea>
        </div>
        <div class="form-actions" style="margin-top: 15px; display:flex; gap:10px;">
            <button type="submit" class="btn-danger">
                <i class="fa-solid fa-xmark"></i> Reject story
            </button>
            <a href="<?= $base ?>/?controller=admin&action=stories" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
