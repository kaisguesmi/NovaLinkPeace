<?php
include_once 'Model/db.php';
$database = new Database();
$db = $database->getConnection();

$stmt = $db->query("SELECT id, category, ai_score, ai_analysis FROM reclamations WHERE ai_analysis IS NOT NULL");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: " . $row['id'] . "\n";
    echo "Category: " . $row['category'] . "\n";
    echo "AI Score: " . $row['ai_score'] . "\n";
    echo "AI Analysis: " . $row['ai_analysis'] . "\n";
    echo "---------------------------\n";
}
?>
