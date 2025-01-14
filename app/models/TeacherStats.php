<?php

class TeacherStats {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Get number of active courses for a teacher
    public function getActiveCourses($teacherId) {
        $sql = "SELECT COUNT(*) as count FROM courses 
                WHERE teacher_id = ? AND status = 'published'";
        $result = $this->db->query($sql, [$teacherId])->fetch();
        return $result['count'] ?? 0;
    }

    // Get total views for all teacher's courses
    public function getTotalViews($teacherId) {
        $sql = "SELECT SUM(views) as total FROM course_views cv
                JOIN courses c ON cv.course_id = c.id 
                WHERE c.teacher_id = ?";
        $result = $this->db->query($sql, [$teacherId])->fetch();
        return $result['total'] ?? 0;
    }

    // Get recent activities in teacher's courses
    public function getRecentActivities($teacherId, $limit = 5) {
        $sql = "SELECT 
                    u.name as student_name,
                    c.title as course_title,
                    ca.action,
                    ca.created_at
                FROM course_activities ca
                JOIN courses c ON ca.course_id = c.id
                JOIN users u ON ca.student_id = u.id
                WHERE c.teacher_id = ?
                ORDER BY ca.created_at DESC
                LIMIT ?";
        
        return $this->db->query($sql, [$teacherId, $limit])->fetchAll();
    }
}
