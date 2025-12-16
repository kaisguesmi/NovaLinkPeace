<?php if (isset($_SESSION['flash'])): ?>
    <?php if (is_array($_SESSION['flash'])): ?>
        <?php foreach ($_SESSION['flash'] as $type => $message): ?>
            <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show" role="alert">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= $_SESSION['flash'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
