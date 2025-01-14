<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once __DIR__ . '/../../models/Admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['course_id']) || !is_numeric($_POST['course_id']) || !isset($_POST['status'])) {
        $_SESSION['error_message'] = "Invalid course ID or status";
        header('Location: courses.php');
        exit();
    }

    try {
        $admin = new Admin();
        $courseId = filter_input(INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT);
        $status = htmlspecialchars(strip_tags($_POST['status']));
        
        // Update course status
        if ($admin->updateCourseStatus($courseId, $status)) {
            $_SESSION['success_message'] = "Course status updated successfully";
        } else {
            throw new Exception("Failed to update course status");
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
    
    header('Location: courses.php');
    exit();
}
?>
