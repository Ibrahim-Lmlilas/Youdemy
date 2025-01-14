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

// Check if name is provided
if(!isset($_POST['name']) || empty(trim($_POST['name']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tag name is required']);
    exit();
}

$admin = new Admin();
$name = trim($_POST['name']);

if($admin->addTag($name)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to add tag']);
}
