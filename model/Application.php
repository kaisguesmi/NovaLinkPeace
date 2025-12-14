<?php
// model/Application.php
require_once 'Database.php';

class Application {
    private $conn;
    private $table_name = "applications";
    
    public $id;
    public $offer_id;
    public $candidate_id;
    public $candidate_name;
    public $candidate_email;
    public $motivation;
    public $status;
    public $submitted_at;
    
    // Nouveaux champs IA
    public $score;
    public $sentiment;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function hasAlreadyApplied($offer_id, $candidate_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE offer_id = :offer_id AND candidate_id = :candidate_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':offer_id', $offer_id);
        $stmt->bindParam(':candidate_id', $candidate_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }

    public function create() {
        // Ajout de candidate_id, score et sentiment dans la requête
        $query = "INSERT INTO " . $this->table_name . " 
                  SET offer_id=:offer_id, candidate_id=:candidate_id, candidate_name=:candidate_name, candidate_email=:candidate_email, motivation=:motivation, status=:status, score=:score, sentiment=:sentiment, submitted_at=NOW()";
        
        $stmt = $this->conn->prepare($query);
        
        $this->offer_id = htmlspecialchars(strip_tags($this->offer_id));
        $this->candidate_id = htmlspecialchars(strip_tags($this->candidate_id));
        $this->candidate_name = htmlspecialchars(strip_tags($this->candidate_name));
        $this->candidate_email = htmlspecialchars(strip_tags($this->candidate_email));
        $this->motivation = htmlspecialchars(strip_tags($this->motivation));
        
        if(empty($this->status)) { $this->status = 'en_attente'; }

        $stmt->bindParam(":offer_id", $this->offer_id);
        $stmt->bindParam(":candidate_id", $this->candidate_id);
        $stmt->bindParam(":candidate_name", $this->candidate_name);
        $stmt->bindParam(":candidate_email", $this->candidate_email);
        $stmt->bindParam(":motivation", $this->motivation);
        $stmt->bindParam(":status", $this->status); 
        
        // Liaison des nouvelles données IA
        $stmt->bindParam(":score", $this->score);
        $stmt->bindParam(":sentiment", $this->sentiment);
        
        return $stmt->execute();
    }

    public function getAllWithOfferDetails($offer_id = null) {
        // On récupère aussi le score, sentiment et les infos du client
        $query = "SELECT app.*, off.title as offer_title, off.org_id,
                  cl.nom_complet as client_nom, u.email as client_email_user
                  FROM " . $this->table_name . " as app
                  LEFT JOIN offers as off ON app.offer_id = off.id
                  LEFT JOIN Client as cl ON app.candidate_id = cl.id_utilisateur
                  LEFT JOIN Utilisateur as u ON app.candidate_id = u.id_utilisateur";
        
        if ($offer_id) {
            $query .= " WHERE app.offer_id = :offer_id";
        }
        
        $query .= " ORDER BY app.score DESC, app.submitted_at DESC"; // On trie par Score (les meilleurs en premier !)
        
        $stmt = $this->conn->prepare($query);
        if ($offer_id) {
            $stmt->bindParam(':offer_id', $offer_id);
        }
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT app.*, off.title as offer_title 
                  FROM " . $this->table_name . " as app
                  LEFT JOIN offers as off ON app.offer_id = off.id
                  WHERE app.id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $id = htmlspecialchars(strip_tags($id));
        $status = htmlspecialchars(strip_tags($status));
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
?>