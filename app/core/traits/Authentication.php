<?php

require_once __DIR__ . '/../../config/Database.php';

trait Authentication {
    protected $db;
    protected $id;
    protected $name;
    protected $email;
    protected $role;
    protected $status;

    public function __construct() {
        $db = new Database();
        $this->db = $db->getConnection();
    }

    public function register($name, $email, $password, $role) {
        // Check if email already exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if($stmt->fetch()) {
            return false; // Email already exists
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Set status based on role
        $status = ($role === 'teacher') ? 'pending' : 'active';

        // Insert new user
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, password, role, status) 
            VALUES (?, ?, ?, ?, ?)
        ");

        return $stmt->execute([$name, $email, $hashedPassword, $role, $status]);
    }

    public function login($email, $password) {
        // Get user by email AND role
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
        $role = strtolower(str_replace('Model', '', get_class($this)));
        $stmt->execute([$email, $role]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if user exists and password is correct
        if($user && password_verify($password, $user['password'])) {
            // Store user data in properties
            $this->id = $user['id'];
            $this->name = $user['name'];
            $this->email = $user['email'];
            $this->role = $user['role'];
            $this->status = $user['status'];

            return true;
        }

        return false;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getRole() {
        return $this->role;
    }
}
