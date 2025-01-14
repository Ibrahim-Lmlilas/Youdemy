<?php
require_once '../../models/Admin.php';
require_once '../../helpers/SessionHelper.php';

// Check if user is logged in and is admin
SessionHelper::requireAdmin();

// Get course ID from POST request
$courseId = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;

if ($courseId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
    exit;
}

// Initialize Admin model
$admin = new Admin();

// Delete course
$success = $admin->deleteCourse($courseId);

// Return response
echo json_encode([
    'success' => $success,
    'message' => $success ? 'Course deleted successfully' : 'Failed to delete course'
]);
