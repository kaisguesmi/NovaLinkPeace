<?php $config = require __DIR__ . '/../../../config/config.php'; $base = rtrim($config['app']['base_url'], '/'); ?>
<section class="mission-section">
    <div class="mission-container">
        <h2>Mes participations</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Initiative</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($participations as $participation): ?>
                    <tr>
                        <td><?= htmlspecialchars($participation['nom']) ?></td>
                        <td><?= $participation['date_inscription'] ?></td>
                        <td>
                            <a class="btn-danger" href="<?= $base ?>/?controller=participation&action=delete&id_initiative=<?= $participation['id_initiative'] ?>">Quitter</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

