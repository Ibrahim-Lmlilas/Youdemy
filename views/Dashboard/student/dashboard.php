<?php
session_start();
require_once '../../../models/Student.php';
require_once '../../../models/Course.php';

// Check if user is logged in and is student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: /yooudemy/views/auth/login.php');
    exit;
}

// Initialize Student object
$student = new Student($_SESSION['user_id']);

// Get available courses
$course = new Course();
$availableCourses = $course->getAllPublishedCourses();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Youdemy</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
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
            <h1 class="text-2xl font-bold mb-6">My Learning Dashboard</h1>

            <!-- Display messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Available Courses Section -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Available Courses</h2>
                <?php if (empty($availableCourses)): ?>
                    <p class="text-gray-500">No courses available for enrollment.</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($availableCourses as $course): ?>
                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="px-3 py-1 text-sm font-medium rounded-full 
                                            <?php echo isset($course['type']) && $course['type'] === 'video' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'; ?>">
                                            <?php echo ucfirst(isset($course['type']) ? $course['type'] : 'Document'); ?>
                                        </span>
                                    </div>
                                    <h3 class="font-semibold text-lg mb-2"><?php echo htmlspecialchars($course['title']); ?></h3>
                                    <?php if (!empty($course['tags'])): ?>
                                        <div class="flex flex-wrap gap-2 mb-4">
                                            <?php foreach (explode(',', $course['tags']) as $tag): ?>
                                                <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-700 rounded-full">
                                                    <?php echo htmlspecialchars(trim($tag)); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="space-y-2 mb-4">
                                        <p class="text-gray-600 text-sm flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                            </svg>
                                            <?php echo htmlspecialchars($course['category_name']); ?>
                                        </p>
                                        <p class="text-gray-600 text-sm flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <?php echo htmlspecialchars($course['teacher_name']); ?>
                                        </p>
                                    </div>
                                    <form method="POST" action="/yooudemy/controllers/student/enroll.php" class="block">
                                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                        <?php if ($course['is_enrolled']): ?>
                                            <button disabled 
                                                class="w-full flex items-center justify-center px-4 py-2 rounded-md bg-indigo-100 text-indigo-600 hover:bg-indigo-200 transition-all duration-300 transform hover:scale-105">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"/>
                                                </svg>
                                                <span class="ml-2 text-sm font-medium">Enrolled</span>
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" name="enroll" 
                                                class="w-full flex items-center justify-center px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 transition-all duration-300 transform hover:scale-105">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10 3a7 7 0 100 14 7 7 0 000-14zm3 8h-2v2a1 1 0 11-2 0v-2H7a1 1 0 110-2h2V7a1 1 0 112 0v2h2a1 1 0 110 2z"/>
                                                </svg>
                                                <span class="ml-2 text-sm font-medium">Enroll Now</span>
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
