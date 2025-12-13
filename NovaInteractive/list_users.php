<?php
include_once 'Model/db.php';
$database = new Database();
$db = $database->getConnection();

$stmt = $db->query("SELECT id, username, role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total users: " . count($users) . "\n";
foreach ($users as $u) {
    echo "ID: " . $u['id'] . " | User: " . $u['username'] . " | Role: " . $u['role'] . "\n";
}
?>
