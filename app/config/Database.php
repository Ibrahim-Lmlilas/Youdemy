<?php

class Database {
    private $host = 'localhost';
    private $dbname = 'youdemy';
    private $user = 'root';
    private $pass = '';
    private $conn;

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "Connected successfully";
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}


