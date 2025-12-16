<?php
// Admin seeder pour PeaceLink (table admin autonome)
// Accès : http://localhost/integration/NovaLinkPeace/admin_seed.php
// Supprime ou renommez ce fichier après utilisation pour la sécurité.

require_once __DIR__ . '/config.php';

// --- Paramètres à ajuster si besoin ---
$email            = 'admin@peacelink.test';
$plainPassword    = 'Admin!234';
$niveauPermission = 5; // niveau max

try {
    $db = (new Database())->getConnection();
    $db->beginTransaction();

    // S'assurer que la table admin existe dans sa nouvelle forme autonome
    $db->exec("CREATE TABLE IF NOT EXISTS admin (
        id_admin INT NOT NULL AUTO_INCREMENT,
        email VARCHAR(255) NOT NULL,
        mot_de_passe_hash VARCHAR(255) NOT NULL,
        niveau_permission INT NOT NULL DEFAULT 1,
        PRIMARY KEY (id_admin),
        UNIQUE KEY uq_admin_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    // Supprimer l'ancien admin avec le même email
    $stmtDel = $db->prepare('DELETE FROM admin WHERE email = :email');
    $stmtDel->execute([':email' => $email]);

    $hash = password_hash($plainPassword, PASSWORD_BCRYPT);

    // Insérer le nouvel admin
    $stmt = $db->prepare('INSERT INTO admin (email, mot_de_passe_hash, niveau_permission) VALUES (:email, :hash, :perm)');
    $stmt->execute([
        ':email' => $email,
        ':hash'  => $hash,
        ':perm'  => $niveauPermission,
    ]);

    $db->commit();

    echo '<h2>Admin créé avec succès</h2>';
    echo '<p>Email : ' . htmlspecialchars($email) . '</p>';
    echo '<p>Mot de passe : ' . htmlspecialchars($plainPassword) . '</p>';
    echo '<p>Niveau permission : ' . (int)$niveauPermission . '</p>';
    echo '<p>ID admin : ' . (int)$db->lastInsertId() . '</p>';
    echo '<p style="color:red">Pensez à supprimer ce fichier après usage.</p>';
} catch (Exception $e) {
    if ($db && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo '<h2>Erreur</h2>';
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
}
