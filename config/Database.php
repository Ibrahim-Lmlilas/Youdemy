<?php

class Database {
    private $host = 'localhost';
    private $dbname = 'YooudemY';
    private $user = 'root';
    private $pass = '';
    private $conn;

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            throw new Exception("Error: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}
