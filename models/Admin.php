<?php

require_once __DIR__ . '/abstracts/User.php';

class Admin extends User {
    
    public function getRole() {
        return 'admin';
    }

    public function getDashboard() {
        return [
            'users_summary' => [
                'total_users' => $this->getTotalUsers(),
                'total_students' => $this->getUserCountByRole('student'),
                'total_teachers' => $this->getUserCountByRole('teacher')
            ],
            'courses_summary' => [
                'total_courses' => $this->getTotalCourses(),
                'published_courses' => $this->getCoursesCountByStatus('published'),
                'draft_courses' => $this->getCoursesCountByStatus('draft')
            ]
        ];
    }

    public function getAllUsers() {
        $stmt = $this->db->prepare("SELECT id, name, email, role, status FROM users ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
//kiki
    public function getCourses() {
        $sql = "SELECT c.*, u.name as teacher_name, cat.name as category_name 
                FROM courses c 
                JOIN users u ON c.teacher_id = u.id 
                LEFT JOIN categories cat ON c.category_id = cat.id 
                ORDER BY c.created_at DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getTotalUsers() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function getTotalCourses() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM courses");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function updateUserStatus($userId, $status) {
        $validStatuses = ['active', 'pending', 'suspended'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $stmt = $this->db->prepare("UPDATE users SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $userId]);
    }

    public function updateCourseStatus($courseId, $status) {
        $validStatuses = ['draft', 'published'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $stmt = $this->db->prepare("UPDATE courses SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $courseId]);
    }

    public function getUserCountByRole($role) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE role = ?");
        $stmt->execute([$role]);
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function getCoursesCountByStatus($status) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM courses WHERE status = ?");
        $stmt->execute([$status]);
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function getUserCount() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    public function update() {}

    public function delete() {}

    public function deleteCourse($courseId) {
        try {
            // First delete related records in course_tags
            $stmt = $this->db->prepare("DELETE FROM course_tags WHERE course_id = ?");
            $stmt->execute([$courseId]);
            
            // Then delete the course
            $stmt = $this->db->prepare("DELETE FROM courses WHERE id = ?");
            return $stmt->execute([$courseId]);
        } catch (PDOException $e) {
            error_log("Error deleting course: " . $e->getMessage());
            return false;
        }
    }

    public function searchUsers($role = 'all', $search = '') {
        $sql = "SELECT id, name, email, role, status FROM users WHERE 1=1";
        $params = [];

        if ($role !== 'all') {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        if (!empty($search)) {
            $sql .= " AND (name LIKE ? OR email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY id DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addTag($name) {
        $stmt = $this->db->prepare("INSERT INTO tags (name) VALUES (?)");
        return $stmt->execute([$name]);
    }

    public function updateTag($id, $name) {
        $stmt = $this->db->prepare("UPDATE tags SET name = ? WHERE id = ?");
        return $stmt->execute([$name, $id]);
    }

    public function deleteTag($id) {
        $stmt = $this->db->prepare("DELETE FROM tags WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function addCategory($name, $description = '') {
        $stmt = $this->db->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        return $stmt->execute([$name, $description]);
    }

    public function updateCategory($id, $name, $description = '') {
        $stmt = $this->db->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        return $stmt->execute([$name, $description, $id]);
    }

    public function deleteCategory($id) {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }
}