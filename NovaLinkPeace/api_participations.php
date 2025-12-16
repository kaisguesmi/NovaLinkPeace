<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once __DIR__ . '/controllers/ParticipationController.php';

$action = $_GET['action'] ?? 'list';

switch ($action) {

    case 'list':
        ParticipationController::list();
        break;

    case 'listByEvent':
        ParticipationController::listByEvent();
        break;

    case 'countByEvent':
        ParticipationController::countByEvent();
        break;

    case 'create':
        ParticipationController::create();
        break;

    case 'update':
        ParticipationController::update();
        break;

    case 'delete':
        ParticipationController::delete();
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Action inconnue']);
}
