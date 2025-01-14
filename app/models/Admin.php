<?php

require_once __DIR__ . '/../core/abstracts/User.php';

class Admin extends User {


    public function getDashboard() {
        $stats = $this->getStatistics();
        
        return [
            'users_summary' => [
                'total_users' => $stats['users']['total_users'],
                'total_students' => $stats['users']['total_students'],
                'total_teachers' => $stats['users']['total_teachers'],
                'pending_teachers' => $stats['users']['pending_users'],
                'suspended_users' => $stats['users']['suspended_users']
            ],
            'courses_summary' => [
                'total_courses' => $stats['courses']['total_courses'],
                'published_courses' => $stats['courses']['published_courses'],
                'draft_courses' => $stats['courses']['draft_courses'],
                'archived_courses' => $stats['courses']['archived_courses']
            ],
            'enrollments_summary' => [
                'total_enrollments' => $stats['enrollments']['total_enrollments'],
                'completed_enrollments' => $stats['enrollments']['completed_enrollments'],
                'active_enrollments' => $stats['enrollments']['active_enrollments'],
                'average_progress' => round($stats['enrollments']['average_progress'], 2)
            ],
            'popular_courses' => $stats['popular_courses'],
            'top_teachers' => $stats['top_teachers']
        ];
    }

    public function getPermissions() {
        return [
            'users' => [
                'view_all' => true,
                'validate_teachers' => true,
                'suspend_users' => true,
                'activate_users' => true,
                'delete_users' => true
            ],
            'courses' => [
                'view_all' => true,
                'delete_any' => true,
                'manage_categories' => true,
                'manage_tags' => true
            ],
            'statistics' => [
                'view_all' => true,
                'export_data' => true
            ]
        ];
    }




    public function update() {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET name = ?, email = ?, status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND role = 'admin'
        ");
        return $stmt->execute([$this->name, $this->email, $this->status, $this->id]);
    }

    public function delete() {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'");
        return $stmt->execute([$this->id]);
    }

    // User Management
    public function getAllUsers() {
        $stmt = $this->db->prepare("
            SELECT u.*, 
                COUNT(DISTINCT c.id) as total_courses,
                COUNT(DISTINCT e.id) as total_enrollments
            FROM users u
            LEFT JOIN courses c ON u.id = c.teacher_id
            LEFT JOIN enrollments e ON 
                (u.role = 'teacher' AND c.id = e.course_id) OR
                (u.role = 'student' AND u.id = e.student_id)
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function validateTeacher($teacherId) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET status = 'active'
            WHERE id = ? AND role = 'teacher' AND status = 'pending'
        ");
        return $stmt->execute([$teacherId]);
    }

    public function suspendUser($userId) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET status = 'suspended'
            WHERE id = ? AND role != 'admin'
        ");
        return $stmt->execute([$userId]);
    }

    public function activateUser($userId) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET status = 'active'
            WHERE id = ? AND role != 'admin'
        ");
        return $stmt->execute([$userId]);
    }

    // Course Management
    public function getAllCourses() {
        $stmt = $this->db->prepare("
            SELECT c.*, 
                   u.name as teacher_name,
                   cat.name as category_name,
                   COUNT(DISTINCT e.id) as total_students,
                   COUNT(DISTINCT com.id) as total_comments
            FROM courses c
            JOIN users u ON c.teacher_id = u.id
            JOIN categories cat ON c.category_id = cat.id
            LEFT JOIN enrollments e ON c.id = e.course_id
            LEFT JOIN comments com ON c.id = com.course_id
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteCourse($courseId) {
        $stmt = $this->db->prepare("DELETE FROM courses WHERE id = ?");
        return $stmt->execute([$courseId]);
    }

    // Category Management
    public function createCategory($name, $description) {
        $stmt = $this->db->prepare("
            INSERT INTO categories (name, description)
            VALUES (?, ?)
        ");
        return $stmt->execute([$name, $description]);
    }

    public function updateCategory($id, $name, $description) {
        $stmt = $this->db->prepare("
            UPDATE categories 
            SET name = ?, description = ?
            WHERE id = ?
        ");
        return $stmt->execute([$name, $description, $id]);
    }

    public function deleteCategory($id) {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Tag Management
    public function createTag($name) {
        $stmt = $this->db->prepare("INSERT INTO tags (name) VALUES (?)");
        return $stmt->execute([$name]);
    }

    public function deleteTag($id) {
        $stmt = $this->db->prepare("DELETE FROM tags WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Statistics
    public function getStatistics() {
        // Users Statistics
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_users,
                COUNT(CASE WHEN role = 'student' THEN 1 END) as total_students,
                COUNT(CASE WHEN role = 'teacher' THEN 1 END) as total_teachers,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_users,
                COUNT(CASE WHEN status = 'suspended' THEN 1 END) as suspended_users
            FROM users
            WHERE role != 'admin'
        ");
        $stmt->execute();
        $userStats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Course Statistics
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_courses,
                COUNT(CASE WHEN status = 'published' THEN 1 END) as published_courses,
                COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_courses,
                COUNT(CASE WHEN status = 'archived' THEN 1 END) as archived_courses
            FROM courses
        ");
        $stmt->execute();
        $courseStats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Enrollment Statistics
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_enrollments,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_enrollments,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_enrollments,
                COUNT(CASE WHEN status = 'dropped' THEN 1 END) as dropped_enrollments,
                AVG(progress) as average_progress
            FROM enrollments
        ");
        $stmt->execute();
        $enrollmentStats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Most Popular Courses
        $stmt = $this->db->prepare("
            SELECT c.*, u.name as teacher_name,
                   COUNT(e.id) as enrollment_count
            FROM courses c
            JOIN users u ON c.teacher_id = u.id
            LEFT JOIN enrollments e ON c.id = e.course_id
            GROUP BY c.id
            ORDER BY enrollment_count DESC
            LIMIT 5
        ");
        $stmt->execute();
        $popularCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top Teachers
        $stmt = $this->db->prepare("
            SELECT u.name, u.email,
                   COUNT(DISTINCT c.id) as course_count,
                   COUNT(DISTINCT e.id) as student_count
            FROM users u
            LEFT JOIN courses c ON u.id = c.teacher_id
            LEFT JOIN enrollments e ON c.id = e.course_id
            WHERE u.role = 'teacher'
            GROUP BY u.id
            ORDER BY student_count DESC
            LIMIT 3
        ");
        $stmt->execute();
        $topTeachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'users' => $userStats,
            'courses' => $courseStats,
            'enrollments' => $enrollmentStats,
            'popular_courses' => $popularCourses,
            'top_teachers' => $topTeachers
        ];
    }

    public function getRole() {
        return 'admin';
    }

}