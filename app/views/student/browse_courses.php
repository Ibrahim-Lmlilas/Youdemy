<?php
session_start();
require_once '../../models/Student.php';

// Check if user is logged in and is a student
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}

$userName = $_SESSION['user_name'];
$student = new Student();
$student->setId($_SESSION['user_id']);

// Get all available courses
$courses = $student->getAllPublishedCourses();

// Get enrolled course IDs for checking enrollment status
$enrolledCourses = $student->getEnrolledCourses();
$enrolledCourseIds = array_map(function($course) {
    return $course['id'];
}, $enrolledCourses);

// Handle search and filters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sortBy = $_GET['sort'] ?? 'newest';

// Filter courses based on search and category
if (!empty($search)) {
    $courses = array_filter($courses, function($course) use ($search) {
        return stripos($course['title'], $search) !== false || 
               stripos($course['description'], $search) !== false;
    });
}

if (!empty($category)) {
    $courses = array_filter($courses, function($course) use ($category) {
        return $course['category'] === $category;
    });
}

// Sort courses
switch ($sortBy) {
    case 'price_low':
        usort($courses, function($a, $b) { return $a['price'] - $b['price']; });
        break;
    case 'price_high':
        usort($courses, function($a, $b) { return $b['price'] - $a['price']; });
        break;
    case 'popular':
        usort($courses, function($a, $b) { return $b['enrolled_students'] - $a['enrolled_students']; });
        break;
    case 'newest':
    default:
        usort($courses, function($a, $b) { return strtotime($b['created_at']) - strtotime($a['created_at']); });
        break;
}

// Get unique categories for filter
$categories = array_unique(array_column($courses, 'category'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Courses - Youdemy</title>
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
            background: rgb(0, 53, 114);
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
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(30px, 30px) rotate(5deg); }
            100% { transform: translate(0, 0) rotate(0deg); }
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
        .course-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .course-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .course-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .course-icon {
            background: #f3f4f6;
            padding: 0.75rem;
            border-radius: 0.5rem;
            color: #3b82f6;
        }
        .course-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }
        .course-description {
            color: #4b5563;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        .course-category {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #3b82f6;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        .course-category svg {
            width: 1.25rem;
            height: 1.25rem;
        }
        .course-tag {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: #f3f4f6;
            color: #4b5563;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .course-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 0.875rem;
        }
        .course-actions {
            display: flex;
            gap: 0.5rem;
        }
        .action-button {
            padding: 0.5rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }
        .action-button:hover {
            background: #f3f4f6;
        }
        .action-button.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .action-button.disabled:hover {
            background: none;
        }
        .draft-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #fef3c7;
            color: #92400e;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 500;
        }
    </style>
</head>
<body class="min-h-screen">
    
<?php include '../components/navbar.php'; ?>

    <div class="flex">
        <?php include '../components/sidebar.php'; ?>
        <main class="flex-1 p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Browse Courses</h1>
            </div>

            <!-- Search and Filters -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="col-span-2">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search courses..." 
                               class="search-input w-full px-4 py-2 rounded-lg focus:outline-none">
                    </div>
                    <div>
                        <select name="category" class="filter-select w-full px-4 py-2 rounded-lg focus:outline-none">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" 
                                        <?php echo $category === $cat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <select name="sort" class="filter-select w-full px-4 py-2 rounded-lg focus:outline-none">
                            <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="popular" <?php echo $sortBy === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                            <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Course Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($courses as $course): ?>
                    <div class="course-card">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <?php 
                                $courseTags = !empty($course['tags']) ? explode(',', $course['tags']) : [];
                                if (!empty($courseTags)): 
                                    $firstTag = trim($courseTags[0]);
                                ?>
                                
                                <?php endif; ?>
                                <span class="course-tag bg-blue-100 text-blue-800">
                                    <?php echo htmlspecialchars($course['category_name'] ?? 'General'); ?>
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="course-tag bg-green-100 text-green-800 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                        </path>
                                    </svg>
                                    <?php echo $course['enrolled_students'] ?? 0; ?>
                                </span>
                                <?php if(($course['type'] ?? 'video') === 'video'): ?>
                                    <span class="course-tag bg-purple-100 text-purple-800 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z">
                                            </path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                            </path>
                                        </svg>
                                        Video
                                    </span>
                                <?php else: ?>
                                    <span class="course-tag bg-yellow-100 text-yellow-800 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        Document
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                                <p class="course-description">
                                    <?php echo htmlspecialchars(substr($course['description'] ?? '', 0, 100)) . '...'; ?>
                                </p>
                                <?php if(!empty($course['tags'])): ?>
                                    <div class="flex flex-wrap gap-2 mt-3">
                                        <?php 
                                        $tags = is_array($course['tags']) ? $course['tags'] : explode(',', $course['tags']);
                                        foreach($tags as $tag): 
                                            $tag = trim($tag);
                                            if(empty($tag)) continue;
                                        ?>
                                            <span class="course-tag bg-purple-100 text-purple-800">
                                                <?php echo htmlspecialchars($tag); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="course-actions">
                                <?php if(in_array($course['id'], $enrolledCourseIds)): ?>
                                    <a href="view_course.php?id=<?php echo $course['id']; ?>" 
                                       class="action-button text-blue-600" title="Watch Content">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                                            </path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </a>
                                <?php else: ?>
                                    <a href="enroll_course.php?id=<?php echo $course['id']; ?>" 
                                       class="action-button text-blue-600" title="Enroll to Access">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                                            </path>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="course-meta mt-4">
                            <span>Created: <?php echo date('M d, Y', strtotime($course['created_at'] ?? 'now')); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>
