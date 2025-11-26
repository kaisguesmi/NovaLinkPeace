CREATE TABLE IF NOT EXISTS reclamations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author_id INT NOT NULL,
    target_type ENUM('story', 'comment') NOT NULL,
    target_id INT NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
