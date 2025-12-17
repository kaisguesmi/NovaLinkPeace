<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM message_prive LIKE 'sender_role'");
    if ($stmt->fetch()) {
        echo "Column 'sender_role' already exists.\n";
    } else {
        $pdo->exec("ALTER TABLE message_prive ADD COLUMN sender_role ENUM('client', 'expert') DEFAULT 'client'");
        echo "Column 'sender_role' added successfully.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
