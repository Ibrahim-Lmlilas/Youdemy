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

// Jib course
$course = $teacher->getCourse($_GET['id']);
if (!$course) {
    header('Location: /yooudemy/controllers/teacher/dashboard.php?error=Course not found');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Upload file ila kan type = document
    $document_path = $course['document_path']; // Keep old path by default
    if ($_POST['type'] === 'document' && isset($_FILES['document']) && $_FILES['document']['size'] > 0) {
        $target_dir = "../../uploads/courses/documents/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $target_file = $target_dir . basename($_FILES["document"]["name"]);
        if (move_uploaded_file($_FILES["document"]["tmp_name"], $target_file)) {
            $document_path = '/uploads/courses/documents/' . basename($_FILES["document"]["name"]);
        }
    }

    // Update course
    $data = [
        'category_id' => $_POST['category_id'],
        'title' => $_POST['title'],
        'type' => $_POST['type'],
        'description' => $_POST['description'],
        'status' => $_POST['status'],
        'url' => $_POST['type'] === 'video' ? $_POST['url'] : null,
        'document_path' => $document_path
    ];

    if ($teacher->updateCourse($_GET['id'], $data)) {
        header('Location: /yooudemy/controllers/teacher/dashboard.php?success=Course updated successfully');
        exit;
    } else {
        header('Location: /yooudemy/controllers/teacher/edit-course.php?id=' . $_GET['id'] . '&error=Failed to update course');
        exit;
    }
}

// Load view
require_once '../../views/Dashboard/teacher/edit-course.php';
