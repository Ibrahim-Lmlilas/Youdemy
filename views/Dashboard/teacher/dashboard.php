<?php
session_start();
require_once '../../../models/Teacher.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header('Location: /Youdemy/views/auth/login.php');
    exit;
}

// Create teacher instance
$teacher = new Teacher();
$teacher->setId($_SESSION['user_id']);

// Get teacher's courses
$courses = $teacher->getCourses();

// Get statistics
$total_courses = count($courses);
$published_courses = array_filter($courses, function($course) {
    return $course['status'] === 'published';
});
$total_published = count($published_courses);
$total_draft = $total_courses - $total_published;
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

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- Total Courses Card -->
                <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-gradient-to-r from-blue-400 to-blue-600 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-gray-600 text-sm font-semibold uppercase tracking-wider">Total Courses</h2>
                                <p class="text-3xl font-bold text-gray-800"><?php echo $total_courses; ?></p>
                                <p class="text-sm text-gray-500 mt-1">All your courses</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Published Courses Card -->
                <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-gradient-to-r from-green-400 to-green-600 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-gray-600 text-sm font-semibold uppercase tracking-wider">Published</h2>
                                <p class="text-3xl font-bold text-gray-800"><?php echo $total_published; ?></p>
                                <p class="text-sm text-gray-500 mt-1">Live courses</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Draft Courses Card -->
                <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-gradient-to-r from-yellow-400 to-yellow-600 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-gray-600 text-sm font-semibold uppercase tracking-wider">Drafts</h2>
                                <p class="text-3xl font-bold text-gray-800"><?php echo $total_draft; ?></p>
                                <p class="text-sm text-gray-500 mt-1">Work in progress</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Courses Section -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Your Courses</h2>
                <?php if (empty($courses)): ?>
                    <div class="text-center py-8">
                        <p class="text-gray-500">You haven't created any courses yet.</p>
                        <a href="add-course.php" class="inline-block mt-4 bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors duration-300">Create Your First Course</a>
                    </div>
                <?php else: ?>
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
                                    <?php if (!empty($course['tags'])): ?>
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <?php foreach ($course['tags'] as $tag): ?>
                                            <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full"><?php echo htmlspecialchars($tag); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                    <div class="flex justify-between items-center">
                                        <div class="text-sm text-gray-500">
                                            Created: <?php echo date('M j, Y', strtotime($course['created_at'])); ?>
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
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Chart.js for statistics -->
    <script>
        // Add your charts here if needed
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
                    // Remove the course card from the UI
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
