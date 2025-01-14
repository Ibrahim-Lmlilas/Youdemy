<?php

require_once __DIR__ . '/../core/abstracts/User.php';

class Admin extends User {

    public function getDashboard() {
        return [
            'users_summary' => [
                'total_users' => $this->getTotalUsers(),
                'total_students' => $this->getUserCountByRole('student'),
                'total_teachers' => $this->getUserCountByRole('teacher'),
                'pending_teachers' => 0,
                'suspended_users' => 0
            ],
            'courses_summary' => [
                'total_courses' => $this->getTotalCourses(),
                'published_courses' => $this->getCoursesCountByStatus('published'),
                'draft_courses' => $this->getCoursesCountByStatus('draft'),
                'archived_courses' => $this->getCoursesCountByStatus('archived')
            ],
            'enrollments_summary' => [
                'total_enrollments' => $this->getTotalEnrollments(),
                'completed_enrollments' => $this->getEnrollmentsCountByStatus('completed'),
                'active_enrollments' => $this->getEnrollmentsCountByStatus('active'),
                'average_progress' => 0
            ]
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
            ]
        ];
    }

    public function getAllUsers() {
        $stmt = $this->db->prepare("SELECT id, name, email, role, status FROM users ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllCourses() {
        $sql = "SELECT c.*, u.name as teacher_name, u.email as teacher_email, cat.name as category_name 
                FROM courses c 
                JOIN users u ON c.teacher_id = u.id 
                LEFT JOIN categories cat ON c.category_id = cat.id 
                ORDER BY c.created_at DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getActiveCourseCount() {
        $sql = "SELECT COUNT(*) as count FROM courses WHERE status = 'published'";
        $result = $this->db->query($sql)->fetch();
        return $result['count'];
    }

    public function getTotalUsers() {
        $sql = "SELECT COUNT(*) as count FROM users";
        $result = $this->db->query($sql)->fetch();
        return $result['count'];
    }

    public function getTotalEnrollments() {
        $sql = "SELECT COUNT(*) as count FROM enrollments";
        $result = $this->db->query($sql)->fetch();
        return $result['count'];
    }

    public function getAllEnrollments() {
        $sql = "SELECT e.*, 
                       s.name as student_name,
                       c.title as course_title,
                       t.name as teacher_name
                FROM enrollments e
                JOIN users s ON e.student_id = s.id
                JOIN courses c ON e.course_id = c.id
                JOIN users t ON c.teacher_id = t.id
                ORDER BY e.created_at DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting enrollments: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalCourses() {
        $sql = "SELECT COUNT(*) as count FROM courses";
        $result = $this->db->query($sql)->fetch();
        return $result['count'];
    }

    public function getRole() {
        return 'admin';
    }

    public function setUserPending($userId) {
        $sql = "UPDATE users SET status = 'pending' WHERE id = :user_id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':user_id' => $userId]);
        } catch(PDOException $e) {
            error_log("Error setting user pending: " . $e->getMessage());
            return false;
        }
    }

    public function setUserSuspended($userId) {
        $sql = "UPDATE users SET status = 'suspended' WHERE id = :user_id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':user_id' => $userId]);
        } catch(PDOException $e) {
            error_log("Error suspending user: " . $e->getMessage());
            return false;
        }
    }

    public function updateUserStatus($userId, $status) {
        if (!in_array($status, ['active', 'pending', 'suspended'])) {
            return false;
        }

        $sql = "UPDATE users SET status = :status WHERE id = :user_id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':user_id' => $userId,
                ':status' => $status
            ]);
        } catch(PDOException $e) {
            error_log("Error updating user status: " . $e->getMessage());
            return false;
        }
    }

    public function updateCourseStatus($courseId, $status) {
        if (!in_array($status, ['draft', 'published', 'archived'])) {
            throw new Exception("Invalid status");
        }

        try {
            $sql = "UPDATE courses SET status = :status WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':status' => $status,
                ':id' => $courseId
            ]);
            return $stmt->rowCount() > 0;
        } catch(PDOException $e) {
            error_log("Error updating course status: " . $e->getMessage());
            throw new Exception("Failed to update course status");
        }
    }

    public function getAllTags() {
        $sql = "SELECT * FROM tags ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addTag($name) {
        $sql = "INSERT INTO tags (name) VALUES (?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name]);
    }

    public function updateTag($id, $name) {
        $sql = "UPDATE tags SET name = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $id]);
    }

    public function deleteTag($id) {
        // First remove tag associations
        $sql = "DELETE FROM course_tags WHERE tag_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        // Then delete the tag
        $sql = "DELETE FROM tags WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function getAllCategories() {
        $sql = "SELECT * FROM categories ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCategory($name) {
        $sql = "INSERT INTO categories (name) VALUES (?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name]);
    }

    public function updateCategory($id, $name) {
        $sql = "UPDATE categories SET name = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $id]);
    }

    public function deleteCategory($id) {
        // Update courses to remove category (set to NULL)
        $sql = "UPDATE courses SET category_id = NULL WHERE category_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        // Then delete the category
        $sql = "DELETE FROM categories WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function getCourseTags($courseId) {
        $sql = "SELECT t.* FROM tags t 
                INNER JOIN course_tags ct ON t.id = ct.tag_id 
                WHERE ct.course_id = :course_id 
                ORDER BY t.name";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':course_id' => $courseId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting course tags: " . $e->getMessage());
            return [];
        }
    }

    public function deleteCourse($courseId) {
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Delete course tags
            $sql = "DELETE FROM course_tags WHERE course_id = :course_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':course_id' => $courseId]);
            
            // Delete course
            $sql = "DELETE FROM courses WHERE id = :course_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':course_id' => $courseId]);
            
            // Commit transaction
            $this->db->commit();
            return true;
        } catch(PDOException $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            error_log("Error deleting course: " . $e->getMessage());
            return false;
        }
    }

    private function getUserCountByRole($role) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE role = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$role]);
        $result = $stmt->fetch();
        return $result['count'];
    }

    private function getCoursesCountByStatus($status) {
        $sql = "SELECT COUNT(*) as count FROM courses WHERE status = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$status]);
        $result = $stmt->fetch();
        return $result['count'];
    }

    private function getEnrollmentsCountByStatus($status) {
        $sql = "SELECT COUNT(*) as count FROM enrollments WHERE status = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$status]);
        $result = $stmt->fetch();
        return $result['count'];
    }

    // Required abstract method implementations
    public function update() {
        return true;
    }

    public function delete() {
        return true;
    }
}