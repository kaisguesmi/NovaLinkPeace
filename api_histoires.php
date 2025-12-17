<?php
// api_histoires.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once __DIR__ . '/controllers/HistoireController.php';

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        HistoireController::list();
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            HistoireController::delete();
        } else {
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Action inconnue']);
}
