<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<div class="page-header">
    <div>
        <h1>Profile unavailable</h1>
        <p class="page-subtitle">User profiles are disabled while the site runs without authentication.</p>
    </div>
</div>

<div class="profile-card">
    <p>All interactions are anonymous in this version of PeaceLink. You can still share stories and join initiatives without an account.</p>
    <a href="<?= $base ?>/?controller=home&action=index" class="btn-primary" style="margin-top:16px; display:inline-block;">Return to Home</a>
</div>

