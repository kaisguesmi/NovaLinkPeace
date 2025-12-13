<?php
include_once 'Model/db.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Connection failed.");
}

// Helper to get random date in last year
function randomDate() {
    $timestamp = mt_rand(strtotime('-1 year'), time());
    return date("Y-m-d H:i:s", $timestamp);
}

try {
    // 1. Create 20 Users
    $pass = password_hash("123456", PASSWORD_DEFAULT);
    $userIds = [];
    
    // Check existing users to avoid duplicates if run multiple times
    // For simplicity, we'll just add new ones or ignore unique constraint errors
    for ($i = 1; $i <= 20; $i++) {
        $username = "user_gen_" . $i;
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $userIds[] = $row['id'];
        } else {
            $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
            if ($stmt->execute([$username, $pass])) {
                $userIds[] = $db->lastInsertId();
            }
        }
    }
    echo "Users checked/created.\n";

    // 2. Create Posts and Comments (Randomly distributed over time)
    $postIds = [];
    $categories = ['Sexual Harassment', 'Suicide', 'Ethnicity', 'Sexism', 'Fraud', 'Violence', 'Spam', 'Other'];
    $statuses = ['pending', 'accepted', 'denied'];

    for ($i = 0; $i < 50; $i++) { // 50 Posts
        $author = $userIds[array_rand($userIds)];
        $date = randomDate();
        $stmt = $db->prepare("INSERT INTO posts (author_id, content, created_at) VALUES (?, ?, ?)");
        $stmt->execute([$author, "Random post content $i", $date]);
        $postId = $db->lastInsertId();
        $postIds[] = $postId;

        // Maybe add a comment
        if (rand(0, 1)) {
            $commentAuthor = $userIds[array_rand($userIds)];
            $stmt = $db->prepare("INSERT INTO comments (post_id, author_id, content, created_at) VALUES (?, ?, ?, ?)");
            $stmt->execute([$postId, $commentAuthor, "Random comment on post $i", $date]);
            $commentId = $db->lastInsertId();

            // Maybe report the comment
            if (rand(0, 1)) {
                $cat = $categories[array_rand($categories)];
                $stat = $statuses[array_rand($statuses)];
                $stmt = $db->prepare("INSERT INTO reclamations (author_id, target_type, target_id, category, reason, status, created_at) VALUES (?, 'comment', ?, ?, 'Report reason for comment', ?, ?)");
                $stmt->execute([$userIds[array_rand($userIds)], $commentId, $cat, $stat, $date]);
            }
        }

        // Maybe report the post
        if (rand(0, 1)) {
            $cat = $categories[array_rand($categories)];
            $stat = $statuses[array_rand($statuses)];
            $stmt = $db->prepare("INSERT INTO reclamations (author_id, target_type, target_id, category, reason, status, created_at) VALUES (?, 'post', ?, ?, 'Report reason for post', ?, ?)");
            $stmt->execute([$userIds[array_rand($userIds)], $postId, $cat, $stat, $date]);
        }
    }
    echo "Posts, Comments, and Reclamations generated.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
