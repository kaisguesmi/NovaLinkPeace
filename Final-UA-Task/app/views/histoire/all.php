<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<div class="container mx-auto px-4 py-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Community Stories</h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">Read inspiring stories shared by our community members</p>
    </div>

    <div class="stories-grid">
        <?php if (!empty($stories)): ?>
            <?php foreach ($stories as $story): 
                // Get initials for avatar
                $initials = '';
                $nameParts = explode(' ', $story['nom_complet']);
                foreach ($nameParts as $part) {
                    $initials .= strtoupper(substr($part, 0, 1));
                    if (strlen($initials) >= 2) break;
                }
                
                // Format date
                $date = new DateTime($story['date_publication']);
                $now = new DateTime();
                $interval = $date->diff($now);
                
                if ($interval->y > 0) {
                    $timeAgo = $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
                } elseif ($interval->m > 0) {
                    $timeAgo = $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
                } elseif ($interval->d > 0) {
                    $timeAgo = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
                } elseif ($interval->h > 0) {
                    $timeAgo = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
                } else {
                    $timeAgo = 'Just now';
                }
            ?>
            <article class="story-card">
                <div class="story-header">
                    <div class="story-author">
                        <?php if (!empty($story['photo_profil'])): ?>
                            <img src="<?= htmlspecialchars($story['photo_profil']) ?>" alt="<?= htmlspecialchars($story['nom_complet']) ?>" class="story-avatar">
                        <?php else: ?>
                            <div class="story-avatar"><?= $initials ?></div>
                        <?php endif; ?>
                        <div>
                            <div class="story-author-name"><?= htmlspecialchars($story['nom_complet']) ?></div>
                            <div class="story-date"><?= $timeAgo ?></div>
                        </div>
                    </div>
                </div>
                <div class="story-content">
                    <h3><?= htmlspecialchars($story['titre']) ?></h3>
                    <p><?= nl2br(htmlspecialchars($story['contenu'])) ?></p>
                </div>
            </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500 text-lg">No stories have been shared yet.</p>
                <a href="<?= $base ?>/?controller=histoire&action=index" class="mt-4 inline-block btn-primary">Be the first to share your story</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-8 text-center">
        <a href="#" onclick="window.history.back(); return false;" class="btn-secondary">Back to Home</a>
    </div>
</div>
