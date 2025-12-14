<?php
$base = $this->baseUrl();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeaceLink - Admin</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/backoffice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-pO12Hjv6Yh86g9i9a0Xk5u7i0jGQX+v1Sk2cQ6Cq3aEvs7KqJsteVEh9UpAJZxkd06P88GJEY3E0P4r1XY7T2w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <?php include __DIR__ . '/../admin/partials/sidebar.php'; ?>

    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" type="button" onclick="document.querySelector('.sidebar').classList.toggle('collapsed');">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="navbar-brand" style="display:flex;align-items:center;gap:10px;">
                    <a href="<?= $this->baseUrl('?controller=admin&action=index') ?>" style="display:flex;align-items:center;gap:10px;">
                        <img src="<?= $this->asset('images/mon-logo.png') ?>" alt="Logo PeaceLink" class="logo-img" style="height:40px;">
                        <span class="site-name">PeaceLink</span>
                    </a>
                </div>
            </div>
            <div class="topbar-right">
                <!-- Space for future admin actions (notifications, profile, etc.) -->
            </div>
        </header>

        <div class="content-wrapper">
            <?= $content ?>
        </div>
    </div>
</body>
</html>

