<?php
session_start();

// Check if user is logged in and is a teacher
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header('Location: ../auth/login.php');
    exit();
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/TeacherCourse.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $course = new TeacherCourse($db->getConnection());
    
    try {
        // Set course properties
        $course->id = filter_input(INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT);
        $course->title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $course->description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $course->category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
        $course->teacher_id = $_SESSION['user_id'];
        
        // Handle content based on type
        $content_type = filter_input(INPUT_POST, 'content_type', FILTER_SANITIZE_STRING);
        if ($content_type === 'video') {
            $course->content = filter_input(INPUT_POST, 'video_url', FILTER_SANITIZE_URL);
        } else {
            // Handle document upload if new file is provided
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
                        // Delete old document if exists
                        $oldCourse = new TeacherCourse($db->getConnection());
                        $oldCourse->findById($course->id);
                        if (!filter_var($oldCourse->content, FILTER_VALIDATE_URL)) {
                            $oldFile = $uploadDir . $oldCourse->content;
                            if (file_exists($oldFile)) {
                                unlink($oldFile);
                            }
                        }
                        
                        $course->content = $uniqueFileName;
                    } else {
                        throw new Exception("Failed to upload document");
                    }
                } else {
                    throw new Exception("Invalid file type");
                }
            }
        }

        // Update course
        if ($course->update()) {
            // Update tags
            $course->updateTags($_POST['tags'] ?? []);
            
            $_SESSION['success_message'] = "Course updated successfully!";
        } else {
            throw new Exception("Failed to update course");
        }

        header('Location: courses.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error updating course: " . $e->getMessage();
        header('Location: courses.php');
        exit();
    }
}
?>
