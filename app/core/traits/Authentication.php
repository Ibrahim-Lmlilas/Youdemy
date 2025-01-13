<?php

trait Authentication {
    public function login($email, $password) {
        // Verify 
        if($this->verifyCredentials($email, $password)) {

            session_start();
            $_SESSION['user_id'] = $this->id;
            $_SESSION['role'] = $this->getRole();
            return true;
        }
        return false;
    }


    public function logout() {
        session_start();
        session_destroy();
        return true;
    }

    private function verifyCredentials($email, $password) {
        return false;
    }
}
