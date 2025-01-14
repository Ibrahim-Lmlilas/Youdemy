<?php
session_start();

require_once __DIR__ . '/../../models/TeacherApprovals.php';

// Check if user is logged in and is an admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Handle approve/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacherApprovals = new TeacherApprovals();
    
    if (isset($_POST['approve'])) {
        $teacherApprovals->approveTeacher($_POST['teacher_id']);
    } elseif (isset($_POST['reject'])) {
        $teacherApprovals->rejectTeacher($_POST['teacher_id']);
    }
    
    // Redirect to avoid form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

$teacherApprovals = new TeacherApprovals();
$pendingTeachers = $teacherApprovals->getPendingTeachers();
$userName = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Approvals - Youdemy</title>
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
        .approve-btn {
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
            transition: all 0.3s ease;
        }
        .approve-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 211, 153, 0.4);
        }
        .reject-btn {
            background: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
            transition: all 0.3s ease;
        }
        .reject-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(248, 113, 113, 0.4);
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-full mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <img class="h-8 w-auto" src="../../../assets/img/C.jpg" alt="Logo">
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($userName); ?></span>
                    <a href="../auth/logout.php" class="logout-btn bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-all duration-200">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <aside class="sidebar w-64 flex-shrink-0">
            <nav class="mt-5 px-2">
                <a href="dashboard.php" class="sidebar-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard
                </a>
                <a href="users.php" class="sidebar-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Users
                </a>
                <a href="teacher_approvals.php" class="sidebar-link active">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Teacher Approvals
                </a>
                <a href="courses.php" class="sidebar-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    Courses
                </a>
                <a href="settings.php" class="sidebar-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Settings
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="content-area p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-900">Teacher Approval Requests</h2>
                </div>

                <!-- Teachers List -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach($pendingTeachers as $teacher): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-full bg-gray-200"></div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($teacher['name']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($teacher['email']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        <?php echo htmlspecialchars($teacher['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="teacher_id" value="<?php echo $teacher['id']; ?>">
                                        <button type="submit" name="approve" class="approve-btn text-white px-3 py-1 rounded-md">
                                            Approve
                                        </button>
                                        <button type="submit" name="reject" class="reject-btn text-white px-3 py-1 rounded-md">
                                            Reject
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($pendingTeachers)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                    No pending teacher approvals
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
