<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once __DIR__ . '/../../models/Admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['course_id']) || !is_numeric($_POST['course_id'])) {
        $_SESSION['error_message'] = "Invalid course ID";
        header('Location: courses.php');
        exit();
    }

    try {
        $admin = new Admin();
        $courseId = filter_input(INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT);
        
        // Delete course
        if ($admin->deleteCourse($courseId)) {
            $_SESSION['success_message'] = "Course deleted successfully";
        } else {
            throw new Exception("Failed to delete course");
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
    
    header('Location: courses.php');
    exit();
}
?>
