<?php 
$config = require __DIR__ . '/../../../config/config.php'; 
$base = rtrim($config['app']['base_url'], '/');
?>
<section class="mission-section">
    <div class="mission-container">
        <h2>Account creation disabled</h2>
        <p>User registration is disabled in this version of PeaceLink. All features are available without an account.</p>
        <a href="<?= $base ?>/?controller=home&action=index" class="btn btn-primary btn-block">Go to Home</a>
    </div>
</section>

