<?php
session_start();

// Check if user is logged in and is a teacher
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header('Location: ../auth/login.php');
    exit();
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Course.php';
require_once __DIR__ . '/../../models/VideoCourse.php';
require_once __DIR__ . '/../../models/DocumentCourse.php';

$userName = $_SESSION['user_name'];

// Get teacher's courses
$db = new Database();
$sql = "SELECT * FROM courses WHERE teacher_id = ? ORDER BY created_at DESC";
$courses = $db->query($sql, [$_SESSION['user_id']])->fetchAll();
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard
                </a>
                <a href="courses.php" class="sidebar-link active">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    My Courses
                </a>
                <a href="students.php" class="sidebar-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    My Students
                </a>
                <a href="analytics.php" class="sidebar-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v14a2 2 0 01-2 2h-3a2 2 0 01-2-2z"></path>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add New Course
                    </button>
                </div>
            </div>

            <!-- Course Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($courses as $course): ?>
                <div class="course-card">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p class="text-sm text-gray-500 mb-1">
                                <?php 
                                    $content_type = filter_var($course['content'], FILTER_VALIDATE_URL) ? 'Video' : 'Document';
                                    echo $content_type . ' Course';
                                ?>
                            </p>
                            <p class="text-gray-600 text-sm mb-3"><?php echo htmlspecialchars($course['description']); ?></p>
                        </div>
                        <div class="flex space-x-2 ml-4">
                            <button onclick="editCourse(<?php echo $course['id']; ?>)" class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button onclick="deleteCourse(<?php echo $course['id']; ?>)" class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex justify-between items-center text-sm border-t pt-3 mt-3">
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-500">Status: <span class="font-medium"><?php echo ucfirst($course['status']); ?></span></span>
                            <span class="text-gray-500">Created: <span class="font-medium"><?php echo date('M j, Y', strtotime($course['created_at'])); ?></span></span>
                        </div>
                        <div>
                            <?php if(filter_var($course['content'], FILTER_VALIDATE_URL)): ?>
                                <a href="<?php echo htmlspecialchars($course['content']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Watch Video
                                </a>
                            <?php else: ?>
                                <a href="/uploads/documents/<?php echo htmlspecialchars($course['content']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    View Document
                                </a>
                            <?php endif; ?>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="modal-body p-4">
                <form id="courseForm" method="POST" action="add_course.php" enctype="multipart/form-data">
                    <input type="hidden" id="course_id" name="course_id">
                    
                    <div class="space-y-4">
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700" for="title">Course Title</label>
                            <input type="text" id="title" name="title" required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter course title">
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700" for="description">Description</label>
                            <textarea id="description" name="description" required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                rows="3" placeholder="Enter course description"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700" for="category">Category</label>
                            <select id="category" name="category_id" required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select a category</option>
                                <?php
                                $stmt = $db->query("SELECT id, name FROM categories ORDER BY name");
                                while($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . $category['id'] . "'>" . htmlspecialchars($category['name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Content Type</label>
                            <div class="flex space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="content_type" value="video" class="form-radio text-blue-600" checked>
                                    <span class="ml-2">Video</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="content_type" value="document" class="form-radio text-blue-600">
                                    <span class="ml-2">Document</span>
                                </label>
                            </div>
                        </div>

                        <div id="video_content" class="form-group">
                            <label class="block text-sm font-medium text-gray-700" for="video_url">Video URL</label>
                            <input type="url" id="video_url" name="video_url" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter YouTube or Vimeo URL">
                            <p class="mt-1 text-sm text-gray-500">Paste the URL of your video from YouTube or Vimeo</p>
                        </div>

                        <div id="document_content" class="form-group hidden">
                            <label class="block text-sm font-medium text-gray-700" for="document">Document File</label>
                            <input type="file" id="document" name="document" 
                                class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100"
                                accept=".pdf,.doc,.docx,.ppt,.pptx">
                            <p class="mt-1 text-sm text-gray-500">Accepted formats: PDF, DOC, DOCX, PPT, PPTX</p>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700" for="tags">Tags</label>
                            <select id="tags" name="tags[]" multiple 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <?php
                                $stmt = $db->query("SELECT id, name FROM tags ORDER BY name");
                                while($tag = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . $tag['id'] . "'>" . htmlspecialchars($tag['name']) . "</option>";
                                }
                                ?>
                            </select>
                            <p class="mt-1 text-sm text-gray-500">Select multiple tags for your course</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Save Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editCourse(courseId) {
            // Get course data and populate modal
            $.ajax({
                url: 'get_course.php',
                type: 'POST',
                data: { course_id: courseId },
                dataType: 'json',
                success: function(course) {
                    // Reset form
                    $('#courseForm').trigger('reset');
                    
                    // Populate form fields
                    $('#course_id').val(course.id);
                    $('#title').val(course.title);
                    $('#description').val(course.description);
                    $('#category').val(course.category_id);
                    
                    // Set content type
                    const isVideo = /^https?:\/\//i.test(course.content);
                    $('input[name="content_type"][value="' + (isVideo ? 'video' : 'document') + '"]').prop('checked', true).trigger('change');
                    
                    // Show appropriate content field and set value
                    if (isVideo) {
                        $('#video_content').show();
                        $('#document_content').hide();
                        $('#video_url').val(course.content);
                    } else {
                        $('#video_content').hide();
                        $('#document_content').show();
                        // Can't set file input value for security reasons
                    }
                    
                    // Set tags
                    if (course.tag_ids) {
                        $('#tags').val(course.tag_ids.split(',')).trigger('change');
                    } else {
                        $('#tags').val(null).trigger('change');
                    }
                    
                    // Update form action
                    $('#courseForm').attr('action', 'edit_course.php');
                    
                    // Show modal
                    openModal();
                },
                error: function(xhr) {
                    const error = JSON.parse(xhr.responseText);
                    alert('Error: ' + error.message);
                }
            });
        }

        function deleteCourse(courseId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This course will be permanently deleted. This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'delete_course.php';
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'course_id';
                    input.value = courseId;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        $(document).ready(function() {
            // Initialize Select2 for tags
            $('#tags').select2({
                placeholder: 'Select tags',
                width: '100%'
            });

            // Handle content type change
            $('input[name="content_type"]').change(function() {
                if ($(this).val() === 'video') {
                    $('#video_content').show();
                    $('#document_content').hide();
                    $('#document').prop('required', false);
                    $('#video_url').prop('required', true);
                } else {
                    $('#video_content').hide();
                    $('#document_content').show();
                    $('#video_url').prop('required', false);
                    $('#document').prop('required', true);
                }
            });

            // Reset form when opening modal for new course
            $('#courseForm').on('reset', function() {
                $('#course_id').val('');
                $('#tags').val(null).trigger('change');
                $(this).attr('action', 'add_course.php');
                $('input[name="content_type"][value="video"]').prop('checked', true).trigger('change');
            });
        });

        function openModal() {
            document.getElementById('courseModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('courseModal').classList.add('hidden');
            document.getElementById('courseForm').reset();
        }
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
