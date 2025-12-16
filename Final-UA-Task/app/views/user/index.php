<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<div class="page-header">
    <div>
        <h1>Utilisateurs</h1>
        <p class="page-subtitle">Liste de tous les utilisateurs</p>
    </div>
</div>

<div class="table-card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Date d'inscription</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #888;">
                            Aucun utilisateur trouvé.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <?php
                                    $avatar = $u['avatar'] ?? null;
                                    $name = $u['nom_complet'] ?? $u['email'] ?? 'Utilisateur';
                                    $initials = strtoupper(substr($name, 0, 2));
                                    ?>
                                    <?php if ($avatar): ?>
                                        <img src="<?= $base ?>/assets/images/<?= htmlspecialchars($avatar) ?>" 
                                             alt="<?= htmlspecialchars($name) ?>" 
                                             class="user-cell-avatar">
                                    <?php else: ?>
                                        <div class="user-cell-avatar"><?= htmlspecialchars($initials) ?></div>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($name) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <?php if (!empty($u['is_admin'])): ?>
                                    <span class="role-badge admin">Admin</span>
                                <?php else: ?>
                                    <span class="role-badge user">User</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($u['date_inscription'])) ?></td>
                            <td class="actions-cell">
                                <span style="color:#aaa;">N/A</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

