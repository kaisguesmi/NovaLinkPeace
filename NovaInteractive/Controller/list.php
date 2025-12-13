<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../Model/db.php';
include_once '../Model/Complaint.php';

$database = new Database();
$db = $database->getConnection();

$complaint = new Complaint($db);

$status = isset($_GET['status']) ? $_GET['status'] : null;
$category = isset($_GET['category']) ? $_GET['category'] : null;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Handle empty strings as null
if ($status === "") $status = null;
if ($category === "") $category = null;
if ($start_date === "") $start_date = null;
if ($end_date === "") $end_date = null;

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;

$result = $complaint->readPaginated($status, $category, $start_date, $end_date, $page, $per_page);
$stmt = $result['stmt'];
$total = $result['total'];
$page = $result['page'];
$per_page = $result['per_page'];

$complaints_arr = array();
$complaints_arr["records"] = array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    extract($row);
    $complaint_item = array(
        "id" => $id,
        "author_id" => $author_id,
        "target_type" => $target_type,
        "target_id" => $target_id,
        "category" => $category,
        "reason" => $reason,
        "status" => $status,
        "target_content" => $target_content,
        "ai_score" => $ai_score,
        "ai_analysis" => $ai_analysis,
        "created_at" => $created_at
    );
    array_push($complaints_arr["records"], $complaint_item);
}

// Pagination metadata
$total_pages = (int) ceil($total / $per_page);
$complaints_arr['pagination'] = array(
    'total' => $total,
    'per_page' => $per_page,
    'current_page' => $page,
    'total_pages' => $total_pages
);

http_response_code(200);
echo json_encode($complaints_arr);
?>
