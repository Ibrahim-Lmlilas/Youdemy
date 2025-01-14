<?php

require_once __DIR__ . '/../config/Database.php';

class Stats {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    private function tableExists($tableName) {
        $query = "SHOW TABLES LIKE ?";
        $result = $this->db->query($query, [$tableName])->fetch();
        return !empty($result);
    }

    public function getTotalUsers() {
        if (!$this->tableExists('users')) return 0;
        $query = "SELECT COUNT(*) as total FROM users";
        $result = $this->db->query($query)->fetch();
        return $result['total'];
    }

    public function getActiveCourses() {
        if (!$this->tableExists('courses')) return 0;
        $query = "SELECT COUNT(*) as total FROM courses WHERE status = 'active'";
        $result = $this->db->query($query)->fetch();
        return $result['total'];
    }

    public function getPendingTeachers() {
        if (!$this->tableExists('users')) return 0;
        $query = "SELECT COUNT(*) as total FROM users WHERE role = 'teacher' AND status = 'pending'";
        $result = $this->db->query($query)->fetch();
        return $result['total'];
    }

    public function getRecentActivities($limit = 5) {
        if (!$this->tableExists('activities') || !$this->tableExists('users')) return [];
        $query = "SELECT 
                    a.id,
                    a.activity_type,
                    a.description,
                    a.created_at,
                    u.name as user_name
                FROM activities a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.created_at DESC
                LIMIT ?";
        return $this->db->query($query, [$limit])->fetchAll();
    }
}
