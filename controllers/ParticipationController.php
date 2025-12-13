<?php
// controllers/ParticipationController.php

require_once __DIR__ . '/../models/ParticipationModel.php';
require_once __DIR__ . '/../models/EventModel.php'; // pour vérifier capacité

class ParticipationController
{
    
    public static function list()
    {
        $items = ParticipationModel::getAll();
        echo json_encode($items);
    }

    
    public static function listByEvent()
    {
        $eventId = $_GET['event_id'] ?? null;

        if (!$eventId) {
            echo json_encode(['success' => false, 'error' => 'event_id manquant']);
            return;
        }

        $items = ParticipationModel::getByEvent($eventId);
        echo json_encode(['success' => true, 'items' => $items]);
    }

    
    public static function countByEvent()
    {
        $eventId = $_GET['event_id'] ?? null;

        if (!$eventId) {
            echo json_encode(['success' => false, 'error' => 'event_id manquant']);
            return;
        }

        $count = ParticipationModel::countByEvent($eventId);
        echo json_encode(['success' => true, 'count' => $count]);
    }

    
    public static function create()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            echo json_encode(['success' => false, 'error' => 'Données invalides']);
            return;
        }

        $eventId  = (int)($data['event_id'] ?? 0);
        $fullname = trim($data['fullname'] ?? '');
        $email    = trim($data['email'] ?? '');
        $message  = trim($data['message'] ?? '');

        if ($eventId <= 0 || $fullname === '' || $email === '') {
            echo json_encode(['success' => false, 'error' => 'Champs obligatoires manquants']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Email invalide']);
            return;
        }

        // Vérifier si cet email a déjà participé à cet événement
        $already = ParticipationModel::findByEventAndEmail($eventId, $email);
        if ($already) {
            echo json_encode([
                'success' => false,
                'error'   => 'Vous avez déjà participé à cette initiative.'
            ]);
            return;
        }

        // Vérifier la capacité de l’événement
        $event = EventModel::getEventById($eventId);
        if (!$event) {
            echo json_encode(['success' => false, 'error' => 'Événement introuvable']);
            return;
        }

        $capacity      = (int)$event['capacity'];
        $currentCount  = ParticipationModel::countByEvent($eventId);

        if ($currentCount >= $capacity) {
            echo json_encode(['success' => false, 'error' => 'Capacité maximale atteinte']);
            return;
        }

        // Créer la participation
        $ok = ParticipationModel::create([
            'event_id' => $eventId,
            'fullname' => $fullname,
            'email'    => $email,
            'message'  => $message
        ]);

        echo json_encode(['success' => $ok]);
    }

    
    public static function update()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data['id'])) {
            echo json_encode(['success' => false, 'error' => 'ID manquant']);
            return;
        }

        $ok = ParticipationModel::update($data);
        echo json_encode(['success' => $ok]);
    }

    
    public static function delete()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data['id'])) {
            echo json_encode(['success' => false, 'error' => 'ID manquant']);
            return;
        }

        $ok = ParticipationModel::delete((int)$data['id']);
        echo json_encode(['success' => $ok]);
    }
}
