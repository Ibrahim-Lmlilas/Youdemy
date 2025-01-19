<?php
session_start();
require_once '../../models/Teacher.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header('Location: /yooudemy/views/auth/login.php');
    exit;
}

// Debug session
error_log("Session user_id: " . $_SESSION['user_id']);
error_log("Session user_role: " . $_SESSION['user_role']);

// Get teacher's courses
$teacher = new Teacher();
$teacher->setId($_SESSION['user_id']);
$courses = $teacher->getCourses();

// Debug courses
error_log("Courses in controller: " . print_r($courses, true));

// Make sure $courses is at least an empty array
if ($courses === null) {
    $courses = [];
}

// Include the dashboard view
include '../../views/Dashboard/teacher/dashboard.php';
?>
