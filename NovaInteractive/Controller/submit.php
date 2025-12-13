<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../Model/db.php';
include_once '../Model/Complaint.php';

$database = new Database();
$db = $database->getConnection();

$complaint = new Complaint($db);

$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->author_id) &&
    !empty($data->target_type) &&
    !empty($data->target_id) &&
    !empty($data->category) &&
    !empty($data->reason)
){
    $complaint->author_id = $data->author_id;
    $complaint->target_type = $data->target_type;
    $complaint->target_id = $data->target_id;
    $complaint->category = $data->category;
    $complaint->reason = $data->reason;

    if($complaint->create()){
        http_response_code(201);
        echo json_encode(array("message" => "Complaint was created."));
    } else{
        http_response_code(503);
        echo json_encode(array("message" => "Unable to create complaint."));
    }
} else{
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create complaint. Data is incomplete."));
}
?>
