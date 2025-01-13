<?php

require_once '../core/abstracts/User.php';
require_once '../config/Database.php';

class Teacher extends User {
    private $courses = [];

    public function getRole() {
        return 'teacher';
    }

    public function getDashboard() {
        return [
            'my_courses' => $this->getCourses(),
            'total_students' => $this->getTotalStudents(),
            'course_stats' => $this->getCourseStats()
        ];
    }

    public function getPermissions() {
        return [
            'can_create_course' => true,
            'can_edit_course' => true,
            'can_delete_course' => true,
            'can_view_students' => true
        ];
    }

    // Course Management
    public function createCourse($data) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "INSERT INTO courses (teacher_id, title, description, content, status) 
                    VALUES (:teacher_id, :title, :description, :content, :status)";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'teacher_id' => $this->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'content' => $data['content'],
                'status' => 'draft'
            ]);
            
            return $conn->lastInsertId();
        } catch(PDOException $e) {
            return false;
        }
    }

    public function updateCourse($courseId, $data) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "UPDATE courses 
                    SET title = :title, description = :description, 
                        content = :content, status = :status 
                    WHERE id = :id AND teacher_id = :teacher_id";
            
            $stmt = $conn->prepare($sql);
            return $stmt->execute([
                'id' => $courseId,
                'teacher_id' => $this->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'content' => $data['content'],
                'status' => $data['status']
            ]);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function deleteCourse($courseId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "DELETE FROM courses 
                    WHERE id = :id AND teacher_id = :teacher_id";
            
            $stmt = $conn->prepare($sql);
            return $stmt->execute([
                'id' => $courseId,
                'teacher_id' => $this->id
            ]);
        } catch(PDOException $e) {
            return false;
        }
    }

    // Course Retrieval
    public function getCourses() {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "SELECT * FROM courses WHERE teacher_id = :teacher_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['teacher_id' => $this->id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function getCourse($courseId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "SELECT * FROM courses 
                    WHERE id = :id AND teacher_id = :teacher_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'id' => $courseId,
                'teacher_id' => $this->id
            ]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return null;
        }
    }

    // Student Management
    public function getStudentsInCourse($courseId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "SELECT u.* FROM users u 
                    JOIN enrollments e ON u.id = e.student_id 
                    JOIN courses c ON e.course_id = c.id 
                    WHERE c.id = :course_id AND c.teacher_id = :teacher_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'course_id' => $courseId,
                'teacher_id' => $this->id
            ]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    private function getTotalStudents() {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "SELECT COUNT(DISTINCT student_id) as total 
                    FROM enrollments e 
                    JOIN courses c ON e.course_id = c.id 
                    WHERE c.teacher_id = :teacher_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute(['teacher_id' => $this->id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['total'] ?? 0;
        } catch(PDOException $e) {
            return 0;
        }
    }

    // Statistics
    private function getCourseStats() {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "SELECT c.id, c.title, 
                    COUNT(e.student_id) as enrolled_students,
                    AVG(e.progress) as avg_progress
                    FROM courses c 
                    LEFT JOIN enrollments e ON c.id = e.course_id 
                    WHERE c.teacher_id = :teacher_id 
                    GROUP BY c.id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute(['teacher_id' => $this->id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
}
