<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../Model/db.php';
include_once '../Model/Complaint.php';

$database = new Database();
$db = $database->getConnection();

$complaint = new Complaint($db);

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Handle empty strings
if ($start_date === "") $start_date = null;
if ($end_date === "") $end_date = null;

$stats = $complaint->getStats($start_date, $end_date);

http_response_code(200);
echo json_encode($stats);
?>
