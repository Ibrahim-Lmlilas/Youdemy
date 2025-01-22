<?php
session_start();
require_once '../../models/Student.php';
require_once '../../models/Teacher.php';
require_once '../../models/Admin.php';

// Get  information
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// error_log("Trying to login with: " . $email);

$errors = [];

if(empty($email)) {
    $errors['email'] = "Email is required";
} elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Invalid email format";
}

if(empty($password)) {
    $errors['password'] = "Password is required";
}

if(!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: /Youdemy/views/auth/login.php');
    exit;
}

$users = [new Admin(), new Teacher(), new Student()]; 
foreach($users as $user) {
    // echo "Trying role: " . get_class($user) . "<br>";
    if($user->login($email, $password)) {
        // echo "Login successful with role: " . $user->getRole() . "<br>";
        // Check if is active
        if($user->getStatus() !== 'active') {
            $_SESSION['errors'] = ['login' => 'Your account is pending approval'];
            header('Location: /Youdemy/views/auth/login.php');
            exit;
        }

        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_name'] = $user->getName();
        $_SESSION['user_role'] = $user->getRole();

        // echo "Redirecting to: " . $user->getRole() . " dashboard<br>";

        // Redirect based on role
        if ($user->getRole() === 'admin') {
            header('Location: /Youdemy/views/Dashboard/admin/dashboard.php');
            exit;
        } else if ($user->getRole() === 'teacher') {
            header('Location: /Youdemy/views/Dashboard/teacher/dashboard.php');
            exit;
        } else if ($user->getRole() === 'student') {
            header('Location: /Youdemy/views/Dashboard/student/dashboard.php');
            exit;
        }
    }
}

$_SESSION['errors'] = ['login' => 'Invalid email or password'];
header('Location: /Youdemy/views/auth/login.php');
exit;