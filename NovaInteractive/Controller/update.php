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

if(!empty($data->id) && !empty($data->status)){
    $complaint->id = $data->id;
    $complaint->status = $data->status;
    
    if (isset($data->category)) {
        $complaint->category = $data->category;
    }
    $admin_response = isset($data->admin_response) ? $data->admin_response : null;

    if($complaint->updateStatus($admin_response)){
        http_response_code(200);
        echo json_encode(array("message" => "Complaint updated."));
    } else{
        http_response_code(503);
        echo json_encode(array("message" => "Unable to update complaint status."));
    }
} else{
    http_response_code(400);
    echo json_encode(array("message" => "Unable to update complaint. Data is incomplete."));
}
?>
