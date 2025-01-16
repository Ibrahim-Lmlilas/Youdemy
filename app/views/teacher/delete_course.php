<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header('Location: ../auth/login.php');
    exit();
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/TeacherCourse.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['course_id']) || !is_numeric($_POST['course_id'])) {
        $_SESSION['error_message'] = "Invalid course ID";
        header('Location: courses.php');
        exit();
    }

    try {
        $db = new Database();
        $course = new TeacherCourse($db->getConnection());
        $course->setId($_POST['course_id']);
        $course->setTeacherId($_SESSION['user_id']);
        
        if ($course->delete()) {
            $_SESSION['success_message'] = "Course deleted successfully";
        } else {
            $_SESSION['error_message'] = "Failed to delete course";
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
    
    header('Location: courses.php');
    exit();
} else {
    header('Location: courses.php');
    exit();
}
