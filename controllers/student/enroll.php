<?php
session_start();
require_once '../../models/Student.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: /Youdemy/views/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    $student = new Student();
    $student->id = $_SESSION['user_id'];
    $courseId = $_POST['course_id'];
    
    error_log("Trying to enroll student " . $_SESSION['user_id'] . " in course " . $courseId);
    
    if ($student->enrollCourse($courseId)) {
        $_SESSION['success'] = "Successfully enrolled in course!";
        error_log("Enrollment successful");
        header('Location: /Youdemy/views/Dashboard/student/my-courses.php');
    } else {
        $_SESSION['error'] = "You are already enrolled in this course.";
        error_log("Enrollment failed");
        header('Location: /Youdemy/views/Dashboard/student/dashboard.php');
    }
    exit;
}

header('Location: /Youdemy/views/Dashboard/student/dashboard.php');
exit;
