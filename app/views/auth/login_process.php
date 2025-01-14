<?php
session_start();
require_once '../../models/Student.php';
require_once '../../models/Teacher.php';
require_once '../../models/Admin.php';

// Get form data
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Debug
echo "Trying to login with: " . $email . "<br>";

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
    echo "Trying role: " . get_class($user) . "<br>";
    if($user->login($email, $password)) {
        echo "Login successful with role: " . $user->getRole() . "<br>";
        // Check if account is active
        if($user->getStatus() !== 'active') {
            $_SESSION['errors'] = ['login' => 'Your account is pending approval'];
            header('Location: login.php');
            exit;
        }

        // Store user data in session
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_name'] = $user->getName();
        $_SESSION['user_role'] = $user->getRole();

        echo "Redirecting to: " . $user->getRole() . " dashboard<br>";

        // Redirect based on role
        switch($user->getRole()) {
            case 'student':
                header('Location: ../student/dashboard.php');
                exit;
            case 'teacher':
                header('Location: ../teacher/dashboard.php');
                exit;
            case 'admin':
                header('Location: ../admin/dashboard.php');
                exit;
        }
    }
}

// If we get here, login failed
$_SESSION['errors'] = ['login' => 'Invalid email or password'];
header('Location: login.php');
exit;
