<?php

class Initiative extends Model
{
    protected string $table = 'Initiative';
    protected string $primaryKey = 'id_initiative';

    public function getWithCreator(): array
    {
        $sql = "SELECT i.*, c.nom_complet 
                FROM Initiative i
                JOIN Client c ON c.id_utilisateur = i.id_createur
                ORDER BY i.date_evenement ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function moderate(int $id, string $status): bool
    {
        return $this->update($id, ['statut' => $status]);
    }
}

