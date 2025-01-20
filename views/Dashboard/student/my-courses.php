<?php
session_start();
require_once '../../../models/Student.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: /Youdemy/views/auth/login.php');
    exit;
}

$student = new Student();
$student->id = $_SESSION['user_id'];
$enrolledCourses = $student->getCourses();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../../assets/css/Dashboard.css">
</head>
<body class="bg-gray-100">
    <?php include '../../components/navbar.php'; ?>

    <div class="flex">
        <?php include '../../components/sidebar.php'; ?>

        <div class="flex-1 p-8">
            <h1 class="text-2xl font-bold mb-6">My Courses</h1>

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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($enrolledCourses)): ?>
                    <div class="col-span-3">
                        <p class="text-gray-500 text-center">You are not enrolled in any courses yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($enrolledCourses as $course): ?>
                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                            <div class="p-6">
                                <div class="flex items-center gap-2 mb-2">
                                    <?php if ($course['type'] === 'video'): ?>
                                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                        </svg>
                                    <?php else: ?>
                                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                        </svg>
                                    <?php endif; ?>
                                    <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($course['title']); ?></h3>
                                </div>
                                <p class="text-sm mb-2">zz</p>
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    <span class="text-gray-600 text-sm">Category: <?php echo htmlspecialchars($course['category_name']); ?></span>
                                </div>
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-600 rounded-lg">
                                        <?php echo ucfirst($course['type']); ?>
                                    </span>
                                    <?php if (!empty($course['tags'])): ?>
                                        <?php foreach (explode(',', $course['tags']) as $tag): ?>
                                            <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-lg">
                                                <?php echo htmlspecialchars(trim($tag)); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <?php if ($course['type'] === 'video'): ?>
                                    <?php if (!empty($course['url'])): ?>
                                        <a href="<?php echo htmlspecialchars($course['url']); ?>" 
                                           target="_blank"
                                           class="w-full flex items-center justify-center px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
                                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.894l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                            </svg>
                                            Watch Video
                                        </a>
                                    <?php else: ?>
                                        <button disabled class="w-full flex items-center justify-center px-4 py-2 bg-gray-400 text-white rounded-lg cursor-not-allowed">
                                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.894l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                            </svg>
                                            Video Not Available
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if (!empty($course['document_path'])): ?>
                                        <a href="<?php echo htmlspecialchars($course['document_path']); ?>"
                                           download
                                           class="w-full flex items-center justify-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                            </svg>
                                            Download Document
                                        </a>
                                    <?php else: ?>
                                        <button disabled class="w-full flex items-center justify-center px-4 py-2 bg-gray-400 text-white rounded-lg cursor-not-allowed">
                                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                            </svg>
                                            Document Not Available
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <div class="flex items-center justify-between mt-4 text-sm text-gray-500">
                                    <span>Created: <?php echo date('M d, Y', strtotime($course['created_at'])); ?></span>
                                    <?php if ($course['status'] === 'draft'): ?>
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Draft</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
