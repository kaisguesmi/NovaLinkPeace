<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../Model/db.php';
include_once '../Model/AIModerator.php';

$database = new Database();
$db = $database->getConnection();
$ai = new AIModerator();

if (!$db) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed."]);
    exit;
}

// Re-use logic from process_reclamations.php but as an API endpoint
try {
    // Select pending reclamations OR those with NULL ai_analysis.
    // We might want to allow re-scanning pending ones even if scored? 
    // For now, let's scan anything 'pending' that hasn't been scored,
    // OR allow a specific ID to be passed in POST body if we want single-item scan (future proofing).
    
    // For this specific 'Test on Site' request, a 'Scan All Pending' button is best.
    
    $stmt = $db->prepare("SELECT r.*, 
            CASE 
                WHEN r.target_type = 'post' THEN p.content 
                WHEN r.target_type = 'comment' THEN c.content 
            END as content
        FROM reclamations r
        LEFT JOIN posts p ON r.target_type = 'post' AND r.target_id = p.id
        LEFT JOIN comments c ON r.target_type = 'comment' AND r.target_id = c.id
        WHERE r.status = 'pending' AND (r.ai_analysis IS NULL OR r.ai_analysis = '')
    ");

    $stmt->execute();
    $reclamations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = 0;

    foreach ($reclamations as $rec) {
        $content = $rec['content'];
        $category = $rec['category'];
        $id = $rec['id'];

        if (empty($content)) {
            $analysis = "Content not found (deleted?).";
            $score = 0;
        } else {
            $result = $ai->analyze($content, $category);
            $score = $result['score'];
            $analysis = $result['analysis'];
        }

        $update = $db->prepare("UPDATE reclamations SET ai_score = ?, ai_analysis = ? WHERE id = ?");
        $update->execute([$score, $analysis, $id]);
        $count++;
    }

    echo json_encode(["message" => "AI Scan Complete.", "processed_count" => $count]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error: " . $e->getMessage()]);
}
?>
