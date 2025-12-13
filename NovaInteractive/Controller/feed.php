<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../Model/db.php';
include_once '../Model/Post.php';

$database = new Database();
$db = $database->getConnection();

$post = new Post($db);

$feed = $post->getFeed();

http_response_code(200);
echo json_encode($feed);
?>
