<?php

class Offre extends Model
{
    protected string $table = 'Offre';
    protected string $primaryKey = 'id_offre';

    public function getWithAdmin(): array
    {
        $sql = "SELECT o.*, u.email as admin_email
                FROM Offre o
                JOIN Utilisateur u ON u.id_utilisateur = o.id_admin
                ORDER BY o.id_offre DESC";
        return $this->db->query($sql)->fetchAll();
    }
}

