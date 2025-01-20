<?php
session_start();
require_once '../../models/Admin.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /Youdemy/views/auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id']) && isset($_POST['status'])) {
    $admin = new Admin();
    $courseId = $_POST['course_id'];
    $status = $_POST['status'];
    
    if ($admin->updateCourseStatus($courseId, $status)) {
        $_SESSION['success'] = "Course status updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update course status.";
    }
}

header('Location: /Youdemy/views/Dashboard/admin/courses.php');
exit();
