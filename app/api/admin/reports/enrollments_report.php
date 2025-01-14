<?php
require_once __DIR__ . '/../../../models/Admin.php';
require_once __DIR__ . '/../../../helpers/SessionHelper.php';

// Check if user is logged in and is admin
SessionHelper::requireAdmin();

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="enrollments_report.xls"');
header('Cache-Control: max-age=0');

// Create Excel content
$excel = "Student\tCourse\tTeacher\tEnrolled At\n";

// Get enrollments data
$admin = new Admin();
$enrollments = $admin->getAllEnrollments();

// Add enrollments to Excel
foreach ($enrollments as $enrollment) {
    $excel .= sprintf(
        "%s\t%s\t%s\t%s\n",
        $enrollment['student_name'],
        $enrollment['course_title'],
        $enrollment['teacher_name'],
        $enrollment['created_at']
    );
}

// Output Excel content
echo $excel;
