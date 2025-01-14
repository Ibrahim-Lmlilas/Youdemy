<?php
require_once __DIR__ . '/../../models/Admin.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';

// Check if user is logged in and is admin
SessionHelper::requireAdmin();

// Get category data from POST request
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';

if ($id <= 0 || empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Invalid category data']);
    exit;
}

// Initialize Admin model
$admin = new Admin();

// Update category
$success = $admin->updateCategory($id, $name);

// Return response
echo json_encode([
    'success' => $success,
    'message' => $success ? 'Category updated successfully' : 'Failed to update category'
]);
