<?php
require_once __DIR__ . '/../../../models/Admin.php';
require_once __DIR__ . '/../../../helpers/SessionHelper.php';

// Check if user is logged in and is admin
SessionHelper::requireAdmin();

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="courses_report.xls"');
header('Cache-Control: max-age=0');

// Create Excel content
$excel = "Course Title\tTeacher\tCategory\tStatus\tCreated At\n";

// Get courses data
$admin = new Admin();
$courses = $admin->getAllCourses();

// Add courses to Excel
foreach ($courses as $course) {
    $excel .= sprintf(
        "%s\t%s\t%s\t%s\t%s\n",
        $course['title'],
        $course['teacher_name'],
        $course['category_name'],
        $course['status'],
        $course['created_at']
    );
}

// Output Excel content
echo $excel;
