<?php

require_once __DIR__ . '/../config.php';

class ParticipationModel
{
    public static function getAll()
    {
        global $pdo;

        $sql = "SELECT p.*, e.title AS event_title
                FROM participations p
                JOIN events e ON e.id = p.event_id
                ORDER BY p.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByEvent($eventId)
    {
        global $pdo;

        $sql = "SELECT p.*, e.title AS event_title
                FROM participations p
                JOIN events e ON e.id = p.event_id
                WHERE p.event_id = :event_id
                ORDER BY p.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':event_id' => $eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($data)
    {
        global $pdo;

        $sql = "INSERT INTO participations (event_id, full_name, email, message, created_at)
                VALUES (:event_id, :full_name, :email, :message, NOW())";

        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            ':event_id'  => $data['event_id'],
            ':full_name' => $data['fullname'],
            ':email'     => $data['email'],
            ':message'   => $data['message'] ?? ''
        ]);
    }

    public static function update($data)
    {
        global $pdo;

        $sql = "UPDATE participations
                SET event_id = :event_id,
                    full_name = :full_name,
                    email     = :email,
                    message   = :message
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            ':event_id'  => $data['event_id'],
            ':full_name' => $data['fullname'],
            ':email'     => $data['email'],
            ':message'   => $data['message'] ?? '',
            ':id'        => $data['id']
        ]);
    }

    public static function delete($id)
    {
        global $pdo;

        $stmt = $pdo->prepare("DELETE FROM participations WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public static function countByEvent($eventId)
    {
        global $pdo;

        $sql = "SELECT COUNT(*) AS total FROM participations WHERE event_id = :event_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':event_id' => $eventId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public static function findByEventAndEmail($eventId, $email)
    {
        global $pdo;

        $sql = "SELECT * FROM participations
                WHERE event_id = :event_id AND email = :email
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':event_id' => $eventId,
            ':email'    => $email
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // IA : découvrir la catégorie préférée d'un utilisateur
    public static function getFavoriteCategoryByEmail($email)
    {
        global $pdo;

        $sql = "SELECT e.category, COUNT(*) AS total
                FROM participations p
                JOIN events e ON e.id = p.event_id
                WHERE p.email = :email
                GROUP BY e.category
                ORDER BY total DESC
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['category'] : null;
    }
}
