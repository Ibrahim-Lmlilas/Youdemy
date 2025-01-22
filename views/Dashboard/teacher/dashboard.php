<?php
session_start();
require_once '../../../models/Teacher.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header('Location: /Youdemy/views/auth/login.php');
    exit;
}

$teacher = new Teacher();
$teacher->setId($_SESSION['user_id']);

$courses = $teacher->getCourses();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Youdemy</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js for statistics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../../../assets/css/Dashboard.css">
    <style>
        /* Custom scrollbar styles */
        .overflow-y-auto::-webkit-scrollbar {
            width: 8px;
        }

        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #666;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Include navbar -->
    <?php include '../../components/navbar.php'; ?>

    <div class="flex">
        <!-- Include sidebar -->
        <?php include '../../components/sidebar.php'; ?>

        <!-- Main content -->
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Dashboard Overview</h1>
                <a href="add-course.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors duration-300">
                    Add New Course
                </a>
            </div>



            <!-- Courses Section -->
            <div class="bg-white rounded-lg shadow-lg p-6 h-[500px] flex flex-col">
                <h2 class="text-xl font-semibold mb-4">Your Courses</h2>
                <?php if (empty($courses)): ?>
                    <div class="text-center py-8">
                        <p class="text-gray-500">You haven't created any courses yet.</p>
                        <a href="add-course.php" class="inline-block mt-4 bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors duration-300">Create Your First Course</a>
                    </div>
                <?php else: ?>
                    <div class="flex-1 overflow-y-auto pr-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($courses as $course): ?>
                                <div class="bg-white border rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300" data-course-id="<?php echo $course['id']; ?>">
                                    <div class="p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center">
                                                <svg class="w-6 h-6 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                                <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($course['title']); ?></h3>
                                            </div>
                                            <span class="px-2 py-1 text-xs rounded-full <?php echo $course['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                <?php echo ucfirst($course['status']); ?>
                                            </span>
                                        </div>
                                        <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($course['description'] ?? 'No description available'); ?></p>
                                        <?php if (isset($course['tags'])): ?>
                                        <div class="flex flex-wrap gap-2 mb-4">
                                            <?php foreach ($course['tags'] as $tag): ?>
                                                <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full"><?php echo htmlspecialchars($tag); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                        <div class="flex justify-between items-center">
                                            <div class="text-sm text-gray-500">
                                                Created: <?php echo date('M j- Y', strtotime($course['created_at'])); ?>
                                            </div>
                                            <div class="flex space-x-2">
                                                <button onclick="editCourse(<?php echo $course['id']; ?>)" class="text-blue-500 hover:text-blue-600">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                <button onclick="deleteCourse(<?php echo $course['id']; ?>)" class="text-red-500 hover:text-red-600">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    </script>

    <script>
        async function deleteCourse(courseId) {
            if (!confirm('Are you sure you want to delete this course?')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('course_id', courseId);

                const response = await fetch('/Youdemy/controllers/teacher/delete-course.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    const courseCard = document.querySelector(`[data-course-id="${courseId}"]`);
                    courseCard.remove();
                    alert('Course deleted successfully');
                } else {
                    alert(data.error || 'Failed to delete course');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while deleting the course');
            }
        }

        function editCourse(courseId) {
            window.location.href = `edit-course.php?id=${courseId}`;
        }
    </script>
</body>
</html>
