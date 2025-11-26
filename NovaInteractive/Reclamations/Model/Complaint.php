<?php
class Complaint {
    private $conn;
    private $table_name = "reclamations";

    public $id;
    public $author_id;
    public $target_type;
    public $target_id;
    public $reason;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new complaint
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET author_id=:author_id, target_type=:target_type, target_id=:target_id, reason=:reason, status='pending'";
        $stmt = $this->conn->prepare($query);

        $this->author_id = htmlspecialchars(strip_tags($this->author_id));
        $this->target_type = htmlspecialchars(strip_tags($this->target_type));
        $this->target_id = htmlspecialchars(strip_tags($this->target_id));
        $this->reason = htmlspecialchars(strip_tags($this->reason));

        $stmt->bindParam(":author_id", $this->author_id);
        $stmt->bindParam(":target_type", $this->target_type);
        $stmt->bindParam(":target_id", $this->target_id);
        $stmt->bindParam(":reason", $this->reason);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read complaints
    public function read($filter_status = null) {
        $query = "SELECT * FROM " . $this->table_name;
        if ($filter_status) {
            $query .= " WHERE status = :status";
        }
        $query .= " ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        if ($filter_status) {
            $stmt->bindParam(":status", $filter_status);
        }
        $stmt->execute();
        return $stmt;
    }

    // Update status
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
