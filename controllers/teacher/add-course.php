<?php
session_start();
require_once __DIR__ . '/../../models/Teacher.php';

// Check wach user mconnecti w wach teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header('Location: /yooudemy/views/auth/login.php');
    exit;
}

// Initialiser teacher
$teacher = new Teacher();
$teacher->setId($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Upload file ila kan type = document
    $document_path = null;
    if ($_POST['type'] === 'document' && isset($_FILES['document'])) {
        $target_dir = "../../uploads/courses/documents/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $target_file = $target_dir . basename($_FILES["document"]["name"]);
        if (move_uploaded_file($_FILES["document"]["tmp_name"], $target_file)) {
            $document_path = '/uploads/courses/documents/' . basename($_FILES["document"]["name"]);
        }
    }

    // Process tags - Only use existing tags
    $tag_ids = [];
    if (!empty($_POST['tags'])) {
        foreach ($_POST['tags'] as $tag_id) {
            if (is_numeric($tag_id)) {
                $tag_ids[] = (int)$tag_id;
            }
        }
    }

    // Create course
    $data = [
        'category_id' => $_POST['category_id'],
        'title' => $_POST['title'],
        'type' => $_POST['type'],
        'description' => $_POST['description'],
        'url' => $_POST['type'] === 'video' ? $_POST['url'] : null,
        'document_path' => $document_path,
        'tag_ids' => $tag_ids
    ];

    if ($teacher->createCourse($data)) {
        header('Location: /yooudemy/controllers/teacher/dashboard.php?success=Course created successfully');
        exit;
    } else {
        header('Location: /yooudemy/controllers/teacher/dashboard.php?error=Failed to create course');
        exit;
    }
} else {
    // Show add course form
    include '../../views/Dashboard/teacher/add-course.php';
}
?>
