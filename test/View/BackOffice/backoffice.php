<?php
session_start();

// 1. SÉCURITÉ : Vérifier si l'utilisateur est connecté ET est un Admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Si pas admin, retour à la connexion
    header("Location: ../FrontOffice/login.php");
    exit();
}

// 2. INCLUSIONS
include_once __DIR__ . '/../../Model/Database.php';
include_once __DIR__ . '/../../Model/Utilisateur.php';
include_once __DIR__ . '/../../Model/Histoire.php';

$database = new Database();
$db = $database->getConnection();
$utilisateur = new Utilisateur($db);
$histoireModel = new Histoire($db);

// 3. RÉCUPÉRATION DES DONNÉES
$stats = $utilisateur->getDashboardStats();
$listeOrganisations = $utilisateur->getAllOrganisations();
$listeClients = $utilisateur->getAllClients();
$listeHistoires = $histoireModel->getAllStories();
// Récupérer les bannis
$listeBannis = $utilisateur->getAllBannedUsers();
$pendingReclamations = 0;
try {
    $stmtRec = $db->query("SELECT COUNT(*) FROM reclamation WHERE statut = 'nouvelle'");
    $pendingReclamations = (int) $stmtRec->fetchColumn();
} catch (Exception $e) {
    $pendingReclamations = 0;
}
$adminName = $_SESSION['username'] ?? 'Administrateur';

// Gestion des messages de succès/erreur (flash messages)
$msg_success = "";
$msg_error = "";
if (isset($_SESSION['success_msg'])) {
    $msg_success = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}
if (isset($_SESSION['error_msg'])) {
    $msg_error = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office - PeaceLink</title>
    <link rel="stylesheet" href="backofficeStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Petits ajouts CSS pour les boutons d'action */
        .btn-validate { background-color: #27ae60; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
        .btn-delete { background-color: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
        .status-pending { background-color: #f39c12; color: white; padding: 2px 6px; border-radius: 4px; font-size: 12px; }
        .status-verified { background-color: #27ae60; color: white; padding: 2px 6px; border-radius: 4px; font-size: 12px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
        .nav-badge { background:#e74c3c; color:#fff; border-radius:10px; padding:2px 8px; font-size:12px; margin-left:6px; }
    </style>
</head>
<body>

    <!-- =========== 1. Barre Latérale (Sidebar) =========== -->
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="../FrontOffice/index.php" class="logo">
                <img src="mon-logo.png" alt="Logo PeaceLink">
                <span>PeaceLink</span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <a href="backoffice.php" class="nav-item active">
                <i class="fa-solid fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <a href="#organisations" class="nav-item">
                <i class="fa-solid fa-building"></i>
                <span>Organizations</span>
            </a>
            <a href="#clients" class="nav-item">
                <i class="fa-solid fa-users"></i>
                <span>Clients</span>
            </a>
            <a href="#stories" class="nav-item">
                <i class="fa-solid fa-book-open"></i>
                <span>Histoires</span>
            </a>
            <a href="reclamations.php" class="nav-item">
                <i class="fa-solid fa-flag"></i>
                <span>Reclamations</span>
                <?php if ($pendingReclamations > 0): ?>
                    <span class="nav-badge"><?php echo $pendingReclamations; ?></span>
                <?php endif; ?>
            </a>
             <a href="#banned" class="nav-item">
                <i class="fa-solid fa-user-slash"></i> <!-- Icône utilisateur barré -->
                <span>Banned Users</span>
            </a>
            <!-- Lien retour Front Office -->
            <a href="../FrontOffice/index.php" class="nav-item">
                <i class="fa-solid fa-globe"></i>
                <span>View the Site</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-profile">
                <i class="fa-solid fa-user-shield user-avatar"></i>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($adminName); ?></span>
                    <span class="user-role">Super Admin</span>
                </div>
            </div>
            <!-- Bouton Logout fonctionnel -->
            <a href="../../Controller/UtilisateurController.php?action=logout" class="logout-btn" aria-label="Logout">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </div>

    <!-- =========== 2. Contenu Principal =========== -->
    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menu-toggle"><i class="fa-solid fa-bars"></i></button>
            </div>
            <div class="topbar-right">
                <div class="user-info">Welcome <?php echo htmlspecialchars($adminName); ?></div>
            </div>
        </header>

        <main class="content-wrapper">
            
            <!-- Affichage des Messages -->
            <?php if ($msg_success): ?>
                <div class="alert alert-success"><?php echo $msg_success; ?></div>
            <?php endif; ?>
            <?php if ($msg_error): ?>
                <div class="alert alert-danger"><?php echo $msg_error; ?></div>
            <?php endif; ?>

            <div class="page-header">
                <h1>Dashboard</h1>
                <p class="page-subtitle">Platform overview</p>
            </div>

            <!-- Grille de statistiques DYNAMIQUE -->
            <div class="stats-grid">
                <!-- Clients -->
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fa-solid fa-user"></i></div>
                    <div>
                        <p class="stat-label">Total Clients</p>
                        <h2 class="stat-value"><?php echo $stats['clients']; ?></h2>
                    </div>
                </div>
                <!-- Organisations -->
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fa-solid fa-building"></i></div>
                    <div>
                        <p class="stat-label">Organizations</p>
                        <h2 class="stat-value"><?php echo $stats['organisations']; ?></h2>
                    </div>
                </div>
                <!-- En attente -->
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fa-solid fa-clock"></i></div>
                    <div>
                        <p class="stat-label">Pending validations</p>
                        <h2 class="stat-value"><?php echo $stats['pending_validations']; ?></h2>
                    </div>
                </div>
            </div>

            <!-- TABLEAU 1 : GESTION DES ORGANISATIONS -->
            <div class="table-card" id="organisations" style="margin-top: 30px;">
                <h3>Organization Managements</h3>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Organization</th>
                                <th>Email</th>
                                <th>Adress</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listeOrganisations as $org): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($org['nom_organisation']); ?></strong></td>
                                <td><?php echo htmlspecialchars($org['email']); ?></td>
                                <td><?php echo htmlspecialchars($org['adresse']); ?></td>
                                <td>
                                    <?php if ($org['statut_verification'] == 'Verifié'): ?>
                                        <span class="status-verified">Verified</span>
                                    <?php else: ?>
                                        <span class="status-pending">On hold</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions-cell">
    
                                <!-- Bouton Valider (Si pas vérifié) -->
                                <?php if ($org['statut_verification'] != 'Verifié'): ?>
                                    <form action="../../Controller/UtilisateurController.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="admin_validate_org">
                                        <input type="hidden" name="id_organisation" value="<?php echo $org['id_utilisateur']; ?>">
                                        <button type="submit" class="btn-validate" title="Valider"><i class="fa-solid fa-check"></i></button>
                                    </form>
                                <?php endif; ?>

                                <!-- Bouton Modifier -->
                                <a href="../../Controller/UtilisateurController.php?action=admin_edit_form&id=<?php echo $org['id_utilisateur']; ?>" class="btn-edit" title="Modifier" style="background-color:#3498db; color:white; padding:5px 10px; border-radius:4px; text-decoration:none; margin-right:5px;">
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                                <!-- === AJOUTER CE BOUTON BANNIR ICI === -->
                                <!-- Attention à bien utiliser $org['id_utilisateur'] -->
                                <a href="../../Controller/UtilisateurController.php?action=admin_ban_form&id=<?php echo $org['id_utilisateur']; ?>" class="btn-ban" title="Bannir" style="background-color:#e74c3c; color:white; padding:5px 10px; border-radius:4px; text-decoration:none; margin-right:5px;">
                                    <i class="fa-solid fa-ban"></i>
                                </a>
                                <!-- ==================================== -->

                                <!-- Bouton Supprimer -->
                                <form action="../../Controller/UtilisateurController.php" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cette organisation ?');">
                                    <input type="hidden" name="action" value="admin_delete_user">
                                    <input type="hidden" name="id_utilisateur" value="<?php echo $org['id_utilisateur']; ?>">
                                    <button type="submit" class="btn-delete" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TABLEAU 2 : GESTION DES CLIENTS -->
            <div class="table-card" id="clients" style="margin-top: 30px;">
                <h3>Customer List</h3>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Date Registration</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listeClients as $client): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($client['nom_complet']); ?></td>
                                <td><?php echo htmlspecialchars($client['email']); ?></td>
                                <td><?php echo htmlspecialchars($client['date_inscription']); ?></td>
                                <td class="actions-cell">
    
                                <!-- Bouton Modifier -->
                                <a href="../../Controller/UtilisateurController.php?action=admin_edit_form&id=<?php echo $client['id_utilisateur']; ?>" class="btn-edit" title="Modifier" style="background-color:#3498db; color:white; padding:5px 10px; border-radius:4px; text-decoration:none; margin-right:5px;">
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                                <!-- === VÉRIFIE QU'IL N'Y A QU'UNE SEULE LIGNE COMME CELLE-CI === -->
                                <a href="../../Controller/UtilisateurController.php?action=admin_ban_form&id=<?php echo $client['id_utilisateur']; ?>" class="btn-ban" title="Bannir" style="background-color:#e74c3c; color:white; padding:5px 10px; border-radius:4px; text-decoration:none; margin-right:5px;">
                                    <i class="fa-solid fa-ban"></i>
                                </a>
                                <!-- (Si tu as une autre ligne identique en dessous, supprime-la !) -->

                                <!-- Bouton Supprimer -->
                                <form action="../../Controller/UtilisateurController.php" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce client ?');">
                                    <input type="hidden" name="action" value="admin_delete_user">
                                    <input type="hidden" name="id_utilisateur" value="<?php echo $client['id_utilisateur']; ?>">
                                    <button type="submit" class="btn-delete" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TABLEAU 3 : HISTOIRES / REVIEW -->
            <div class="table-card" id="stories" style="margin-top: 30px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <h3>Histoires publiées</h3>
                    <a href="#stories" class="btn-primary" style="padding:8px 16px; text-decoration:none;">Review</a>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Auteur</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($listeHistoires)): ?>
                                <tr><td colspan="5">Aucune histoire.</td></tr>
                            <?php else: ?>
                                <?php foreach ($listeHistoires as $story): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($story['titre']); ?></td>
                                        <td><?php echo htmlspecialchars($story['auteur_nom'] ?? 'Inconnu'); ?></td>
                                        <td><?php echo htmlspecialchars($story['date_publication']); ?></td>
                                        <td>
                                            <?php if (($story['statut'] ?? '') === 'publiee'): ?>
                                                <span class="status-verified">Publiée</span>
                                            <?php else: ?>
                                                <span class="status-pending"><?php echo htmlspecialchars($story['statut']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="actions-cell">
                                            <button type="button" class="btn-validate" title="Review" onclick="alert('Contenu :\n\n<?php echo addslashes(str_replace(['\r','\n'], ['','\\n'], $story['contenu'] ?? '')); ?>');">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <form action="../../Controller/HistoireController.php" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cette histoire ?');">
                                                <input type="hidden" name="action" value="admin_delete_story">
                                                <input type="hidden" name="id_histoire" value="<?php echo (int)$story['id_histoire']; ?>">
                                                <button type="submit" class="btn-delete" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- TABLEAU 3 : UTILISATEURS BANNIS -->
            <!-- TABLEAU 3 : BANNED USERS -->
            <!-- L'id="banned" permet au lien de la sidebar de descendre ici -->
            <div class="table-card" id="banned" style="margin-top: 30px; border-top: 4px solid #e74c3c;">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <h3 style="color: #c0392b; margin:0;"><i class="fa-solid fa-ban"></i> Suspended Users</h3>
                </div>
                
                <?php if (empty($listeBannis)): ?>
                    <p style="padding: 20px; color: #777; text-align:center;">No suspended users at the moment.</p>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>User Details</th> <!-- Combiné Nom + Email -->
                                    <th>Role</th>
                                    <th>Reason</th>
                                    <th>Time Left</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listeBannis as $banni): ?>
                                    <?php 
                                        $now = new DateTime();
                                        $end = new DateTime($banni['date_fin_bannissement']);
                                        $interval = $now->diff($end);
                                        
                                        $timeLeft = "";
                                        if ($end < $now) {
                                            $timeLeft = "<span class='badge-expired'>Expired</span>";
                                        } else {
                                            if ($interval->days > 0) $timeLeft .= $interval->days . "d ";
                                            $timeLeft .= $interval->h . "h " . $interval->i . "m";
                                        }
                                    ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <div style="background:#f2f2f2; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#555; font-weight:bold;">
                                                <?php echo strtoupper(substr($banni['nom'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div style="font-weight:bold; color:#2c3e50;"><?php echo htmlspecialchars($banni['nom']); ?></div>
                                                <div style="font-size:0.85em; color:#7f8c8d;"><?php echo htmlspecialchars($banni['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($banni['role'] == 'Client'): ?>
                                            <span class="status-badge client-badge">Client</span>
                                        <?php else: ?>
                                            <span class="status-badge orga-badge">Organization</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="reason-text" title="<?php echo htmlspecialchars($banni['raison_bannissement']); ?>">
                                            "<?php echo htmlspecialchars(substr($banni['raison_bannissement'], 0, 30)); ?>..."
                                        </span>
                                    </td>
                                    <td style="font-weight:bold; color:#e67e22;">
                                        <i class="fa-regular fa-clock"></i> <?php echo $timeLeft; ?>
                                    </td>
                                    <td class="actions-cell">
                                        <form action="../../Controller/UtilisateurController.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to unban this user?');">
                                            <input type="hidden" name="action" value="admin_unban_user">
                                            <input type="hidden" name="id_utilisateur" value="<?php echo $banni['id_utilisateur']; ?>">
                                            
                                            <!-- Bouton Unban stylisé -->
                                            <button type="submit" class="btn-unban">
                                                <i class="fa-solid fa-lock-open"></i> Unban
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });
    </script>

</body>
</html>