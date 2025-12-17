<?php
// controllers/HistoireController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/HistoireModel.php';

class HistoireController
{
    private static function ensureAdmin(): bool
    {
        $role = $_SESSION['role'] ?? '';
        return $role === 'admin';
    }

    public static function list()
    {
        if (!self::ensureAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Accès réservé aux administrateurs']);
            return;
        }

        $stories = HistoireModel::getAll();
        echo json_encode(['success' => true, 'stories' => $stories]);
    }

    public static function delete()
    {
        if (!self::ensureAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Accès réservé aux administrateurs']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID manquant ou invalide']);
            return;
        }

        $ok = HistoireModel::deleteById($id);
        echo json_encode(['success' => $ok]);
    }
}
