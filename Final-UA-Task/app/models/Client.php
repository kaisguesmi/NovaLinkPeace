<?php

/**
 * Modèle Client : étend les informations utilisateur.
 */
class Client extends Model
{
    protected string $table = 'Client';
    protected string $primaryKey = 'id_utilisateur';

    public function getProfile(int $id): ?array
    {
        $sql = "SELECT u.*, c.bio, c.nom_complet
                FROM Client c
                JOIN Utilisateur u ON u.id_utilisateur = c.id_utilisateur
                WHERE c.id_utilisateur = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $profile = $stmt->fetch();
        return $profile ?: null;
    }
}

