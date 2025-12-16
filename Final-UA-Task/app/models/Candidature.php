<?php

class Candidature extends Model
{
    protected string $table = 'Candidature';
    protected string $primaryKey = 'id_candidature';

    public function getWithRelations(): array
    {
        $sql = "SELECT ca.*, cl.nom_complet, o.titre AS offre_titre
                FROM Candidature ca
                JOIN Client cl ON cl.id_utilisateur = ca.id_client
                JOIN Offre o ON o.id_offre = ca.id_offre
                ORDER BY ca.id_candidature DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getByClient(int $clientId): array
    {
        $sql = "SELECT ca.*, o.titre AS offre_titre
                FROM Candidature ca
                JOIN Offre o ON o.id_offre = ca.id_offre
                WHERE ca.id_client = :client
                ORDER BY ca.id_candidature DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['client' => $clientId]);
        return $stmt->fetchAll();
    }
}

