<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../FrontOffice/login.php');
    exit();
}

require_once __DIR__ . '/../../Model/Database.php';
require_once __DIR__ . '/../../Model/Histoire.php';

$database = new Database();
$db = $database->getConnection();
$histoireModel = new Histoire($db);

$reclamations = $histoireModel->getAllReclamations();
$successMsg = $_SESSION['success_msg'] ?? '';
$errorMsg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réclamations Histoires - BackOffice</title>
    <link rel="stylesheet" href="backofficeStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="sidebar">
    <div class="sidebar-header">
        <a href="../FrontOffice/index.php" class="logo">
            <img src="mon-logo.png" alt="Logo PeaceLink">
            <span>PeaceLink</span>
        </a>
    </div>
    <nav class="sidebar-nav">
        <a href="backoffice.php" class="nav-item"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></a>
        <a href="reclamations.php" class="nav-item active"><i class="fa-solid fa-flag"></i><span>Reclamations</span></a>
        <a href="../FrontOffice/index.php" class="nav-item"><i class="fa-solid fa-globe"></i><span>View the Site</span></a>
    </nav>
    <div class="sidebar-footer">
        <div class="user-profile">
            <i class="fa-solid fa-user-shield user-avatar"></i>
            <div class="user-info">
                <span class="user-name">Admin</span>
                <span class="user-role">Super Admin</span>
            </div>
        </div>
        <a href="../../Controller/UtilisateurController.php?action=logout" class="logout-btn" aria-label="Logout">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </div>
</div>

<div class="main-content">
    <header class="topbar">
        <div class="topbar-left"><button class="menu-toggle" id="menu-toggle"><i class="fa-solid fa-bars"></i></button></div>
        <div class="topbar-right"><div class="user-info">Réclamations histoires</div></div>
    </header>

    <main class="content-wrapper">
        <div class="page-header">
            <h1>Réclamations</h1>
            <p class="page-subtitle">Accepter pour supprimer l'histoire, refuser pour la conserver.</p>
        </div>

        <?php if ($successMsg): ?><div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div><?php endif; ?>
        <?php if ($errorMsg): ?><div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div><?php endif; ?>

        <div class="table-card">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Auteur</th>
                            <th>Histoire</th>
                            <th>Description</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($reclamations)): ?>
                        <tr><td colspan="6">Aucune réclamation.</td></tr>
                    <?php else: ?>
                        <?php foreach ($reclamations as $rec): ?>
                            <tr>
                                <td>#<?= (int)$rec['id_reclamation'] ?></td>
                                <td><?= htmlspecialchars($rec['auteur_nom']) ?></td>
                                <td><?= htmlspecialchars($rec['histoire_titre'] ?? 'N/A') ?></td>
                                <td><?= nl2br(htmlspecialchars($rec['description_personnalisee'])) ?></td>
                                <td><?= htmlspecialchars($rec['statut']) ?></td>
                                <td class="actions-cell">
                                    <form action="../../Controller/HistoireController.php" method="POST" style="display:inline-block;">
                                        <input type="hidden" name="action" value="accepter_reclamation">
                                        <input type="hidden" name="id_reclamation" value="<?= (int)$rec['id_reclamation'] ?>">
                                        <button type="submit" class="btn-validate" title="Accepter et supprimer l'histoire"><i class="fa-solid fa-check"></i></button>
                                    </form>
                                    <form action="../../Controller/HistoireController.php" method="POST" style="display:inline-block;">
                                        <input type="hidden" name="action" value="refuser_reclamation">
                                        <input type="hidden" name="id_reclamation" value="<?= (int)$rec['id_reclamation'] ?>">
                                        <button type="submit" class="btn-delete" title="Refuser"><i class="fa-solid fa-xmark"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
const toggle = document.getElementById('menu-toggle');
const sidebar = document.querySelector('.sidebar');
toggle?.addEventListener('click', () => sidebar.classList.toggle('open'));
</script>
</body>
</html>
