<?php
include_once 'Model/db.php';

$database = new Database();
$db = $database->getConnection();

if ($db) {
    try {
        // Drop and Re-create Reclamations Table
        $db->exec("DROP TABLE IF EXISTS reclamations");
        echo "Dropped old reclamations table.\n";

        // Read the schema file to find the CREATE TABLE statement for reclamations
        // Since schema.sql contains multiple tables, we'll just manually run the query for reclamations here to be safe and quick
        // Or we can parse the file, but manual is safer for this specific task to ensure we get the new columns.
        
        $sql = "CREATE TABLE IF NOT EXISTS reclamations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            author_id INT NOT NULL,
            target_type ENUM('post', 'comment') NOT NULL,
            target_id INT NOT NULL,
            category ENUM('Sexual Harassment', 'Suicide', 'Ethnicity', 'Sexism', 'Fraud', 'Violence', 'Spam', 'Other') NOT NULL,
            reason TEXT NOT NULL,
            status ENUM('pending', 'accepted', 'denied') DEFAULT 'pending',
            admin_response TEXT NULL,
            ai_analysis TEXT NULL,
            ai_score INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id)
        )";
        
        $db->exec($sql);
        echo "Reclamations table recreated with AI columns.\n";

        // Re-populate some dummy data
         $db->exec("INSERT INTO reclamations (author_id, target_type, target_id, category, reason, status, created_at) VALUES 
            (2, 'post', 2, 'Spam', 'Looks like spam', 'pending', NOW()),
            (3, 'comment', 1, 'Violence', 'Aggressive comment', 'accepted', DATE_SUB(NOW(), INTERVAL 1 DAY)),
            (2, 'post', 1, 'Fraud', 'Scam post', 'denied', DATE_SUB(NOW(), INTERVAL 2 DAY)),
            (3, 'post', 2, 'Sexual Harassment', 'Inappropriate', 'pending', NOW())
        ");
        echo "Dummy reclamations inserted.\n";

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
