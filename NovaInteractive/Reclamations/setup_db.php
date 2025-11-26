<?php
include_once 'Model/db.php';

$database = new Database();
$db = $database->getConnection();

if ($db) {
    $sql = file_get_contents('schema.sql');
    try {
        $db->exec($sql);
        echo "Table 'reclamations' created successfully (or already exists).\n";
    } catch (PDOException $e) {
        echo "Error creating table: " . $e->getMessage() . "\n";
    }
} else {
    echo "Database connection failed.\n";
}
?>
