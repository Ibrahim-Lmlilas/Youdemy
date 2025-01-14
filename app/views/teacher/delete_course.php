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
    if (!isset($_POST['course_id']) || !is_numeric($_POST['course_id'])) {
        $_SESSION['error_message'] = "Invalid course ID";
        header('Location: courses.php');
        exit();
    }

    try {
        $db = Database::getInstance();
        $course = new TeacherCourse($db->getConnection());
        
        // Set course properties
        $course->setId(filter_input(INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT));
        $course->setTeacherId($_SESSION['user_id']);
        
        // Get course details before deletion
        if (!$course->findById($course->getId())) {
            throw new Exception("Course not found");
        }

        // Verify ownership
        if ($course->getTeacherId() != $_SESSION['user_id']) {
            throw new Exception("You don't have permission to delete this course");
        }
        
        // Store content for file deletion
        $oldContent = $course->getContent();
        
        // Delete course and related data
        if ($course->delete()) {
            // If it was a document, delete the file
            if (!filter_var($oldContent, FILTER_VALIDATE_URL)) {
                $uploadDir = __DIR__ . '/../../../uploads/documents/';
                $oldFile = $uploadDir . $oldContent;
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            
            $_SESSION['success_message'] = "Course and all related data deleted successfully!";
        } else {
            throw new Exception("Failed to delete course");
        }

    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error deleting course: " . $e->getMessage();
    }
    
    header('Location: courses.php');
    exit();
}

// If not POST request, redirect to courses page
header('Location: courses.php');
exit();
?>
