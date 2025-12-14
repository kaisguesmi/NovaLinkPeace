<?php

class Participation extends Model
{
    protected string $table = 'Participation';
    protected string $primaryKey = 'id_client'; // composite handled manually

    public function join(int $clientId, int $initiativeId): bool
    {
        $sql = "REPLACE INTO Participation (id_client, id_initiative, date_inscription, statut) 
                VALUES (:client, :initiative, NOW(), 'approved')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['client' => $clientId, 'initiative' => $initiativeId]);
    }

    public function leave(int $clientId, int $initiativeId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM Participation WHERE id_client = :client AND id_initiative = :initiative");
        return $stmt->execute(['client' => $clientId, 'initiative' => $initiativeId]);
    }

    public function getParticipants(int $initiativeId): array
    {
        $sql = "SELECT p.*, c.nom_complet
                FROM Participation p
                JOIN Client c ON c.id_utilisateur = p.id_client
                WHERE p.id_initiative = :initiative";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['initiative' => $initiativeId]);
        return $stmt->fetchAll();
    }
}

