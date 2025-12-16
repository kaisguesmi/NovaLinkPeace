<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<div class="page-header">
    <div>
        <h1>Profile editing disabled</h1>
        <p class="page-subtitle">Profiles and account settings are not available while authentication is disabled.</p>
    </div>
</div>

<div class="profile-card">
    <p>Your participation on PeaceLink is currently anonymous. There is no profile to edit.</p>
    <a href="<?= $base ?>/?controller=home&action=index" class="btn-secondary" style="margin-top:16px; display:inline-block;">Back to Home</a>
</div>

