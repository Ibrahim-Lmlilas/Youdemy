<?php
require_once __DIR__ . '/../../models/Admin.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';

// Check if user is logged in and is admin
SessionHelper::requireAdmin();

// Get category name from POST request
$name = isset($_POST['name']) ? trim($_POST['name']) : '';

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Category name is required']);
    exit;
}

// Initialize Admin model
$admin = new Admin();

// Add category
$success = $admin->addCategory($name);

// Return response
echo json_encode([
    'success' => $success,
    'message' => $success ? 'Category added successfully' : 'Failed to add category'
]);
