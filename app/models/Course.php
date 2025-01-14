<?php

require_once __DIR__ . '/../config/Database.php';

class Course {
    protected $db;
    protected $id;
    protected $teacher_id;
    protected $category_id;
    protected $title;
    protected $description;
    protected $content;
    protected $status;
    protected $type;
    protected $thumbnail;
    protected $price;
    protected $tags = [];

    public function __construct() {
        $db = new Database();
        $this->db = $db->getConnection();
        $this->status = 'draft';
    }

    public function save() {
        try {
            $this->db->beginTransaction();

            // Insert course
            $stmt = $this->db->prepare("
                INSERT INTO courses (teacher_id, category_id, title, description, 
                                   content, status, type, thumbnail, price)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $this->teacher_id,
                $this->category_id,
                $this->title,
                $this->description,
                $this->content,
                $this->status,
                $this->type,
                $this->thumbnail,
                $this->price
            ]);
            
            $this->id = $this->db->lastInsertId();

            // Save tags
            if (!empty($this->tags)) {
                foreach ($this->tags as $tag) {
                    // First try to insert the tag if it doesn't exist
                    $stmt = $this->db->prepare("
                        INSERT IGNORE INTO tags (name) VALUES (?)
                    ");
                    $stmt->execute([$tag]);
                    
                    // Get tag id
                    $stmt = $this->db->prepare("SELECT id FROM tags WHERE name = ?");
                    $stmt->execute([$tag]);
                    $tagId = $stmt->fetchColumn();
                    
                    // Link tag to course
                    $stmt = $this->db->prepare("
                        INSERT INTO course_tags (course_id, tag_id) VALUES (?, ?)
                    ");
                    $stmt->execute([$this->id, $tagId]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update() {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                UPDATE courses 
                SET title = ?, description = ?, content = ?, 
                    status = ?, type = ?, thumbnail = ?, price = ?,
                    category_id = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND teacher_id = ?
            ");
            
            $success = $stmt->execute([
                $this->title,
                $this->description,
                $this->content,
                $this->status,
                $this->type,
                $this->thumbnail,
                $this->price,
                $this->category_id,
                $this->id,
                $this->teacher_id
            ]);

            // Update tags
            if ($success) {
                // Remove old tags
                $stmt = $this->db->prepare("DELETE FROM course_tags WHERE course_id = ?");
                $stmt->execute([$this->id]);
                
                // Add new tags
                if (!empty($this->tags)) {
                    foreach ($this->tags as $tag) {
                        $stmt = $this->db->prepare("
                            INSERT IGNORE INTO tags (name) VALUES (?)
                        ");
                        $stmt->execute([$tag]);
                        
                        $stmt = $this->db->prepare("SELECT id FROM tags WHERE name = ?");
                        $stmt->execute([$tag]);
                        $tagId = $stmt->fetchColumn();
                        
                        $stmt = $this->db->prepare("
                            INSERT INTO course_tags (course_id, tag_id) VALUES (?, ?)
                        ");
                        $stmt->execute([$this->id, $tagId]);
                    }
                }
            }

            $this->db->commit();
            return $success;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete() {
        $stmt = $this->db->prepare("DELETE FROM courses WHERE id = ? AND teacher_id = ?");
        return $stmt->execute([$this->id, $this->teacher_id]);
    }

    // Getters
    public function getId() { return $this->id; }
    public function getTeacherId() { return $this->teacher_id; }
    public function getCategoryId() { return $this->category_id; }
    public function getTitle() { return $this->title; }
    public function getDescription() { return $this->description; }
    public function getContent() { return $this->content; }
    public function getStatus() { return $this->status; }
    public function getType() { return $this->type; }
    public function getThumbnail() { return $this->thumbnail; }
    public function getPrice() { return $this->price; }
    public function getTags() { return $this->tags; }

    // Setters
    public function setTeacherId($id) { $this->teacher_id = $id; }
    public function setCategoryId($id) { $this->category_id = $id; }
    public function setTitle($title) { $this->title = $title; }
    public function setDescription($desc) { $this->description = $desc; }
    public function setContent($content) { $this->content = $content; }
    public function setStatus($status) { $this->status = $status; }
    public function setType($type) { $this->type = $type; }
    public function setThumbnail($thumbnail) { $this->thumbnail = $thumbnail; }
    public function setPrice($price) { $this->price = $price; }
    public function setTags($tags) { $this->tags = $tags; }

    // Static methods
    public static function findById($id) {
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findByTeacher($teacherId) {
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM courses WHERE teacher_id = ?");
        $stmt->execute([$teacherId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findByCategory($categoryId) {
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM courses WHERE category_id = ?");
        $stmt->execute([$categoryId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function search($query) {
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT DISTINCT c.* 
            FROM courses c
            LEFT JOIN course_tags ct ON c.id = ct.course_id
            LEFT JOIN tags t ON ct.tag_id = t.id
            WHERE c.title LIKE ? 
            OR c.description LIKE ?
            OR t.name LIKE ?
        ");
        
        $searchTerm = "%$query%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
