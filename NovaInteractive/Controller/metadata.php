<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../Model/db.php';
include_once '../Model/Complaint.php';

$database = new Database();
$db = $database->getConnection();

$complaint = new Complaint($db);

$range = $complaint->getDateRange();

// Handle case where table is empty or query failed
if (!$range || !$range['min_date']) {
    $range = [
        'min_date' => date('Y-m-d H:i:s', strtotime('-1 month')),
        'max_date' => date('Y-m-d H:i:s')
    ];
}

http_response_code(200);
echo json_encode($range);
?>
