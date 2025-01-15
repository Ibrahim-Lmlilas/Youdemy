<?php

require_once __DIR__ . '/../traits/Authentication.php';

abstract class User {
    use Authentication;

    protected $id;
    protected $name;
    protected $email;
    protected $role;
    protected $status;
    protected $created_at;
    protected $updated_at;

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }
    public function getStatus() { return $this->status; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setName($name) { $this->name = $name; }
    public function setEmail($email) { $this->email = $email; }
    public function setStatus($status) { $this->status = $status; }

    // Abstract methods
    abstract public function update();
    abstract public function delete();
}
