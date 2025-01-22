<?php
session_start();
require_once '../../models/Teacher.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_POST['course_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Course ID is required']);
    exit;
}

$teacher = new Teacher();
$teacher->setId($_SESSION['user_id']);

try {
    $courseId = $_POST['course_id'];
    
    $course = $teacher->getCourse($courseId);
    if (!$course) {
        http_response_code(404);
        echo json_encode(['error' => 'Course not found']);
        exit;
    }
    
    if ($teacher->deleteCourse($courseId)) {
        echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete course']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
