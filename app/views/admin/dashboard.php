<?php
session_start();

require_once __DIR__ . '/../../models/Admin.php';

// Check if user is logged in and is an admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$admin = new Admin();
$totalUsers = $admin->getTotalUsers();
$activeCourses = $admin->getActiveCourseCount();
$totalEnrollments = $admin->getTotalEnrollments();
$userName = $_SESSION['user_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            background-image: linear-gradient(135deg, #f0f0f0 25%, transparent 25%),
                            linear-gradient(225deg, #f0f0f0 25%, transparent 25%),
                            linear-gradient(45deg, #f0f0f0 25%, transparent 25%),
                            linear-gradient(315deg, #f0f0f0 25%, #f3f4f6 25%);
            background-position: 10px 0, 10px 0, 0 0, 0 0;
            background-size: 20px 20px;
            background-repeat: repeat;
        }
        /* Decorative Circles */
        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            z-index: -1;
        }
        body::before {
            width: 450px;
            height: 450px;
            background:rgb(0, 53, 114);
            top: -100px;
            right: -100px;
            animation: float 8s ease-in-out infinite;
        }
        body::after {
            width: 250px;
            height: 250px;
            background: rgb(10, 20, 74);
            bottom: -50px;
            left: -50px;
            backdrop-filter: blur(10px);
            animation: float 7s ease-in-out infinite reverse;
            z-index: 2;
            opacity: 0.5;
        }
        @keyframes float {
            0% {
                transform: translate(0, 0) rotate(0deg);
            }
            50% {
                transform: translate(30px, 30px) rotate(5deg);
            }
            100% {
                transform: translate(0, 0) rotate(0deg);
            }
        }
        .sidebar {
            background: rgba(31, 41, 55, 0.95);
            backdrop-filter: blur(10px);
            min-height: calc(100vh - 4rem);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1;
            opacity: 0.7;
        }
        .sidebar-link {
            color: #9ca3af;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border-radius: 0.5rem;
            margin: 0.25rem 0.5rem;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }
        .sidebar-link svg {
            width: 1.25rem;
            height: 1.25rem;
            margin-right: 0.75rem;
        }
        .content-area {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            position: relative;
            z-index: 1;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
        }
        .stat-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        nav.bg-white {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
        }
        .logout-btn {
            background: linear-gradient(135deg, #ff4b4b 0%, #ff9797 100%);
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 75, 75, 0.4);
        }
    </style>
</head>
<body class="min-h-screen">
<?php include '../components/navbar.php'; ?>

    <div class="flex">
        <?php include '../components/sidebar.php'; ?>
        <main class="flex-1 p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- Active Users Card -->
                <div class="stat-card">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-500 bg-opacity-10">
                            <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5">
                            <h4 class="text-2xl font-semibold text-gray-700"><?= number_format($totalUsers) ?></h4>
                            <p class="text-gray-600">Total Users</p>
                        </div>
                    </div>
                </div>

                <!-- Active Courses Card -->
                <div class="stat-card">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-500 bg-opacity-10">
                            <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <div class="ml-5">
                            <h4 class="text-2xl font-semibold text-gray-700"><?= number_format($activeCourses) ?></h4>
                            <p class="text-gray-600">Active Courses</p>
                        </div>
                    </div>
                </div>

                <!-- Total Enrollments Card -->
                <div class="stat-card">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-500 bg-opacity-10">
                            <svg class="h-8 w-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                        </div>
                        <div class="ml-5">
                            <h4 class="text-2xl font-semibold text-gray-700"><?= number_format($totalEnrollments) ?></h4>
                            <p class="text-gray-600">Total Enrollments</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="content-area">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">Recent Activity</h2>
                    <p class="text-gray-600">Coming soon...</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
