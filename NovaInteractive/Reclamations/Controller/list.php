<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../Model/db.php';
include_once '../Model/Complaint.php';

$database = new Database();
$db = $database->getConnection();

$complaint = new Complaint($db);

$status = isset($_GET['status']) ? $_GET['status'] : null;

$stmt = $complaint->read($status);
$num = $stmt->rowCount();

if($num > 0){
    $complaints_arr = array();
    $complaints_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);
        $complaint_item = array(
            "id" => $id,
            "author_id" => $author_id,
            "target_type" => $target_type,
            "target_id" => $target_id,
            "reason" => $reason,
            "status" => $status,
            "created_at" => $created_at
        );
        array_push($complaints_arr["records"], $complaint_item);
    }
    http_response_code(200);
    echo json_encode($complaints_arr);
} else{
    http_response_code(404);
    echo json_encode(array("message" => "No complaints found."));
}
?>
