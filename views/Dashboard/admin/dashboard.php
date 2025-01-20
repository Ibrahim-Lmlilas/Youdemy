<?php
session_start();
require_once '../../../models/Admin.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /Youdemy/views/auth/login.php');
    exit;
}

// Initialize Admin object
$admin = new Admin();

// Get dashboard statistics
$dashboard = $admin->getDashboard();
$users_summary = $dashboard['users_summary'];
$courses_summary = $dashboard['courses_summary'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Youdemy</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js for statistics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../../../assets/css/Dashboard.css">

</head>
<body class="bg-gray-100">
    <!-- Include navbar -->
    <?php include '../../components/navbar.php'; ?>

    <div class="flex">
        <!-- Include sidebar -->
        <?php include '../../components/sidebar.php'; ?>

        <!-- Main content -->
        <div class="flex-1 p-8">
            <h1 class="text-2xl font-bold mb-6">Dashboard Overview</h1>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- Total Users Card -->
                <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-gradient-to-r from-blue-400 to-blue-600 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-gray-600 text-sm font-semibold uppercase tracking-wider">Total Users</h2>
                                <p class="text-3xl font-bold text-gray-800"><?php echo $users_summary['total_users']; ?></p>
                                <p class="text-sm text-gray-500 mt-1">Active accounts</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Students Card -->
                <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-gradient-to-r from-green-400 to-green-600 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-gray-600 text-sm font-semibold uppercase tracking-wider">Students</h2>
                                <p class="text-3xl font-bold text-gray-800"><?php echo $users_summary['total_students']; ?></p>
                                <p class="text-sm text-gray-500 mt-1">Enrolled students</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Teachers Card -->
                <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-gradient-to-r from-yellow-400 to-yellow-600 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-gray-600 text-sm font-semibold uppercase tracking-wider">Teachers</h2>
                                <p class="text-3xl font-bold text-gray-800"><?php echo $users_summary['total_teachers']; ?></p>
                                <p class="text-sm text-gray-500 mt-1">Active instructors</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Courses Card -->
                <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-gradient-to-r from-purple-400 to-purple-600 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-gray-600 text-sm font-semibold uppercase tracking-wider">Courses</h2>
                                <p class="text-3xl font-bold text-gray-800"><?php echo $courses_summary['total_courses']; ?></p>
                                <p class="text-sm text-gray-500 mt-1">Total courses</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Teachers Card -->
                <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-gradient-to-r from-orange-400 to-orange-600 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-gray-600 text-sm font-semibold uppercase tracking-wider">Pending Teachers</h2>
                                <p class="text-3xl font-bold text-gray-800"><?php 
                                    $pending_teachers = array_filter($admin->searchUsers('teacher', ''), function($teacher) {
                                        return $teacher['status'] === 'pending';
                                    });
                                    echo count($pending_teachers);
                                ?></p>
                                <p class="text-sm text-gray-500 mt-1">Awaiting approval</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Published Courses Card -->
                <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-gradient-to-r from-teal-400 to-teal-600 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-gray-600 text-sm font-semibold uppercase tracking-wider">Published Courses</h2>
                                <p class="text-3xl font-bold text-gray-800"><?php echo $courses_summary['published_courses']; ?></p>
                                <p class="text-sm text-gray-500 mt-1">Active courses</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
