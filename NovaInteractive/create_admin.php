<?php
include_once 'Model/db.php';
$database = new Database();
$db = $database->getConnection();

$username = 'admin';
$password = 'AdminPass2025!';
$hash = password_hash($password, PASSWORD_DEFAULT);

// Check if admin exists
$stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->fetch()) {
    echo "Admin user already exists. Updating password.\n";
    $stmt = $db->prepare("UPDATE users SET password = ?, role = 'admin' WHERE username = ?");
    $stmt->execute([$hash, $username]);
} else {
    echo "Creating admin user.\n";
    $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
    $stmt->execute([$username, $hash]);
}
echo "Admin setup complete.\n";
?>
