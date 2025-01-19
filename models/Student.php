<?php

require_once __DIR__ . '/abstracts/User.php';
require_once __DIR__ . '/../config/Database.php';

/**
 * Had class khas b student (telmid) f system
 * Fih ga3 functions li kay7taj student: inscription f cours, tchof progress dyalo...
 */
class Student extends User {
    public $id;

    /**
     * Kat3tik role dyal had user
     * Khassna n3rfo wach student wla teacher bach n3rfo permissions dyalo
     */
    public function getRole() {
        return 'student';
    }

    /**
     * Katsjel student f chi cours
     * Katzid row jdid f table enrollments
     * @param int $courseId ID dyal cours li bghina nsjlo fih
     */
    public function enrollCourse($courseId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Check if already enrolled
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = ? AND course_id = ?");
            $checkStmt->execute([$this->id, $courseId]);
            $isEnrolled = $checkStmt->fetchColumn() > 0;

            if ($isEnrolled) {
                $_SESSION['error'] = "You are already enrolled in this course.";
                return false;
            }

            // Enroll in course
            $stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
            $stmt->execute([$this->id, $courseId]);
            
            $_SESSION['success'] = "Successfully enrolled in course!";
            return true;

        } catch (PDOException $e) {
            $_SESSION['error'] = "Error enrolling in course: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Katjib ga3 les cours li msjel fihom student
     * Fihom: tafassil dyal cours, category dyalo, prof li kayqrih
     * @return array Lista dyal les cours m3a tafassil dyalhom
     */
    public function getEnrolledCourses() {
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

    public function update() {}
    public function delete() {}
}
