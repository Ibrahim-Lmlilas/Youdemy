<?php
session_start();
require_once '../../models/Admin.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /Youdemy/views/auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    $admin = new Admin();
    $courseId = $_POST['course_id'];
    
    if ($admin->deleteCourse($courseId)) {
        $_SESSION['success'] = "Course deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete course.";
    }
}

header('Location: /Youdemy/views/Dashboard/admin/courses.php');
exit();
