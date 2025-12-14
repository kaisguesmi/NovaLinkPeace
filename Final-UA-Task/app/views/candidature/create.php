<?php $config = require __DIR__ . '/../../../config/config.php'; $base = rtrim($config['app']['base_url'], '/'); ?>
<section class="mission-section">
    <div class="mission-container">
        <h2>Postuler à <?= htmlspecialchars($offre['titre'] ?? '') ?></h2>
        <form method="post" action="<?= $base ?>/?controller=candidature&action=store" class="form-card" id="candidature-create-form">
            <input type="hidden" name="id_offre" value="<?= $offre['id_offre'] ?? '' ?>">
            <label>Motivation
                <textarea name="motivation" rows="5"></textarea>
            </label>
            <button class="btn-hero-primary">Envoyer</button>
        </form>
    </div>
</section>

