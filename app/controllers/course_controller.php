<?php
require_once '../config/Database.php';
require_once '../models/Course.php';
require_once '../models/VideoCourse.php';
require_once '../models/DocumentCourse.php';

session_start();

// Check if user is logged in and is a teacher
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacherId = $_SESSION['user_id'];
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $type = $_POST['courseType'] ?? '';

    if (empty($title) || empty($description) || empty($type)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    try {
        $db = new Database();
        
        if ($type === 'video') {
            $videoUrl = $_POST['videoUrl'] ?? '';
            $duration = $_POST['duration'] ?? 0;

            $course = new VideoCourse($db);
            $course->setTeacherId($teacherId);
            $course->setTitle($title);
            $course->setDescription($description);
            $course->setVideoUrl($videoUrl);
            $course->setDuration($duration);
            
            $success = $course->save();
        } else {
            // Handle document upload
            if (!isset($_FILES['documentFile'])) {
                echo json_encode(['success' => false, 'message' => 'No document file uploaded']);
                exit;
            }

            $file = $_FILES['documentFile'];
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileError = $file['error'];
            
            // Validate file
            $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
            $fileType = mime_content_type($fileTmpName);
            
            if (!in_array($fileType, $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Invalid file type']);
                exit;
            }

            if ($fileError !== 0) {
                echo json_encode(['success' => false, 'message' => 'Error uploading file']);
                exit;
            }

            // Create upload directory if it doesn't exist
            $uploadDir = '../../uploads/documents/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate unique filename
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
            $uniqueFileName = uniqid('doc_') . '.' . $fileExt;
            $targetPath = $uploadDir . $uniqueFileName;

            // Move uploaded file
            if (move_uploaded_file($fileTmpName, $targetPath)) {
                $pageCount = $_POST['pageCount'] ?? 0;
                
                $course = new DocumentCourse($db);
                $course->setTeacherId($teacherId);
                $course->setTitle($title);
                $course->setDescription($description);
                $course->setDocumentUrl($uniqueFileName);
                $course->setPageCount($pageCount);
                
                $success = $course->save();
            } else {
                echo json_encode(['success' => false, 'message' => 'Error saving file']);
                exit;
            }
        }

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Course added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error saving course']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
