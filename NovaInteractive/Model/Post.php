<?php
class Post {
    private $conn;
    private $table_name = "posts";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getFeed() {
        $query = "SELECT p.id, p.content, p.created_at, u.username as author 
                  FROM " . $this->table_name . " p 
                  JOIN users u ON p.author_id = u.id 
                  ORDER BY p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $posts = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['comments'] = $this->getComments($row['id']);
            $posts[] = $row;
        }
        return $posts;
    }

    private function getComments($post_id) {
        $query = "SELECT c.id, c.content, c.created_at, u.username as author 
                  FROM comments c 
                  JOIN users u ON c.author_id = u.id 
                  WHERE c.post_id = :post_id 
                  ORDER BY c.created_at ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":post_id", $post_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function update($id, $content) {
        $query = "UPDATE " . $this->table_name . " SET content = :content WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
