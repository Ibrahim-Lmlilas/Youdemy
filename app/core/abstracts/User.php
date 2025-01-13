<?php

abstract class User {
    protected $id;
    protected $name;
    protected $email;
    protected $password;
    protected $status = 'active';

    abstract public function getRole();
    abstract public function getDashboard();
    abstract public function getPermissions();

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setPassword($password) {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function getStatus() {
        return $this->status;
    }
}
