<?php
session_start();
require_once '../../../models/Teacher.php';
require_once '../../../config/Database.php';
require_once '../../../models/Course.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header('Location: /Youdemy/views/auth/login.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$course = new Course();
$categories = $course->getAllCategories();
$tags = $course->getAllTags();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Course - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="../../../assets/css/Dashboard.css">
    <style>
        .select2-container--default .select2-selection--multiple {
            border-color: #D1D5DB;
            border-radius: 0.375rem;
            min-height: 42px;
            padding: 2px 8px;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #3B82F6;
            outline: none;
            box-shadow: 0 0 0 1px #3B82F6;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #EFF6FF;
            border: 1px solid #3B82F6;
            color: #1D4ED8;
            border-radius: 0.375rem;
            padding: 2px 8px;
            margin: 3px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #1D4ED8;
            margin-right: 5px;
        }
        .select2-container--default .select2-search--inline .select2-search__field {
            margin-top: 3px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include '../../components/navbar.php'; ?>

    <div class="flex">
        <?php include '../../components/sidebar.php'; ?>

        <div class="flex-1 p-4">
            <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
                <h1 class="text-xl font-semibold mb-6">Add New Course</h1>

                <form action="/Youdemy/controllers/teacher/add-course.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Course Title</label>
                        <input type="text" id="title" name="title" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select id="category" name="category_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                        <select id="tags" name="tags[]" multiple class="w-full">
                            <?php foreach ($tags as $tag): ?>
                                <option value="<?php echo $tag['id']; ?>">
                                    <?php echo htmlspecialchars($tag['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Course Type</label>
                        <select id="type" name="type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Select type</option>
                            <option value="video">Video</option>
                            <option value="document">Document</option>
                        </select>
                    </div>

                    <div id="urlField" class="hidden">
                        <label for="url" class="block text-sm font-medium text-gray-700 mb-1">Video URL</label>
                        <input type="url" id="url" name="url"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Enter YouTube or video URL">
                    </div>

                    <div id="documentField" class="hidden">
                        <label for="document" class="block text-sm font-medium text-gray-700 mb-1">Course Document</label>
                        <input type="file" id="document" name="document"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" name="description" rows="4" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <a href="dashboard.php" 
                            class="px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-4 py-2 text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700">
                            Create Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#tags').select2({
                placeholder: 'Select tags...',
                allowClear: true,
                width: '100%'
            });
        });

        document.getElementById('type').addEventListener('change', function() {
            const urlField = document.getElementById('urlField');
            const documentField = document.getElementById('documentField');
            
            if (this.value === 'video') {
                urlField.classList.remove('hidden');
                documentField.classList.add('hidden');
            } else if (this.value === 'document') {
                urlField.classList.add('hidden');
                documentField.classList.remove('hidden');
            } else {
                urlField.classList.add('hidden');
                documentField.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
