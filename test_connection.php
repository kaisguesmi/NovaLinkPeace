<?php
// test_connection.php
header("Content-Type: application/json; charset=utf-8");

// Test 1: Vérifier config.php
echo json_encode(['step' => 'Testing config.php...']) . "\n";

try {
    require_once __DIR__ . '/config.php';
    echo json_encode(['step' => 'config.php loaded', 'pdo_exists' => isset($pdo)]) . "\n";
    
    if ($pdo) {
        $stmt = $pdo->query("SELECT VERSION()");
        $version = $stmt->fetchColumn();
        echo json_encode(['step' => 'MySQL connection OK', 'version' => $version]) . "\n";
        
        // Test the events table
        $stmt = $pdo->query("SELECT COUNT(*) FROM events");
        $count = $stmt->fetchColumn();
        echo json_encode(['step' => 'Events table readable', 'count' => $count]) . "\n";
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]) . "\n";
}
?>
