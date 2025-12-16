<?php
// Script pour valider toutes les initiatives en attente
require_once __DIR__ . '/config.php';

try {
    $stmt = $pdo->prepare("UPDATE events SET status = 'validé' WHERE status = 'en_attente'");
    $stmt->execute();
    
    $count = $stmt->rowCount();
    
    echo "✅ $count initiative(s) validée(s) avec succès !\n";
    echo "Les clients peuvent maintenant voir et participer à toutes les initiatives.\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
?>
