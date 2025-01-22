<?php
session_start();
require_once '../../models/Student.php';
require_once '../../models/Teacher.php';

// Get  lm3lomat
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

$errors = [];

if(empty($name)) {
    $errors['name'] = "Name is required";
}

if(empty($email)) {
    $errors['email'] = "Email is required";
} elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Invalid email format";
}

if(empty($password)) {
    $errors['password'] = "Password is required";
}

if(empty($role) || !in_array($role, ['student', 'teacher'])) {
    $errors['role'] = "Invalid role selected";
}

if(!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: /Youdemy/views/auth/register.php');
    exit;
}

$user = null;
if($role === 'student') {
    $user = new Student();
} else {
    $user = new Teacher();
}

if($user->register($name, $email, $password, $role)) {
    if($role === 'teacher') {
        $_SESSION['success'] = "Registration successful Please wait for admin approval before logging in.";
    } else {
        $_SESSION['success'] = "Registration successful You can now login.";
    }
    header('Location: /Youdemy/views/auth/login.php');
    exit;
} else {
    $_SESSION['errors'] = ['email' => 'Email already exists'];
    header('Location: /Youdemy/views/auth/register.php');
    exit;
}
