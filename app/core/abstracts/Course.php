<?php

require_once __DIR__ . '/../../config/Database.php';

abstract class Course {
    protected $db;
    protected $id;
    protected $title;
    protected $description;
    protected $content;
    protected $image;
    protected $price;
    protected $category_id;
    protected $teacher_id;
    protected $status;
    protected $created_at;
    protected $updated_at;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getTitle() { return $this->title; }
    public function getDescription() { return $this->description; }
    public function getContent() { return $this->content; }
    public function getImage() { return $this->image; }
    public function getPrice() { return $this->price; }
    public function getCategoryId() { return $this->category_id; }
    public function getTeacherId() { return $this->teacher_id; }
    public function getStatus() { return $this->status; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    // Setters
    public function setTitle($title) { $this->title = $title; }
    public function setDescription($description) { $this->description = $description; }
    public function setContent($content) { $this->content = $content; }
    public function setImage($image) { $this->image = $image; }
    public function setPrice($price) { $this->price = $price; }
    public function setCategoryId($category_id) { $this->category_id = $category_id; }
    public function setTeacherId($teacher_id) { $this->teacher_id = $teacher_id; }
    public function setStatus($status) { $this->status = $status; }

    // Abstract methods that must be implemented by child classes
    abstract public function save();
    abstract public function update();
    abstract public function delete();
    abstract public function findById($id);
    abstract public function findAll();

    // Common methods for all courses
    public function getCategory() {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$this->category_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTeacher() {
        $stmt = $this->db->prepare("SELECT id, name, email FROM users WHERE id = ? AND role = 'teacher'");
        $stmt->execute([$this->teacher_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTags() {
        $stmt = $this->db->prepare("
            SELECT t.* 
            FROM tags t
            JOIN course_tags ct ON t.id = ct.tag_id
            WHERE ct.course_id = ?
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEnrollments() {
        $stmt = $this->db->prepare("SELECT * FROM enrollments WHERE course_id = ?");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getComments() {
        $stmt = $this->db->prepare("
            SELECT c.*, u.name as user_name
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.course_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addTag($tag_id) {
        $stmt = $this->db->prepare("INSERT INTO course_tags (course_id, tag_id) VALUES (?, ?)");
        return $stmt->execute([$this->id, $tag_id]);
    }

    public function removeTag($tag_id) {
        $stmt = $this->db->prepare("DELETE FROM course_tags WHERE course_id = ? AND tag_id = ?");
        return $stmt->execute([$this->id, $tag_id]);
    }
}
