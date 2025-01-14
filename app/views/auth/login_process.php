<?php
session_start();
require_once '../../models/Student.php';
require_once '../../models/Teacher.php';
require_once '../../models/Admin.php';

// Get form data
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Validate data
$errors = [];

if(empty($email)) {
    $errors['email'] = "Email is required";
} elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Invalid email format";
}

if(empty($password)) {
    $errors['password'] = "Password is required";
}

// If there are errors, redirect back to login
if(!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: login.php');
    exit;
}

// Try to login with each role
$users = [new Student(), new Teacher(), new Admin()];
foreach($users as $user) {
    if($user->login($email, $password)) {
        // Check if account is active
        if($user->getStatus() !== 'active') {
            $_SESSION['errors'] = ['login' => 'Your account is pending approval'];
            header('Location: login.php');
            exit;
        }

        // Redirect based on role
        switch($user->getRole()) {
            case 'student':
                header('Location: ../student/dashboard.php');
                break;
            case 'teacher':
                header('Location: ../teacher/dashboard.php');
                break;
            case 'admin':
                header('Location: ../admin/dashboard.php');
                break;
        }
        exit;
    }
}

// If we get here, login failed
$_SESSION['errors'] = ['login' => 'Invalid email or password'];
header('Location: login.php');
exit;
