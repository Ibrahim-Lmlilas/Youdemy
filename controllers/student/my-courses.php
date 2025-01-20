<?php
session_start();
require_once '../../models/Student.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: /Youdemy/views/auth/login.php');
    exit;
}

$student = new Student($_SESSION['user_id']);
$enrolledCourses = $student->getEnrolledCourses();

require_once '../../views/Dashboard/student/my-courses.php';
