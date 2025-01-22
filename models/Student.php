<?php

require_once __DIR__ . '/abstracts/User.php';
require_once __DIR__ . '/../config/Database.php';


class Student extends User {
    public $id;

    public function getRole() {
        return 'student';
    }


    public function enrollCourse($courseId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = ? AND course_id = ?");
            $checkStmt->execute([$this->id, $courseId]);
            $isEnrolled = $checkStmt->fetchColumn() > 0;

            if ($isEnrolled) {
                $_SESSION['error'] = "You are already enrolled in this course.";
                return false;
            }

          
            $stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
            $stmt->execute([$this->id, $courseId]);
            
            $_SESSION['success'] = "Successfully enrolled in course!";
            return true;

        } catch (PDOException $e) {
            $_SESSION['error'] = "Error enrolling in course: " . $e->getMessage();
            return false;
        }
    }

    public function getCourses() {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT c.*, cat.name as category_name, u.name as teacher_name
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                JOIN categories cat ON c.category_id = cat.id
                JOIN users u ON c.teacher_id = u.id
                WHERE e.student_id = ?
            ");
            $stmt->execute([$this->id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error getting enrolled courses: " . $e->getMessage();
            return [];
        }
    }


}
