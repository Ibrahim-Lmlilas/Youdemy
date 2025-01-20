<?php
session_start();
require_once '../../models/Teacher.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if course ID is provided
if (!isset($_POST['course_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Course ID is required']);
    exit;
}

$teacher = new Teacher();
$teacher->setId($_SESSION['user_id']);

try {
    $courseId = $_POST['course_id'];
    
    // Verify the course belongs to this teacher
    $course = $teacher->getCourse($courseId);
    if (!$course) {
        http_response_code(404);
        echo json_encode(['error' => 'Course not found']);
        exit;
    }
    
    // Handle file upload if document type
    $document_path = $course['document_path']; // Keep existing path by default
    if ($_POST['type'] === 'document' && isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/courses/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $document_path = $upload_dir . $file_name;
        
        if (!move_uploaded_file($_FILES['document']['tmp_name'], $document_path)) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to upload document']);
            exit;
        }
    }

    // Prepare course data
    $courseData = [
        'category_id' => $_POST['category_id'],
        'title' => $_POST['title'],
        'type' => $_POST['type'],
        'description' => $_POST['description'],
        'status' => $_POST['status'],
        'url' => $_POST['type'] === 'video' ? $_POST['url'] : null,
        'document_path' => $_POST['type'] === 'document' ? $document_path : null,
        'tags' => isset($_POST['tags']) ? $_POST['tags'] : []
    ];

    // Update the course
    if ($teacher->updateCourse($courseId, $courseData)) {
        echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update course']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
