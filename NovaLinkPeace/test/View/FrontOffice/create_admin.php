<?php
// On inclut ta connexion existante
// Assure-toi que le chemin vers Database.php est bon
require_once 'Model/Database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // --- CONFIGURATION ---
    $email = "admin@gmail.com";
    $password_en_clair = "azertyadmin"; // Ton mot de passe demandé
    $niveau_permission = 5; // Niveau max

    echo "<h1>Création de l'Administrateur</h1>";

    // 1. Démarrer la transaction (pour que tout se fasse ou rien du tout)
    $conn->beginTransaction();

    // 2. Nettoyage : On supprime l'ancien admin s'il existe déjà pour éviter les doublons
    $checkSql = "DELETE FROM Utilisateur WHERE email = :email";
    $stmtCheck = $conn->prepare($checkSql);
    $stmtCheck->bindParam(':email', $email);
    $stmtCheck->execute();
    
    if ($stmtCheck->rowCount() > 0) {
        echo "<p style='color:orange'>⚠ Un ancien compte avec cet email a été supprimé pour le remplacer.</p>";
    }

    // 3. Hashage du mot de passe (Indispensable pour que password_verify fonctionne)
    $password_hash = password_hash($password_en_clair, PASSWORD_BCRYPT);

    // 4. Insertion dans la table UTILISATEUR
    $sqlUser = "INSERT INTO Utilisateur (email, mot_de_passe_hash, date_inscription, est_banni) 
                VALUES (:email, :mdp, NOW(), 0)";
    
    $stmtUser = $conn->prepare($sqlUser);
    $stmtUser->bindParam(':email', $email);
    $stmtUser->bindParam(':mdp', $password_hash);
    $stmtUser->execute();

    // Récupération de l'ID créé
    $last_id = $conn->lastInsertId();

    // 5. Insertion dans la table ADMIN
    $sqlAdmin = "INSERT INTO Admin (id_utilisateur, niveau_permission) 
                 VALUES (:id, :perm)";
    
    $stmtAdmin = $conn->prepare($sqlAdmin);
    $stmtAdmin->bindParam(':id', $last_id);
    $stmtAdmin->bindParam(':perm', $niveau_permission);
    $stmtAdmin->execute();

    // 6. Validation finale
    $conn->commit();

    echo "<h2 style='color:green'>✅ SUCCÈS !</h2>";
    echo "<p>L'administrateur a été créé.</p>";
    echo "<ul>";
    echo "<li><strong>Email :</strong> $email</li>";
    echo "<li><strong>Mot de passe :</strong> $password_en_clair</li>";
    echo "<li><strong>ID :</strong> $last_id</li>";
    echo "</ul>";
    echo "<br><a href='View/FrontOffice/login.php'>👉 Aller à la page de connexion</a>";

} catch (Exception $e) {
    // En cas d'erreur, on annule tout
    $conn->rollBack();
    echo "<h2 style='color:red'>❌ ERREUR</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>