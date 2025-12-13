<?php
class Complaint {
    private $conn;
    private $table_name = "reclamations";

    public $id;
    public $author_id;
    public $target_type;
    public $target_id;
    public $category;
    public $reason;
    public $status;
    public $created_at;
    public $ai_score;
    public $ai_analysis;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new complaint
    public function create() {
        // AI AUTOMATION: Fetch content and analyze before saving
        $target_content = "";
        if ($this->target_type == 'post') {
            $q = "SELECT content FROM posts WHERE id = :id";
            $s = $this->conn->prepare($q);
            $s->bindParam(':id', $this->target_id);
            $s->execute();
            if($row = $s->fetch(PDO::FETCH_ASSOC)) $target_content = $row['content'];
        } elseif ($this->target_type == 'comment') {
            $q = "SELECT content FROM comments WHERE id = :id";
            $s = $this->conn->prepare($q);
            $s->bindParam(':id', $this->target_id);
            $s->execute();
            if($row = $s->fetch(PDO::FETCH_ASSOC)) $target_content = $row['content'];
        }

        // Run AI Analysis
        require_once __DIR__ . '/AIModerator.php';
        $moderator = new AIModerator();
        $ai_result = $moderator->analyze($target_content, $this->category);
        $this->ai_score = $ai_result['score'];
        $this->ai_analysis = $ai_result['analysis'];

        $query = "INSERT INTO " . $this->table_name . " SET 
                author_id=:author_id, 
                target_type=:target_type, 
                target_id=:target_id, 
                category=:category, 
                reason=:reason, 
                status='pending',
                ai_score=:ai_score,
                ai_analysis=:ai_analysis";
        
        $stmt = $this->conn->prepare($query);

        $this->author_id = htmlspecialchars(strip_tags($this->author_id));
        $this->target_type = htmlspecialchars(strip_tags($this->target_type));
        $this->target_id = htmlspecialchars(strip_tags($this->target_id));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->reason = htmlspecialchars(strip_tags($this->reason));

        $stmt->bindParam(":author_id", $this->author_id);
        $stmt->bindParam(":target_type", $this->target_type);
        $stmt->bindParam(":target_id", $this->target_id);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":reason", $this->reason);
        $stmt->bindParam(":ai_score", $this->ai_score);
        $stmt->bindParam(":ai_analysis", $this->ai_analysis);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read complaints with filters
    public function read($filter_status = null, $filter_category = null, $start_date = null, $end_date = null) {
        $query = "SELECT r.*, 
                         CASE 
                            WHEN r.target_type = 'post' THEN p.content 
                            WHEN r.target_type = 'comment' THEN c.content 
                         END as target_content 
                  FROM " . $this->table_name . " r
                  LEFT JOIN posts p ON r.target_type = 'post' AND r.target_id = p.id
                  LEFT JOIN comments c ON r.target_type = 'comment' AND r.target_id = c.id
                  WHERE 1=1";
        
        if ($filter_status) {
            $query .= " AND r.status = :status";
        }
        if ($filter_category) {
            $query .= " AND r.category = :category";
        }
        if ($start_date) {
            $query .= " AND r.created_at >= :start_date";
        }
        if ($end_date) {
            $query .= " AND r.created_at <= :end_date";
        }

        $query .= " ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($query);
        
        if ($filter_status) $stmt->bindParam(":status", $filter_status);
        if ($filter_category) $stmt->bindParam(":category", $filter_category);
        if ($start_date) $stmt->bindParam(":start_date", $start_date);
        if ($end_date) $stmt->bindParam(":end_date", $end_date);

        $stmt->execute();
        return $stmt;
    }

    // Read complaints with pagination
    public function readPaginated($filter_status = null, $filter_category = null, $start_date = null, $end_date = null, $page = 1, $per_page = 10) {
        $where = " WHERE 1=1";
        $params = [];

        if ($filter_status) {
            $where .= " AND r.status = :status";
            $params[':status'] = $filter_status;
        }
        if ($filter_category) {
            $where .= " AND r.category = :category";
            $params[':category'] = $filter_category;
        }
        if ($start_date) {
            $where .= " AND r.created_at >= :start_date";
            $params[':start_date'] = $start_date;
        }
        if ($end_date) {
            $where .= " AND r.created_at <= :end_date";
            $params[':end_date'] = $end_date;
        }

        // total count
        $countQuery = "SELECT COUNT(*) as total FROM " . $this->table_name . " r" . $where;
        $countStmt = $this->conn->prepare($countQuery);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // calculate limit/offset
        $page = max(1, (int)$page);
        $per_page = max(1, (int)$per_page);
        $offset = ($page - 1) * $per_page;

        $query = "SELECT r.*, 
                         CASE 
                            WHEN r.target_type = 'post' THEN p.content 
                            WHEN r.target_type = 'comment' THEN c.content 
                         END as target_content 
                  FROM " . $this->table_name . " r
                  LEFT JOIN posts p ON r.target_type = 'post' AND r.target_id = p.id
                  LEFT JOIN comments c ON r.target_type = 'comment' AND r.target_id = c.id
                  " . $where . " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        // bind params
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', (int)$per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();

        return array('stmt' => $stmt, 'total' => $total, 'page' => $page, 'per_page' => $per_page);
    }

    // Update status and optionally category and admin_response
    public function updateStatus($admin_response = null) {
        $query = "UPDATE " . $this->table_name . " SET status = :status";
        if (!empty($this->category)) {
            $query .= ", category = :category";
        }
        if ($admin_response !== null) {
            $query .= ", admin_response = :admin_response";
        }
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);

        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);
        if (!empty($this->category)) {
            $this->category = htmlspecialchars(strip_tags($this->category));
            $stmt->bindParam(":category", $this->category);
        }
        if ($admin_response !== null) {
            $admin_response = htmlspecialchars(strip_tags($admin_response));
            $stmt->bindParam(":admin_response", $admin_response);
        }

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get statistics with date range
    public function getStats($start_date = null, $end_date = null) {
        $stats = [];
        $where = " WHERE 1=1";
        $params = [];

        if ($start_date) {
            $where .= " AND created_at >= :start_date";
            $params[':start_date'] = $start_date;
        }
        if ($end_date) {
            $where .= " AND created_at <= :end_date";
            $params[':end_date'] = $end_date;
        }

        // By Category
        $query = "SELECT category, COUNT(*) as count FROM " . $this->table_name . $where . " GROUP BY category";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $stats['by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // By Status
        $query = "SELECT status, COUNT(*) as count FROM " . $this->table_name . $where . " GROUP BY status";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Over Time (Daily within range)
        $query = "SELECT DATE(created_at) as date, COUNT(*) as count FROM " . $this->table_name . $where . " GROUP BY DATE(created_at) ORDER BY date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $stats['over_time'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }

    // Get date range of all complaints
    public function getDateRange() {
        $query = "SELECT MIN(created_at) as min_date, MAX(created_at) as max_date FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get reports for a specific user
    public function getUserReports($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE author_id = :author_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":author_id", $user_id);
        $stmt->execute();
        return $stmt;
    }
}
?>
