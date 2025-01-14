<?php
session_start();

// Check if user is logged in and is a teacher
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/TeacherCourse.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $course = new TeacherCourse($db->getConnection());
    
    try {
        $course_id = filter_input(INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT);
        
        if ($course->findById($course_id)) {
            // Check if this course belongs to the current teacher
            if ($course->teacher_id == $_SESSION['user_id']) {
                echo json_encode([
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'content' => $course->content,
                    'category_id' => $course->category_id,
                    'tag_ids' => $course->tag_ids
                ]);
            } else {
                throw new Exception("Unauthorized access");
            }
        } else {
            throw new Exception("Course not found");
        }
    } catch (Exception $e) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
