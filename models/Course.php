<?php

require_once __DIR__ . '/../config/Database.php';

class Course {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }


    public function getAllPublishedCourses($studentId = null) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT courses.*, categories.name AS category_name, users.name AS teacher_name,
                GROUP_CONCAT(DISTINCT tags.name) AS tags,
                EXISTS (SELECT 1 FROM enrollments  WHERE enrollments.course_id = courses.id 
                    AND enrollments.student_id = ?
                ) AS is_enrolled
                FROM courses
                JOIN categories ON courses.category_id = categories.id
                JOIN users ON courses.teacher_id = users.id
                LEFT JOIN course_tags ON courses.id = course_tags.course_id
                LEFT JOIN tags ON course_tags.tag_id = tags.id
                WHERE courses.status = 'published'
                GROUP BY courses.id, categories.name, users.name";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$studentId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error in getAllPublishedCourses: " . $e->getMessage());
            return [];
        }
    }


    public function getCourseById($id) {
        try {
            $conn = $this->db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT c.*, cat.name as category_name, u.name as teacher_name,
                       GROUP_CONCAT(t.name) as tags
                FROM courses c
                JOIN categories cat ON c.category_id = cat.id
                JOIN users u ON c.teacher_id = u.id
                LEFT JOIN course_tags ct ON c.id = ct.course_id
                LEFT JOIN tags t ON ct.tag_id = t.id
                WHERE c.id = ?
                GROUP BY c.id
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Get course error: " . $e->getMessage());
            return null;
        }
    }

    public function getAllCategories() {
        try {
            $conn = $this->db->getConnection();
            $stmt = $conn->query("SELECT * FROM categories ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error  " . $e->getMessage());
            return [];
        }
    }

    public function getAllTags() {
        try {
            $conn = $this->db->getConnection();
            $stmt = $conn->query("SELECT * FROM tags ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error : " . $e->getMessage());
            return [];
        }
    }

    public function getFilteredCourses($categoryId = null, $tagId = null, $studentId = null) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT c.*, cat.name as category_name, u.name as teacher_name,
                          GROUP_CONCAT(DISTINCT t.name) as tags,
                          EXISTS (
                              SELECT 1 FROM enrollments e 
                              WHERE e.course_id = c.id 
                              AND e.student_id = ?
                          ) as is_enrolled
                   FROM courses c
                   JOIN categories cat ON c.category_id = cat.id
                   JOIN users u ON c.teacher_id = u.id
                   LEFT JOIN course_tags ct ON c.id = ct.course_id
                   LEFT JOIN tags t ON ct.tag_id = t.id
                   WHERE c.status = 'published'";
            
            $params = [$studentId];
            
            if ($categoryId) {
                $sql .= " AND c.category_id = ?";
                $params[] = $categoryId;
            }
            
            if ($tagId) {
                $sql .= " AND EXISTS (SELECT 1 FROM course_tags WHERE course_id = c.id AND tag_id = ?)";
                $params[] = $tagId;
            }
            
            $sql .= " GROUP BY c.id, cat.name, u.name";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error in getFilteredCourses: " . $e->getMessage());
            return [];
        }
    }
}
