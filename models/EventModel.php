<?php

require_once __DIR__ . '/../config.php';

class EventModel
{
    public static function getAllEvents()
    {
        global $pdo;

        $sql = "SELECT e.*, o.nom_organisation AS organisation_nom
            FROM events e
            LEFT JOIN organisation o ON o.id_utilisateur = e.created_by
            ORDER BY e.date ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getEventById($id)
    {
        global $pdo;

        $sql = "SELECT e.*, o.nom_organisation AS organisation_nom
            FROM events e
            LEFT JOIN organisation o ON o.id_utilisateur = e.created_by
            WHERE e.id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Alias pour compatibilité
    public static function getById($id)
    {
        return self::getEventById($id);
    }

    public static function createEvent($data)
    {
        global $pdo;

        $sql = "INSERT INTO events (
                    title, category, location, date, capacity, description,
                    status, created_by, org_id, created_at
                )
                VALUES (
                    :title, :category, :location, :date, :capacity, :description,
                    'en_attente', :created_by, :org_id, NOW()
                )";

        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            ':title'       => $data['title'],
            ':category'    => $data['category'],
            ':location'    => $data['location'],
            ':date'        => $data['date'],
            ':capacity'    => $data['capacity'],
            ':description' => $data['description'],
            ':created_by'  => $data['created_by'],
            ':org_id'      => $data['org_id']
        ]);
    }

    public static function updateStatus($id, $status)
    {
        global $pdo;

        $sql = "UPDATE events SET status = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public static function deleteEvent($id)
    {
        global $pdo;

        $stmt = $pdo->prepare("DELETE FROM events WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public static function updateEvent($data)
    {
        global $pdo;

        $sql = "UPDATE events SET 
                    title       = :title,
                    category    = :category,
                    location    = :location,
                    date        = :date,
                    capacity    = :capacity,
                    description = :description
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            ':title'       => $data['title'],
            ':category'    => $data['category'],
            ':location'    => $data['location'],
            ':date'        => $data['date'],
            ':capacity'    => $data['capacity'],
            ':description' => $data['description'],
            ':id'          => $data['id']
        ]);
    }

    // IA : recommander des initiatives similaires
    public static function getRecommendedByCategory($category, $limit = 3)
    {
        global $pdo;

        $sql = "SELECT *
                FROM events
                WHERE category = :category
                  AND status = 'validé'
                  AND date >= CURDATE()
                ORDER BY date ASC
                LIMIT :lim";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':category', $category, PDO::PARAM_STR);
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
