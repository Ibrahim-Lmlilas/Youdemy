<?php
session_start();
require_once '../../models/Teacher.php';

// Check wach user mconnecti w wach teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header('Location: /yooudemy/views/auth/login.php');
    exit;
}

// Check wach kayn course_id
if (!isset($_GET['id'])) {
    header('Location: /yooudemy/controllers/teacher/dashboard.php?error=Course ID is required');
    exit;
}

// Initialiser teacher
$teacher = new Teacher();
$teacher->id = $_SESSION['user_id'];

// Delete course
if ($teacher->deleteCourse($_GET['id'])) {
    header('Location: /yooudemy/controllers/teacher/dashboard.php?success=Course deleted successfully');
    exit;
} else {
    header('Location: /yooudemy/controllers/teacher/dashboard.php?error=Failed to delete course');
    exit;
}
