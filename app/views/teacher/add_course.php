<?php
session_start();

// Check if user is logged in and is a teacher
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header('Location: ../auth/login.php');
    exit();
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Course.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->getConnection();

    try {
        
        $conn->beginTransaction();

        
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
        $content_type = filter_input(INPUT_POST, 'content_type', FILTER_SANITIZE_STRING);
        $teacher_id = $_SESSION['user_id'];

        // Get content based on type
        $content = '';
        if ($content_type === 'video') {
            $content = filter_input(INPUT_POST, 'video_url', FILTER_SANITIZE_URL);
        } else {
            // Handle document upload
            if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['document'];
                $fileName = $file['name'];
                $fileType = $file['type'];
                $fileTmpName = $file['tmp_name'];
                
                // Define allowed file types
                $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
                                'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
                
                if (in_array($fileType, $allowedTypes)) {
                    // Create uploads directory if it doesn't exist
                    $uploadDir = __DIR__ . '/../../../uploads/documents/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    // Generate unique filename
                    $uniqueFileName = uniqid() . '_' . $fileName;
                    $destination = $uploadDir . $uniqueFileName;
                    
                    if (move_uploaded_file($fileTmpName, $destination)) {
                        $content = $uniqueFileName;
                    } else {
                        throw new Exception("Failed to upload document");
                    }
                } else {
                    throw new Exception("Invalid file type");
                }
            }
        }

        // Insert course
        $sql = "INSERT INTO courses (title, description, category_id, teacher_id, content) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$title, $description, $category_id, $teacher_id, $content]);
        $course_id = $conn->lastInsertId();

        // Handle tags
        if (isset($_POST['tags']) && is_array($_POST['tags'])) {
            foreach ($_POST['tags'] as $tag_id) {
                $sql = "INSERT INTO course_tags (course_id, tag_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$course_id, $tag_id]);
            }
        }

        // Commit transaction
        $conn->commit();

        // Redirect back to courses page with success message
        $_SESSION['success_message'] = "Course added successfully!";
        header('Location: courses.php');
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $_SESSION['error_message'] = "Error adding course: " . $e->getMessage();
        header('Location: courses.php');
        exit();
    }
}
?>
