<?php
session_start();
require_once __DIR__ . '/../../models/Admin.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';

// Check if user is logged in and is an admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if tag_id is provided
if(!isset($_POST['tag_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tag ID is required']);
    exit();
}

$admin = new Admin();
$tagId = $_POST['tag_id'];

if($admin->deleteTag($tagId)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete tag. The tag might be in use.']);
}
