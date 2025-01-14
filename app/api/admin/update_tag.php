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

// Check if required parameters are provided
if(!isset($_POST['tag_id']) || !isset($_POST['name']) || empty(trim($_POST['name']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tag ID and name are required']);
    exit();
}

$admin = new Admin();
$tagId = $_POST['tag_id'];
$name = trim($_POST['name']);

if($admin->updateTag($tagId, $name)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update tag']);
}
