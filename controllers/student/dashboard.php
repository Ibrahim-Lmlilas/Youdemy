<?php
session_start();
require_once '../../models/Course.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: /yooudemy/views/auth/login.php');
    exit;
}

$course = new Course();
$courses = $course->getAllPublishedCourses($_SESSION['user_id']);

require_once '../../views/Dashboard/student/dashboard.php';
