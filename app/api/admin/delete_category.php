<?php
require_once __DIR__ . '/../../models/Admin.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';

// Check if user is logged in and is admin
SessionHelper::requireAdmin();

// Get category ID from POST request
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
    exit;
}

// Initialize Admin model
$admin = new Admin();

// Delete category
$success = $admin->deleteCategory($id);

// Return response
echo json_encode([
    'success' => $success,
    'message' => $success ? 'Category deleted successfully' : 'Failed to delete category'
]);
