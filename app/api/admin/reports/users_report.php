<?php
require_once __DIR__ . '/../../../models/Admin.php';
require_once __DIR__ . '/../../../helpers/SessionHelper.php';

// Check if user is logged in and is admin
SessionHelper::requireAdmin();

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="users_report.xls"');
header('Cache-Control: max-age=0');

// Create Excel content
$excel = "Name\tEmail\tRole\tCreated At\n";

// Get users data
$admin = new Admin();
$users = $admin->getAllUsers();

// Add users to Excel
foreach ($users as $user) {
    $excel .= sprintf(
        "%s\t%s\t%s\t%s\n",
        $user['name'],
        $user['email'],
        $user['role'],
        $user['created_at']
    );
}

// Output Excel content
echo $excel;
