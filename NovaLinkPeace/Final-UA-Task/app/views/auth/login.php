<?php 
$config = require __DIR__ . '/../../../config/config.php'; 
$base = rtrim($config['app']['base_url'], '/'); 
?>
<section class="mission-section">
    <div class="mission-container">
        <h2>Authentication disabled</h2>
        <p>Login is not required in this version of PeaceLink. You can browse stories, initiatives and participate anonymously.</p>
        <a href="<?= $base ?>/?controller=home&action=index" class="btn btn-primary btn-block">Go to Home</a>
    </div>
</section>

