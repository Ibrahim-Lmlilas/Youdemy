<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once __DIR__ . '/../../models/Admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
        $_SESSION['error_message'] = "Invalid user ID";
        header('Location: users.php');
        exit();
    }

    try {
        $admin = new Admin();
        $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
        
        // Suspend user
        if ($admin->updateUserStatus($userId, 'suspended')) {
            $_SESSION['success_message'] = "User suspended successfully";
        } else {
            throw new Exception("Failed to suspend user");
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
    
    header('Location: users.php');
    exit();
}
?>
