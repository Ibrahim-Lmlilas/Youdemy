<?php

require_once __DIR__ . '/../config/Database.php';

class Course {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Katjib ga3 les cours li published
     * @param int|null $studentId ID dyal student
     * @return array Lista dyal les cours
     */
    public function getAllPublishedCourses($studentId = null) {
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
                   WHERE c.status = 'published'
                   GROUP BY c.id, cat.name, u.name";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$studentId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error in getAllPublishedCourses: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Katjib course b ID dyalo
     * @param int $id ID dyal course
     * @return array|null Course data or null if not found
     */
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
}
