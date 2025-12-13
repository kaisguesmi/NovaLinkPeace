<?php
include_once 'Model/db.php';

$database = new Database();
$db = $database->getConnection();

if ($db) {
    // For this dev environment, we will drop the reclamations table to apply the new schema easily.
    // In production, we would use ALTER TABLE.
    try {
        $db->exec("DROP TABLE IF EXISTS reclamations");
        echo "Dropped old reclamations table.\n";
    } catch (PDOException $e) {
        echo "Error dropping table: " . $e->getMessage() . "\n";
    }

    // 1. Create Tables
    $sql = file_get_contents('schema.sql');
    try {
        $db->exec($sql);
        echo "Tables created successfully.\n";
    } catch (PDOException $e) {
        echo "Error creating tables: " . $e->getMessage() . "\n";
    }

    // 2. Insert Dummy Data (if empty)
    try {
        // Check if users exist
        $stmt = $db->query("SELECT COUNT(*) FROM users");
        if ($stmt->fetchColumn() == 0) {
            $pass = password_hash("123456", PASSWORD_DEFAULT);
            $db->exec("INSERT INTO users (username, password, role) VALUES 
                ('admin', '$pass', 'admin'),
                ('user1', '$pass', 'user'),
                ('user2', '$pass', 'user')");
            echo "Dummy users inserted.\n";

            $db->exec("INSERT INTO posts (author_id, content) VALUES 
                (2, 'Hello world! This is my first post.'),
                (3, 'Loving this new platform. Peace and love!')");
            echo "Dummy posts inserted.\n";

            $db->exec("INSERT INTO comments (post_id, author_id, content) VALUES 
                (1, 3, 'Welcome user1!'),
                (1, 2, 'Thanks user2!')");
            echo "Dummy comments inserted.\n";
        }

        // Insert some dummy reclamations for charts
        $stmt = $db->query("SELECT COUNT(*) FROM reclamations");
        if ($stmt->fetchColumn() == 0) {
            $db->exec("INSERT INTO reclamations (author_id, target_type, target_id, category, reason, status, created_at) VALUES 
                (2, 'post', 2, 'Spam', 'Looks like spam', 'pending', NOW()),
                (3, 'comment', 1, 'Violence', 'Aggressive comment', 'accepted', DATE_SUB(NOW(), INTERVAL 1 DAY)),
                (2, 'post', 1, 'Fraud', 'Scam post', 'denied', DATE_SUB(NOW(), INTERVAL 2 DAY)),
                (3, 'post', 2, 'Sexual Harassment', 'Inappropriate', 'pending', NOW())
            ");
            echo "Dummy reclamations inserted.\n";
        }

    } catch (PDOException $e) {
        echo "Error inserting data: " . $e->getMessage() . "\n";
    }

} else {
    echo "Database connection failed.\n";
}
?>
