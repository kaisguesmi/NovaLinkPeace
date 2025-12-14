<?php

class Reclamation extends Model
{
    protected string $table = 'Reclamation';
    protected string $primaryKey = 'id_reclamation';

    public function getWithRelations(): array
    {
        $sql = "SELECT r.*, u.email AS auteur_email, h.titre AS histoire_titre, c.contenu AS commentaire_contenu
                FROM Reclamation r
                JOIN Utilisateur u ON u.id_utilisateur = r.id_auteur
                LEFT JOIN Histoire h ON h.id_histoire = r.id_histoire_cible
                LEFT JOIN Commentaire c ON c.id_commentaire = r.id_commentaire_cible
                ORDER BY r.id_reclamation DESC";
        return $this->db->query($sql)->fetchAll();
    }
}

