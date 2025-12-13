<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../Model/db.php';
include_once '../Model/Complaint.php';

$database = new Database();
$db = $database->getConnection();

$complaint = new Complaint($db);

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : die();

$stmt = $complaint->getUserReports($user_id);
$num = $stmt->rowCount();

if($num > 0) {
    $reports_arr = array();
    $reports_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $report_item = array(
            "id" => $id,
            "target_type" => $target_type,
            "target_id" => $target_id,
            "category" => $category,
            "reason" => $reason,
            "status" => $status,
            "admin_response" => $admin_response,
            "created_at" => $created_at
        );
        array_push($reports_arr["records"], $report_item);
    }
    http_response_code(200);
    echo json_encode($reports_arr);
} else {
    http_response_code(200); // Return empty list instead of 404
    echo json_encode(array("records" => []));
}
?>
