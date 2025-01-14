<?php
session_start();

// Check if user is logged in and is a teacher
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../../config/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $db = new Database();
    
    try {
        $course_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        
        // Get course data
        $sql = "SELECT * FROM courses WHERE id = ? AND teacher_id = ?";
        $course = $db->query($sql, [$course_id, $_SESSION['user_id']])->fetch();
        
        if ($course) {
            // Get course tags
            $sql = "SELECT tag_id FROM course_tags WHERE course_id = ?";
            $tags = $db->query($sql, [$course_id])->fetchAll(PDO::FETCH_COLUMN);
            
            echo json_encode([
                'id' => $course['id'],
                'title' => $course['title'],
                'description' => $course['description'],
                'type' => $course['type'],
                'content_url' => $course['content_url'],
                'category_id' => $course['category_id'],
                'tags' => $tags
            ]);
        } else {
            throw new Exception("Course not found or unauthorized");
        }
    } catch (Exception $e) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => $e->getMessage()]);
    }
}
