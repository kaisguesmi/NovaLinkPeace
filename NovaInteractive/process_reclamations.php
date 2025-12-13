<?php
include_once 'Model/db.php';
include_once 'Model/AIModerator.php';

$database = new Database();
$db = $database->getConnection();
$ai = new AIModerator();

if (!$db) {
    die("Database connection failed.\n");
}

echo "Starting AI Moderation Cycle...\n";

// 1. Fetch 'pending' Reclamations that haven't been analyzed (ai_score is default 0, but we can check if analysis is NULL for cleaner logic)
// Assuming NULL ai_analysis means unprocessed.
$stmt = $db->prepare("SELECT r.*, 
        CASE 
            WHEN r.target_type = 'post' THEN p.content 
            WHEN r.target_type = 'comment' THEN c.content 
        END as content
    FROM reclamations r
    LEFT JOIN posts p ON r.target_type = 'post' AND r.target_id = p.id
    LEFT JOIN comments c ON r.target_type = 'comment' AND r.target_id = c.id
    WHERE r.status = 'pending' AND r.ai_analysis IS NULL
");

$stmt->execute();
$reclamations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count = count($reclamations);
echo "Found $count pending reclamations to analyze.\n";

foreach ($reclamations as $rec) {
    $content = $rec['content'];
    $category = $rec['category'];
    $id = $rec['id'];

    echo "Analyzing Reclamation #$id ($category)... ";

    if (empty($content)) {
        // Content might be deleted or missing
        $analysis = "Content not found (deleted?).";
        $score = 0;
    } else {
        $result = $ai->analyze($content, $category);
        $score = $result['score'];
        $analysis = $result['analysis'];
    }

    // Update Database
    $update = $db->prepare("UPDATE reclamations SET ai_score = ?, ai_analysis = ? WHERE id = ?");
    $update->execute([$score, $analysis, $id]);

    echo "Done. Score: $score/100.\n";
}

echo "Cycle complete.\n";
?>
