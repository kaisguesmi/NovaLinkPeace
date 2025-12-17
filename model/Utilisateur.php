<?php
class Utilisateur {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function findOrganisationById($id) {
        $sql = "SELECT u.id_utilisateur, u.email, u.date_inscription, u.photo_profil,
                       o.nom_organisation, o.adresse, o.statut_verification
                FROM Utilisateur u
                JOIN Organisation o ON u.id_utilisateur = o.id_utilisateur
                WHERE u.id_utilisateur = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
