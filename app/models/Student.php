<?php

require_once __DIR__ . '/../core/abstracts/User.php';

class Student extends User {
    private $enrolledCourses = [];

    public function getRole() {
        return 'student';
    }

    public function getDashboard() {
        return [
            'enrolled_courses' => $this->enrolledCourses,
            'progress' => $this->getProgress()
        ];
    }

    public function getPermissions() {
        return [
            'can_enroll' => true,
            'can_view_courses' => true,
            'can_submit_reviews' => true
        ];
    }

    public function update() {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET name = ?, email = ?, status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND role = 'student'
        ");
        return $stmt->execute([$this->name, $this->email, $this->status, $this->id]);
    }

    public function delete() {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
        return $stmt->execute([$this->id]);
    }

    // Course related methods
    public function enrollCourse($courseId) {
        $stmt = $this->db->prepare("
            INSERT INTO enrollments (student_id, course_id, status)
            VALUES (?, ?, 'active')
        ");
        return $stmt->execute([$this->id, $courseId]);
    }

    public function dropCourse($courseId) {
        $stmt = $this->db->prepare("
            UPDATE enrollments 
            SET status = 'dropped', completed_at = CURRENT_TIMESTAMP
            WHERE student_id = ? AND course_id = ?
        ");
        return $stmt->execute([$this->id, $courseId]);
    }

    public function updateProgress($courseId, $progress) {
        $stmt = $this->db->prepare("
            UPDATE enrollments 
            SET progress = ?,
                status = CASE WHEN ? >= 100 THEN 'completed' ELSE status END,
                completed_at = CASE WHEN ? >= 100 THEN CURRENT_TIMESTAMP ELSE completed_at END
            WHERE student_id = ? AND course_id = ?
        ");
        return $stmt->execute([$progress, $progress, $progress, $this->id, $courseId]);
    }

    public function getEnrolledCourses() {
        $stmt = $this->db->prepare("
            SELECT c.*, cat.name as category_name, u.name as teacher_name,
                   e.status as enrollment_status, e.progress,
                   e.enrolled_at, e.completed_at
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            JOIN categories cat ON c.category_id = cat.id
            JOIN users u ON c.teacher_id = u.id
            WHERE e.student_id = ?
            ORDER BY e.enrolled_at DESC
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllPublishedCourses() {
        $stmt = $this->db->prepare("
            SELECT c.*, cat.name as category_name, u.name as teacher_name,
                   COUNT(DISTINCT e.id) as enrolled_students,
                   GROUP_CONCAT(DISTINCT t.name) as tags
            FROM courses c
            JOIN categories cat ON c.category_id = cat.id
            JOIN users u ON c.teacher_id = u.id
            LEFT JOIN enrollments e ON c.id = e.course_id
            LEFT JOIN course_tags ct ON c.id = ct.course_id
            LEFT JOIN tags t ON ct.tag_id = t.id
            WHERE c.status = 'published'
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourseProgress($courseId) {
        $stmt = $this->db->prepare("
            SELECT progress, status, enrolled_at, completed_at
            FROM enrollments
            WHERE student_id = ? AND course_id = ?
        ");
        $stmt->execute([$this->id, $courseId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addComment($courseId, $content) {
        $stmt = $this->db->prepare("
            INSERT INTO comments (user_id, course_id, content)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$this->id, $courseId, $content]);
    }

    public function getComments() {
        $stmt = $this->db->prepare("
            SELECT c.*, co.title as course_title
            FROM comments c
            JOIN courses co ON c.course_id = co.id
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getProgress() {
        // Here we'll calculate progress for enrolled courses
        return [];
    }
}
