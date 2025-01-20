<?php
session_start();
require_once '../../../models/Student.php';
require_once '../../../models/Course.php';

// Check if user is logged in and is student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: /Youdemy/views/auth/login.php');
    exit;
}

// Initialize Student object
$student = new Student($_SESSION['user_id']);

// Initialize Course object
$course = new Course();

// Get filter values
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;
$selectedTag = isset($_GET['tag']) ? $_GET['tag'] : null;

// Get categories and tags for filters
$categories = $course->getAllCategories();
$tags = $course->getAllTags();

// Get filtered courses
$availableCourses = $course->getFilteredCourses($selectedCategory, $selectedTag, $_SESSION['user_id']);
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

            <!-- Filter Section -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Filter Courses</h3>
                <form method="GET" class="flex flex-wrap gap-4">
                    <!-- Category Filter -->
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                </svg>
                                Category
                            </span>
                        </label>
                        <div class="relative">
                            <select name="category" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-md">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $selectedCategory == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Tag Filter -->
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                Tag
                            </span>
                        </label>
                        <div class="relative">
                            <select name="tag" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-md">
                                <option value="">All Tags</option>
                                <?php foreach ($tags as $tag): ?>
                                    <option value="<?php echo $tag['id']; ?>" <?php echo $selectedTag == $tag['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tag['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Button -->
                    <div class="flex items-end space-x-2">
                        <?php if ($selectedCategory || $selectedTag): ?>
                            <a href="?" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Clear Filters
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Available Courses Section -->
            <div class="bg-white rounded-lg shadow-lg p-6 h-[500px] flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">My Learning Dashboard</h2>
                    <div class="text-sm text-gray-500">
                        <?php echo count($availableCourses); ?> course<?php echo count($availableCourses) !== 1 ? 's' : ''; ?> found
                    </div>
                </div>

                <?php if (empty($availableCourses)): ?>
                    <div class="flex-1 flex flex-col items-center justify-center text-center py-8">
                        <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-gray-500 text-lg mb-2">No courses found matching your filters</p>
                        <?php if ($selectedCategory || $selectedTag): ?>
                            <a href="?" class="text-blue-500 hover:text-blue-600 font-medium">
                                Clear all filters
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="flex-1 overflow-y-auto pr-2">
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
                                        <form method="POST" action="/Youdemy/controllers/student/enroll.php" class="block">
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
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    // Auto-submit form when select values change
    document.querySelectorAll('select[name="category"], select[name="tag"]').forEach(select => {
        select.addEventListener('change', () => {
            select.closest('form').submit();
        });
    });
    </script>
</body>
</html>
