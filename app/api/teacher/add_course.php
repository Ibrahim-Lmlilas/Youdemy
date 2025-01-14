<?php
require_once __DIR__ . '/../../models/Course.php';
require_once __DIR__ . '/../../models/VideoCourse.php';
require_once __DIR__ . '/../../models/DocumentCourse.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';

// Check if user is logged in and is a teacher
SessionHelper::requireTeacher();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        // Get form data
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $category_id = (int)($_POST['category'] ?? 0);
        $tags = $_POST['tags'] ?? [];
        
        // Validate required fields
        if (empty($title) || empty($description) || empty($type)) {
            throw new Exception('Please fill in all required fields');
        }

        // Create appropriate course type
        if ($type === 'video') {
            $course = new VideoCourse();
            
            // Handle video upload
            if (isset($_FILES['content']) && $_FILES['content']['error'] === UPLOAD_ERR_OK) {
                $videoFile = $_FILES['content'];
                $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];
                
                if (!in_array($videoFile['type'], $allowedTypes)) {
                    throw new Exception('Invalid video format. Please upload MP4, WebM, or OGG files.');
                }
                
                $uploadDir = __DIR__ . '/../../../public/uploads/videos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = uniqid() . '_' . basename($videoFile['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (!move_uploaded_file($videoFile['tmp_name'], $targetPath)) {
                    throw new Exception('Failed to upload video file');
                }
                
                $course->setVideoUrl('/uploads/videos/' . $fileName);
            }
        } else {
            $course = new DocumentCourse();
            
            // Handle document upload
            if (isset($_FILES['content']) && $_FILES['content']['error'] === UPLOAD_ERR_OK) {
                $docFile = $_FILES['content'];
                $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                
                if (!in_array($docFile['type'], $allowedTypes)) {
                    throw new Exception('Invalid document format. Please upload PDF or Word documents.');
                }
                
                $uploadDir = __DIR__ . '/../../../public/uploads/documents/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = uniqid() . '_' . basename($docFile['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (!move_uploaded_file($docFile['tmp_name'], $targetPath)) {
                    throw new Exception('Failed to upload document file');
                }
                
                $course->setDocumentUrl('/uploads/documents/' . $fileName);
            }
        }
        
        // Set common course properties
        $course->setTitle($title);
        $course->setDescription($description);
        $course->setTeacherId($_SESSION['user_id']);
        if ($category_id > 0) {
            $course->setCategoryId($category_id);
        }
        
        // Save course
        if ($course->save()) {
            // Add tags
            if (!empty($tags)) {
                $course->setTags($tags);
            }
            
            $response['success'] = true;
            $response['message'] = 'Course added successfully';
        } else {
            throw new Exception('Failed to save course');
        }
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
