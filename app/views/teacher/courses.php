<?php
session_start();

// Check if user is logged in and is a teacher
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header('Location: ../auth/login.php');
    exit();
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../core/abstracts/Course.php';
require_once __DIR__ . '/../../models/TeacherCourse.php';

$userName = $_SESSION['user_name'];
$db = new Database();

// Get teacher's courses with categories and tags
$sql = "SELECT c.*, cat.name as category_name, 
        GROUP_CONCAT(t.name) as tags 
        FROM courses c 
        LEFT JOIN categories cat ON c.category_id = cat.id 
        LEFT JOIN course_tags ct ON c.id = ct.course_id 
        LEFT JOIN tags t ON ct.tag_id = t.id 
        WHERE c.teacher_id = ? 
        GROUP BY c.id";
$stmt = $db->query($sql, [$_SESSION['user_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for the form
$stmt = $db->query("SELECT id, name FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get tags for the form
$stmt = $db->query("SELECT id, name FROM tags");
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch($action) {
            case 'add':
                // Get form data
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $type = trim($_POST['type'] ?? '');
                $category_id = (int)($_POST['category'] ?? 0);
                $tags = $_POST['tags'] ?? [];

                // Validate type
                if (!in_array($type, ['video', 'document'])) {
                    throw new Exception("Invalid course type");
                }

                // Initialize content_url
                $content_url = '';

                // Handle content based on type
                if ($type === 'video') {
                    $content_url = filter_input(INPUT_POST, 'video_url', FILTER_SANITIZE_URL);
                    if (!$content_url) {
                        throw new Exception("Invalid video URL");
                    }
                } else {
                    // Handle document upload
                    if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                        throw new Exception("Document upload failed");
                    }

                    $file = $_FILES['document'];
                    $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
                    
                    if (!in_array($file['type'], $allowed_types)) {
                        throw new Exception("Invalid file type. Only PDF, Word, and PowerPoint documents are allowed.");
                    }

                    $uploadDir = __DIR__ . '/../../public/uploads/documents/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $fileName = uniqid() . '_' . basename($file['name']);
                    $targetPath = $uploadDir . $fileName;

                    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                        throw new Exception("Failed to upload document");
                    }

                    $content_url = '/uploads/documents/' . $fileName;
                }

                // Create course
                $sql = "INSERT INTO courses (title, description, type, content_url, category_id, teacher_id) VALUES (?, ?, ?, ?, ?, ?)";
                $db->query($sql, [$title, $description, $type, $content_url, $category_id, $_SESSION['user_id']]);
                $course_id = $db->lastInsertId();

                // Add tags
                if (!empty($tags)) {
                    foreach ($tags as $tag_id) {
                        $sql = "INSERT INTO course_tags (course_id, tag_id) VALUES (?, ?)";
                        $db->query($sql, [$course_id, $tag_id]);
                    }
                }

                $_SESSION['success_message'] = 'Course added successfully!';
                break;

            case 'update':
                $course_id = (int)($_POST['course_id'] ?? 0);
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $category_id = (int)($_POST['category'] ?? 0);
                $tags = $_POST['tags'] ?? [];

                // Update course
                $sql = "UPDATE courses SET title = ?, description = ?, category_id = ? WHERE id = ? AND teacher_id = ?";
                $db->query($sql, [$title, $description, $category_id, $course_id, $_SESSION['user_id']]);

                // Handle file upload if new file
                if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                    // Get course type
                    $sql = "SELECT type FROM courses WHERE id = ?";
                    $type = $db->query($sql, [$course_id])->fetch()['type'];

                    $file = $_FILES['document'];
                    $uploadDir = __DIR__ . '/../../public/uploads/' . $type . 's/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $fileName = uniqid() . '_' . basename($file['name']);
                    $targetPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $sql = "UPDATE courses SET content_url = ? WHERE id = ?";
                        $db->query($sql, ['/uploads/' . $type . 's/' . $fileName, $course_id]);
                    }
                }

                // Update tags
                $sql = "DELETE FROM course_tags WHERE course_id = ?";
                $db->query($sql, [$course_id]);
                
                if (!empty($tags)) {
                    foreach ($tags as $tag_id) {
                        $sql = "INSERT INTO course_tags (course_id, tag_id) VALUES (?, ?)";
                        $db->query($sql, [$course_id, $tag_id]);
                    }
                }

                $_SESSION['success_message'] = 'Course updated successfully!';
                break;

            case 'delete':
                $course_id = (int)($_POST['course_id'] ?? 0);
                
                // Delete course tags
                $sql = "DELETE FROM course_tags WHERE course_id = ?";
                $db->query($sql, [$course_id]);
                
                // Delete course
                $sql = "DELETE FROM courses WHERE id = ? AND teacher_id = ?";
                $db->query($sql, [$course_id, $_SESSION['user_id']]);
                
                $_SESSION['success_message'] = 'Course deleted successfully!';
                break;
        }
        
        header('Location: courses.php');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        nav.bg-white {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
        }
        .course-card {
            background-color: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
        }

        .course-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .course-card h3 {
            color: #1a1a1a;
            margin-bottom: 0.5rem;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .course-card .text-gray-500 {
            color: #6b7280;
        }

        .course-card .text-gray-600 {
            color: #4b5563;
        }

        .course-card button {
            padding: 0.5rem;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
        }

        .course-card button:hover {
            transform: scale(1.05);
        }

        .course-card .text-blue-600 {
            color: #2563eb;
        }

        .course-card .text-red-600 {
            color: #dc2626;
        }

        .course-card .text-blue-600:hover {
            color: #1d4ed8;
        }

        .course-card .text-red-600:hover {
            color: #b91c1c;
        }

        .course-card a {
            text-decoration: none;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            transition: background-color 0.2s ease;
        }

        .course-card a:hover {
            background-color: rgba(37, 99, 235, 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            color: white;
            font-weight: 500;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 50;
        }

        .modal-content {
            background-color: white;
            border-radius: 0.5rem;
            width: 80%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modal-header {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            background-color: #f8fafc;
        }

        .modal-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
        }

        .modal-body {
            padding: 1rem;
        }

        .modal-footer {
            padding: 1rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            background-color: #f8fafc;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.625rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        textarea.form-input {
            min-height: 100px;
            resize: vertical;
        }

        .btn-primary {
            background-color: #2563eb;
            color: white;
            padding: 0.625rem 1.25rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-secondary {
            background-color: #9ca3af;
            color: white;
            padding: 0.625rem 1.25rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            background-color: #6b7280;
        }

        .form-radio {
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            border: 2px solid #d1d5db;
            transition: all 0.2s;
            cursor: pointer;
        }

        .form-radio:checked {
            border-color: #2563eb;
            background-color: #2563eb;
        }

        .select2-container--classic .select2-selection--multiple {
            border: 1px solid #d1d5db !important;
            border-radius: 0.375rem !important;
            padding: 0.25rem !important;
        }

        .select2-container--classic .select2-selection--multiple:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
        }

        .select2-container--classic .select2-selection--multiple .select2-selection__choice {
            background-color: #e5e7eb !important;
            border: none !important;
            border-radius: 0.25rem !important;
            padding: 0.25rem 0.5rem !important;
            margin: 0.25rem !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
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
                    <a href="../auth/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 logout-btn">Logout</a>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard
                </a>
                <a href="courses.php" class="sidebar-link active">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    My Courses
                </a>
                <a href="students.php" class="sidebar-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    My Students
                </a>
                <a href="analytics.php" class="sidebar-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0h2a2 2 0 012 2v3a2 2 0 01-2 2h-2a2 2 0 01-2-2v-3a2 2 0 012-2zm-1-1h2v1h-2v-1zM3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Analytics
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">My Courses</h1>
                <div>
                    <button onclick="openModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add New Course
                    </button>
                </div>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($courses as $course): ?>
                <div class="course-card bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <!-- Icon based on course type -->
                            <div class="flex items-center gap-3 mb-2">
                                <?php if($course['type'] === 'video'): ?>
                                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                <?php else: ?>
                                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                <?php endif; ?>
                                <h3 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($course['title']) ?></h3>
                            </div>
                            <p class="text-gray-600 mb-4"><?= htmlspecialchars($course['description']) ?></p>
                            
                            <!-- Category and Tags -->
                            <div class="mb-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700">
                                        Category: <span class="text-blue-600"><?= htmlspecialchars($course['category_name']) ?></span>
                                    </span>
                                </div>
                                <?php if(!empty($course['tags'])): ?>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach(explode(',', $course['tags']) as $tag): ?>
                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">
                                            <?= htmlspecialchars(trim($tag)) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Watch/Download Button -->
                            <?php if($course['type'] === 'video'): ?>
                                <a href="<?= htmlspecialchars($course['content_url']) ?>" target="_blank" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M14.752 11.168l-3.197-2.132A1 1 0 0110 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Watch Video
                                </a>
                            <?php else: ?>
                                <a href="download.php?file=<?= basename($course['content_url']) ?>" 
                                   class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download Document
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Course Actions -->
                        <div class="flex space-x-2">
                            <button onclick="editCourse(<?= $course['id'] ?>)" 
                                class="text-blue-600 hover:text-blue-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <form action="delete_course.php" method="POST" class="inline" 
                                onsubmit="return confirm('Are you sure you want to delete this course?');">
                                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between text-sm text-gray-500">
                        <div>Created: <?= date('M j, Y', strtotime($course['created_at'])) ?></div>
                        <div class="flex items-center">
                            <span class="px-2 py-1 rounded-full text-xs <?= 
                                $course['status'] === 'published' ? 'bg-green-100 text-green-800' : 
                                ($course['status'] === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') 
                            ?>">
                                <?= ucfirst($course['status']) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Add Course Modal -->
    <div id="courseModal" class="hidden fixed inset-0 bg-black bg-opacity-50 modal-overlay flex items-center justify-center p-4 z-50">
        <div class="modal-content bg-white rounded-lg shadow-xl max-w-2xl w-full">
            <div class="modal-header flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Add New Course</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="modal-body p-4">
                <form id="courseForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="course_id" name="course_id">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="space-y-4">
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700" for="title">Course Title</label>
                            <input type="text" id="title" name="title" required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700" for="description">Description</label>
                            <textarea id="description" name="description" rows="4" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700" for="type">Course Type</label>
                            <select id="type" name="type" required onchange="handleTypeChange()"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select type</option>
                                <option value="video">Video Course</option>
                                <option value="document">Document Course</option>
                            </select>
                        </div>

                        <!-- Video URL input (for video courses) -->
                        <div id="videoInput" class="form-group hidden">
                            <label class="block text-sm font-medium text-gray-700" for="video_url">Video URL</label>
                            <input type="url" id="video_url" name="video_url" placeholder="Enter YouTube or Vimeo URL"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-sm text-gray-500">
                                Enter a valid YouTube or Vimeo video URL
                            </p>
                        </div>

                        <!-- Document upload input (for document courses) -->
                        <div id="documentInput" class="form-group hidden">
                            <label class="block text-sm font-medium text-gray-700" for="document">Course Document</label>
                            <input type="file" id="document" name="document" accept=".pdf,.doc,.docx,.ppt,.pptx"
                                class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100">
                            <p class="mt-1 text-sm text-gray-500">
                                Accepted formats: PDF, Word documents (.doc, .docx), PowerPoint (.ppt, .pptx)
                            </p>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700" for="category">Category</label>
                            <select id="category" name="category"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select a category</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700" for="tags">Tags</label>
                            <select id="tags" name="tags[]" multiple 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <?php foreach($tags as $tag): ?>
                                    <option value="<?= $tag['id'] ?>"><?= htmlspecialchars($tag['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700">
                            Save Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('courseForm').reset();
            document.getElementById('videoInput').classList.add('hidden');
            document.getElementById('documentInput').classList.add('hidden');
            document.getElementById('courseModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('courseModal').classList.add('hidden');
            document.getElementById('courseForm').reset();
        }

        function handleTypeChange() {
            const type = document.getElementById('type').value;
            const videoInput = document.getElementById('videoInput');
            const documentInput = document.getElementById('documentInput');
            
            // Hide both inputs first
            videoInput.classList.add('hidden');
            documentInput.classList.add('hidden');
            
            // Show the relevant input based on type
            if (type === 'video') {
                videoInput.classList.remove('hidden');
                document.getElementById('video_url').required = true;
                document.getElementById('document').required = false;
            } else if (type === 'document') {
                documentInput.classList.remove('hidden');
                document.getElementById('document').required = true;
                document.getElementById('video_url').required = false;
            }
        }

        async function editCourse(courseId) {
            // Get course data
            const response = await fetch(`get_course.php?id=${courseId}`);
            const course = await response.json();
            
            // Set form values
            document.getElementById('course_id').value = course.id;
            document.getElementById('title').value = course.title;
            document.getElementById('description').value = course.description;
            document.getElementById('type').value = course.type;
            document.getElementById('category').value = course.category_id || '';
            
            // Set tags
            const tagSelect = document.getElementById('tags');
            for (let option of tagSelect.options) {
                option.selected = course.tags.includes(parseInt(option.value));
            }
            
            // Set action to update
            document.querySelector('input[name="action"]').value = 'update';
            
            // Show modal
            document.getElementById('courseModal').classList.remove('hidden');
        }

        async function deleteCourse(courseId) {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            });

            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="course_id" value="${courseId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Initialize Select2 for tags
        $(document).ready(function() {
            $('#tags').select2({
                placeholder: 'Select tags',
                allowClear: true
            });
        });
    </script>
    <?php if(isset($_SESSION['success_message'])): ?>
        <script>
            Swal.fire({
                title: 'Success!',
                text: '<?php echo $_SESSION['success_message']; ?>',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error_message'])): ?>
        <script>
            Swal.fire({
                title: 'Error!',
                text: '<?php echo $_SESSION['error_message']; ?>',
                icon: 'error'
            });
        </script>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
</body>
</html>
