<?php
session_start();
require_once '../../models/Teacher.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header('Location: /Youdemy/views/auth/login.php');
    exit;
}

$teacher = new Teacher();
$teacher->setId($_SESSION['user_id']);

$title = $_POST['title'] ?? '';
$category_id = $_POST['category_id'] ?? '';
$type = $_POST['type'] ?? '';
$description = $_POST['description'] ?? '';
$url = $_POST['url'] ?? '';
$tags = $_POST['tags'] ?? [];

$errors = [];

if (empty($title)) {
    $errors['title'] = "Title is required";
}
if (empty($category_id)) {
    $errors['category_id'] = "Category is required";
}
if (empty($type)) {
    $errors['type'] = "Type is required";
}
if (empty($description)) {
    $errors['description'] = "Description is required";
}

if ($type === 'video' && empty($url)) {
    $errors['url'] = "URL is required for video courses";
}

if ($type === 'document' && empty($_FILES['document']['name'])) {
    $errors['document'] = "Document is required for document courses";
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = $_POST;  
    header('Location: /Youdemy/views/Dashboard/teacher/add-course.php');
    exit;
}

$document_path = '';
if ($type === 'document' && isset($_FILES['document'])) {
    $target_dir = "../../uploads/courses/documents/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["document"]["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($_FILES["document"]["tmp_name"], $target_file)) {
        $document_path = '/Youdemy/uploads/courses/documents/' . $new_filename;
    } else {
        $_SESSION['errors'] = ['upload' => 'Failed to upload document'];
        header('Location: /Youdemy/views/Dashboard/teacher/add-course.php');
        exit;
    }
}

$courseData = [
    'title' => $title,
    'category_id' => $category_id,
    'type' => $type,
    'description' => $description,
    'url' => $type === 'video' ? $url : '',
    'document_path' => $type === 'document' ? $document_path : '',
    'status' => 'draft',  
    'tags' => $tags  
];

if ($teacher->createCourse($courseData)) {
    $_SESSION['success'] = "Course created successfully!";
    header('Location: /Youdemy/views/Dashboard/teacher/dashboard.php');
    exit;
} else {
    $_SESSION['errors'] = ['db' => 'Failed to create course'];
    header('Location: /Youdemy/views/Dashboard/teacher/add-course.php');
    exit;
}
