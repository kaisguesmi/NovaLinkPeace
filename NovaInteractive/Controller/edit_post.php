<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../Model/db.php';
include_once '../Model/Post.php';

$database = new Database();
$db = $database->getConnection();
$post = new Post($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id) && !empty($data->content)) {
    if ($post->update($data->id, $data->content)) {
        http_response_code(200);
        echo json_encode(["message" => "Post was updated."]);
    } else {
        http_response_code(503);
        echo json_encode(["message" => "Unable to update post."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete data."]);
}
?>
