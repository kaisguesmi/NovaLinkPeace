<?php
require_once 'Database.php';

class Offer {
    private $conn;
    private $table_name = "offers";

    public $id;
    public $org_id;
    public $title;
    public $description;
    public $status;
    public $created_at;
    public $max_candidates;
    public $keywords; 
    public $current_count;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    // Récupère toutes les offres OU seulement celles d'une organisation
    public function getAll($org_id = null) {
        $query = "SELECT o.*, org.nom_organisation,
                  (SELECT COUNT(*) FROM applications a WHERE a.offer_id = o.id AND a.status != 'refusée') as current_count
                  FROM " . $this->table_name . " o 
                  LEFT JOIN Organisation org ON o.org_id = org.id_utilisateur";
        
        if ($org_id) {
            $query .= " WHERE o.org_id = :id_org";
        }
        
        $query .= " ORDER BY o.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if ($org_id) {
            $stmt->bindParam(':id_org', $org_id);
        }
        
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->max_candidates = $row['max_candidates'];
            $this->keywords = $row['keywords'];
            return true;
        }
        return false;
    }

    // Vérifie si l'offre est pleine (sauf les refusés)
    public function isFull($offer_id) {
        // MODIFICATION ICI : On ne compte pas les refusés
        $query = "SELECT COUNT(*) as total FROM applications WHERE offer_id = ? AND status != 'refusée'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $offer_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->getById($offer_id);
        
        return $row['total'] >= $this->max_candidates;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET org_id=:id_org, title=:title, description=:description, status=:status, max_candidates=:max, keywords=:keywords, created_at=NOW()";
        $stmt = $this->conn->prepare($query);

        $this->org_id = htmlspecialchars(strip_tags($this->org_id));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->status = 'publiée';
        $this->keywords = htmlspecialchars(strip_tags($this->keywords));

        $stmt->bindParam(":id_org", $this->org_id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":max", $this->max_candidates);
        $stmt->bindParam(":keywords", $this->keywords);

        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET title=:title, description=:description, max_candidates=:max, keywords=:keywords WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->keywords = htmlspecialchars(strip_tags($this->keywords));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':max', $this->max_candidates);
        $stmt->bindParam(':keywords', $this->keywords);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }
}
?>