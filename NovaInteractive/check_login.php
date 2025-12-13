<?php
include_once 'Model/db.php';

$database = new Database();
$db = $database->getConnection();

$username = 'admin';
$password = 'AdminPass2025!';

echo "Checking login for user: " . $username . "\n";
echo "Testing with password: " . $password . "\n";

$stmt = $db->prepare("SELECT id, password, role FROM users WHERE username = ?");
$stmt->execute([$username]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    echo "User found in DB. stored hash: " . $row['password'] . "\n";
    if (password_verify($password, $row['password'])) {
        echo "SUCCESS: password_verify returned true!\n";
    } else {
        echo "FAILURE: password_verify returned false!\n";
        echo "New hash of '$password': " . password_hash($password, PASSWORD_DEFAULT) . "\n";
    }
} else {
    echo "User '$username' not found in database.\n";
}
?>
