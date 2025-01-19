<?php

require_once __DIR__ . '/../config/Database.php';

class TeacherApprovals {
    private $db;
    private $validStatuses = ['pending', 'active', 'suspended'];

    public function __construct() {
        $this->db = new Database();
    }

    private function tableExists($tableName) {
        $query = "SHOW TABLES LIKE ?";
        $result = $this->db->query($query, [$tableName])->fetch();
        return !empty($result);
    }

    private function validateStatus($status) {
        return in_array($status, $this->validStatuses);
    }

    public function getPendingTeachers() {
        if (!$this->tableExists('users')) return [];
        $query = "SELECT id, name, email, status FROM users WHERE role = 'teacher' AND status = 'pending'";
        return $this->db->query($query)->fetchAll();
    }

    public function approveTeacher($teacherId) {
        if (!$this->tableExists('users') || !$this->validateStatus('active')) return false;
        $query = "UPDATE users SET status = 'active' WHERE id = ? AND role = 'teacher' AND status = 'pending'";
        return $this->db->query($query, [$teacherId]);
    }

    public function rejectTeacher($teacherId) {
        if (!$this->tableExists('users') || !$this->validateStatus('suspended')) return false;
        $query = "UPDATE users SET status = 'suspended' WHERE id = ? AND role = 'teacher' AND status = 'pending'";
        return $this->db->query($query, [$teacherId]);
    }
}
