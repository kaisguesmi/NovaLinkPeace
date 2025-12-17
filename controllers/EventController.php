<?php
// controllers/EventController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/ParticipationModel.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/Utilisateur.php';

class EventController
{
    private static function organisationIsVerified($orgId)
    {
        $utilisateurModel = new Utilisateur((new Database())->getConnection());
        $organisation = $utilisateurModel->findOrganisationById($orgId);

        return $organisation && ($organisation['statut_verification'] ?? '') === 'Verifié';
    }

    private static function resolveOrgIdForCreator($userId, $role)
    {
        $db = (new Database())->getConnection();

        if ($role === 'organisation') {
            return (int)$userId;
        }

        if ($role === 'expert') {
            $stmt = $db->prepare('SELECT organisation_id FROM Expert WHERE id_utilisateur = :uid');
            $stmt->execute([':uid' => $userId]);
            $orgId = (int)($stmt->fetchColumn() ?: 0);
            return $orgId > 0 ? $orgId : null;
        }

        return null;
    }

    public static function list()
    {
        $events = EventModel::getAllEvents();
        echo json_encode($events);
    }


    // ================= CREATE EVENT =================
    public static function create()
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
            echo json_encode(['success' => false, 'error' => 'Authentification requise']);
            return;
        }

        if (!in_array($_SESSION['role'], ['organisation', 'expert', 'admin'], true)) {
            echo json_encode(['success' => false, 'error' => 'Rôle non autorisé pour créer une initiative']);
            return;
        }

        $orgId = self::resolveOrgIdForCreator($_SESSION['user_id'], $_SESSION['role']);

        if ($_SESSION['role'] === 'organisation' && !self::organisationIsVerified($orgId)) {
            echo json_encode(['success' => false, 'error' => "Votre organisation n'est pas vérifiée. Contactez l'administrateur pour valider le compte avant de publier."]);
            return;
        }

        if ($_SESSION['role'] === 'expert') {
            if (!$orgId) {
                echo json_encode(['success' => false, 'error' => "Aucune organisation associée à cet expert. Demandez à l'organisation de vous lier avant de publier."]);
                return;
            }
            if (!self::organisationIsVerified($orgId)) {
                echo json_encode(['success' => false, 'error' => "L'organisation associée n'est pas vérifiée. Contactez l'administrateur pour la valider."]);
                return;
            }
        }

        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input) {
            echo json_encode(['success' => false, 'error' => 'Données invalides']);
            return;
        }

        $title       = trim($input['title'] ?? '');
        $category    = trim($input['category'] ?? '');
        $location    = trim($input['location'] ?? '');
        $date        = trim($input['date'] ?? '');
        $capacity    = $input['capacity'] ?? null;
        $description = trim($input['description'] ?? '');

        if (
            $title === '' || $category === '' || $location === '' ||
            $date === '' || $description === '' || $capacity === null
        ) {
            echo json_encode(['success' => false, 'error' => 'Champs obligatoires manquants']);
            return;
        }

        if (!is_numeric($capacity) || (int)$capacity <= 0) {
            echo json_encode(['success' => false, 'error' => 'Capacité invalide']);
            return;
        }

        if (strtotime($date) === false) {
            echo json_encode(['success' => false, 'error' => 'Date invalide']);
            return;
        }

        $creatorId = (int)$_SESSION['user_id'];

        $data = [
            'title'       => $title,
            'category'    => $category,
            'location'    => $location,
            'date'        => $date,
            'capacity'    => (int)$capacity,
            'description' => $description,
            'created_by'  => $creatorId,
            'org_id'      => $orgId ?: $creatorId
        ];

        try {
            $ok = EventModel::createEvent($data);
            if ($ok) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Erreur lors de la création de l\'initiative']);
            }
        } catch (Exception $e) {
            error_log("Erreur création événement: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
        }
    }


    // ================= UPDATE STATUS =================
    public static function updateStatus()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input || !isset($input['eventId'], $input['status'])) {
            echo json_encode(['success' => false, 'error' => 'Paramètres manquants']);
            return;
        }

        $id     = (int)$input['eventId'];
        $status = trim($input['status']);

        if ($id <= 0 || $status === '') {
            echo json_encode(['success' => false, 'error' => 'Paramètres invalides']);
            return;
        }

        $ok = EventModel::updateStatus($id, $status);
        echo json_encode(['success' => $ok]);
    }


    // ================= DELETE =================
    public static function delete()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input || !isset($input['id'])) {
            echo json_encode(['success' => false, 'error' => 'ID manquant']);
            return;
        }

        $id = (int)$input['id'];

        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID invalide']);
            return;
        }

        $ok = EventModel::deleteEvent($id);
        echo json_encode(['success' => $ok]);
    }


    // ================= UPDATE EVENT =================
    public static function update()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data['id'])) {
            echo json_encode(['success' => false, 'error' => 'ID manquant']);
            return;
        }

        $id = (int)$data['id'];

        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID invalide']);
            return;
        }

        $title       = trim($data['title'] ?? '');
        $category    = trim($data['category'] ?? '');
        $location    = trim($data['location'] ?? '');
        $date        = trim($data['date'] ?? '');
        $capacity    = $data['capacity'] ?? null;
        $description = trim($data['description'] ?? '');

        if (
            $title === '' || $category === '' || $location === '' ||
            $date === '' || $description === '' || $capacity === null
        ) {
            echo json_encode(['success' => false, 'error' => 'Champs obligatoires manquants']);
            return;
        }

        if (!is_numeric($capacity) || (int)$capacity <= 0) {
            echo json_encode(['success' => false, 'error' => 'Capacité invalide']);
            return;
        }

        $updateData = [
            'id'          => $id,
            'title'       => $title,
            'category'    => $category,
            'location'    => $location,
            'date'        => $date,
            'capacity'    =>(int)$capacity,
            'description' => $description
        ];

        $ok = EventModel::updateEvent($updateData);

        echo json_encode(['success' => $ok]);
    }


    // ==========================================================
    // ====================== IA RECOMMENDATION =================
    // ==========================================================

    public static function recommend()
    {
        $events = EventModel::getAllEvents();

        if (!$events) {
            echo json_encode([
                'success' => false,
                'error'   => 'Aucune initiative trouvée'
            ]);
            return;
        }

        // IA SIMPLE = Choisir les 3 événements les plus populaires (exemple)
        usort($events, function($a, $b) {
            return $b['capacity'] - $a['capacity']; // tri desc
        });

        $recommended = array_slice($events, 0, 3);

        echo json_encode([
            'success' => true,
            'recommended' => $recommended
        ]);
    }


    // ====================== PARTICIPATION VIA SESSION =================
    public static function participate()
    {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        if (($_SESSION['role'] ?? '') !== 'client') {
            echo json_encode(['success' => false, 'error' => 'Seuls les clients peuvent participer']);
            return;
        }

        $payload = json_decode(file_get_contents("php://input"), true) ?? [];
        $eventId = isset($payload['event_id']) ? (int)$payload['event_id'] : (int)($_POST['event_id'] ?? 0);
        $message = trim($payload['message'] ?? '');
        $clientId = (int)$_SESSION['user_id'];

        if ($eventId <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID initiative manquant']);
            return;
        }

        $event = EventModel::getEventById($eventId);
        if (!$event) {
            echo json_encode(['success' => false, 'error' => 'Initiative introuvable']);
            return;
        }

        if (($event['status'] ?? '') !== 'validé') {
            echo json_encode(['success' => false, 'error' => 'Initiative non validée']);
            return;
        }

        $currentCount = ParticipationModel::countByEvent($eventId);
        $capacity = (int)($event['capacity'] ?? 0);
        if ($capacity > 0 && $currentCount >= $capacity) {
            echo json_encode(['success' => false, 'error' => 'Capacité maximale atteinte']);
            return;
        }

        if (ParticipationModel::findByEventAndClient($eventId, $clientId)) {
            echo json_encode(['success' => false, 'error' => 'Déjà inscrit à cette initiative']);
            return;
        }

        $ok = ParticipationModel::addParticipation($eventId, $clientId, $message);
        echo json_encode(['success' => $ok]);
    }
}
