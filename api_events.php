<?php
// api_events.php

// Activer l'affichage des erreurs en développement
error_reporting(E_ALL);
ini_set('display_errors', 0);  // Ne pas afficher les erreurs en HTML
ini_set('log_errors', 1);      // Enregistrer les erreurs dans les logs

// Capturer les erreurs fatales
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile:$errline");
    if (in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header("Content-Type: application/json; charset=utf-8");
        http_response_code(500);
        die(json_encode(['success' => false, 'error' => 'Erreur serveur interne']));
    }
});

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

require_once __DIR__ . '/controllers/EventController.php';

$action = $_GET['action'] ?? 'list';

switch ($action) {

    case 'list':
        EventController::list();
        break;

    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            EventController::create();
        } else {
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        }
        break;

    case 'updateStatus':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            EventController::updateStatus();
        } else {
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        }
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            EventController::delete();
        } else {
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        }
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            EventController::update();
        } else {
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        }
        break;

    case 'recommend':   // ⭐⭐ NOUVEAU : IA qui recommande des initiatives
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            EventController::recommend();
        } else {
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        }
        break;

    case 'participate':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            EventController::participate();
        } else {
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Action inconnue']);
}
