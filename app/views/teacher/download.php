<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header('Location: ../auth/login.php');
    exit();
}

$file = isset($_GET['file']) ? $_GET['file'] : '';

// Security check: make sure the file is in the uploads directory
$uploadDir = __DIR__ . '/../../public/uploads/documents/';
$filePath = realpath($uploadDir . basename($file));

if ($filePath === false || strpos($filePath, $uploadDir) !== 0) {
    die('Invalid file path');
}

// Check if file exists
if (!file_exists($filePath)) {
    die('File not found');
}

// Get file info
$fileName = basename($filePath);
$fileSize = filesize($filePath);
$fileType = mime_content_type($filePath);

// Set headers for download
header('Content-Type: ' . $fileType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file
readfile($filePath);
exit();
