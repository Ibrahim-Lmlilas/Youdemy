<?php

require_once __DIR__ . '/../models/Admin.php';

class TagCategoryController {
    private $admin;

    public function __construct() {
        $this->admin = new Admin();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $action = $_POST['action'] ?? '';
        $success = false;
        
        try {
            switch($action) {
                case 'addTag':
                    $success = $this->admin->addTag($_POST['name']);
                    $_SESSION['success_message'] = 'Tag added successfully';
                    break;
                    
                case 'updateTag':
                    $success = $this->admin->updateTag($_POST['id'], $_POST['name']);
                    $_SESSION['success_message'] = 'Tag updated successfully';
                    break;
                    
                case 'deleteTag':
                    $success = $this->admin->deleteTag($_POST['id']);
                    $_SESSION['success_message'] = 'Tag deleted successfully';
                    break;
                    
                case 'addCategory':
                    $success = $this->admin->addCategory($_POST['name'], $_POST['description'] ?? '');
                    $_SESSION['success_message'] = 'Category added successfully';
                    break;
                    
                case 'updateCategory':
                    $success = $this->admin->updateCategory($_POST['id'], $_POST['name'], $_POST['description'] ?? '');
                    $_SESSION['success_message'] = 'Category updated successfully';
                    break;
                    
                case 'deleteCategory':
                    $success = $this->admin->deleteCategory($_POST['id']);
                    $_SESSION['success_message'] = 'Category deleted successfully';
                    break;
            }
            
            if (!$success) {
                throw new Exception('Operation failed');
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
        }
        
 
        
        // For regular form submissions, redirect back
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}
