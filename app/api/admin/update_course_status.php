<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../models/Admin.php';

// Check if user is logged in and is an admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get and validate input
$courseId = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

if (!$courseId || !in_array($status, ['draft', 'published', 'archived'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

$admin = new Admin();

if ($admin->updateCourseStatus($courseId, $status)) {
    echo json_encode([
        'success' => true, 
        'message' => 'Status updated successfully'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}
