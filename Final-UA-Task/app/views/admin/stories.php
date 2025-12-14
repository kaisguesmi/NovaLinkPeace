<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<section class="page-header">
    <div>
        <h1>Stories</h1>
        <p class="page-subtitle">All approved stories</p>
    </div>
</section>

<div class="table-card">
    <h3>Approved Stories</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Published At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($stories)): ?>
                <tr>
                    <td colspan="4" style="text-align:center; padding: 20px;">No approved stories.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($stories as $story): ?>
                    <tr>
                        <td><?= htmlspecialchars($story['titre']) ?></td>
                        <td><?= htmlspecialchars($story['nom_complet']) ?></td>
                        <td><?= htmlspecialchars($story['date_publication'] ?? '') ?></td>
                        <td style="display:flex; gap:8px;">
                            <a href="<?= $base ?>/?controller=histoire&action=show&id=<?= $story['id_histoire'] ?>" class="action-btn" title="View">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="<?= $base ?>/?controller=commentaires&action=index&id_histoire=<?= $story['id_histoire'] ?>" class="action-btn" title="View comments">
                                <i class="fa-solid fa-comments"></i>
                            </a>
                            <a href="<?= $base ?>/?controller=admin&action=deleteStory&id=<?= $story['id_histoire'] ?>" class="action-btn danger" title="Delete story" onclick="return confirm('Delete this story and all its comments?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
