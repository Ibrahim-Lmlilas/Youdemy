<?php
session_start();
require_once '../../../models/Teacher.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header('Location: /Youdemy/views/auth/login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$teacher = new Teacher();
$teacher->setId($_SESSION['user_id']);

$course = $teacher->getCourse($_GET['id']);
if (!$course) {
    header('Location: dashboard.php');
    exit;
}

$categories = $teacher->getCategories();
$tags = $teacher->getTags();

$courseTags = $teacher->getCourseTags($course['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../../assets/css/Dashboard.css">
</head>
<body class="bg-gray-100">
    <?php include '../../components/navbar.php'; ?>

    <div class="flex">
        <?php include '../../components/sidebar.php'; ?>

        <div class="flex-1 p-8">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-2xl font-bold mb-6">Edit Course</h1>

                <form id="editCourseForm" class="bg-white rounded-lg shadow-md p-6">
                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                            Course Title
                        </label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($course['title']); ?>" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="category">
                            Category
                        </label>
                        <select id="category" name="category_id" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $course['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="type">
                            Course Type
                        </label>
                        <select id="type" name="type" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
                            <option value="video" <?php echo $course['type'] === 'video' ? 'selected' : ''; ?>>Video</option>
                            <option value="document" <?php echo $course['type'] === 'document' ? 'selected' : ''; ?>>Document</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                            Description
                        </label>
                        <textarea id="description" name="description" rows="4" 
                                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required><?php echo htmlspecialchars($course['description']); ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Tags
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <?php foreach ($tags as $tag): ?>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>"
                                           <?php echo in_array($tag['id'], array_column($courseTags, 'tag_id')) ? 'checked' : ''; ?>>
                                    <span><?php echo htmlspecialchars($tag['name']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="status">
                            Status
                        </label>
                        <select id="status" name="status" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
                            <option value="draft" <?php echo $course['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $course['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>

                    <div class="mb-4" id="urlField" style="<?php echo $course['type'] === 'video' ? 'display:block' : 'display:none'; ?>">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="url">
                            Video URL
                        </label>
                        <input type="url" id="url" name="url" value="<?php echo htmlspecialchars($course['url'] ?? ''); ?>" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>

                    <div class="mb-4" id="documentField" style="<?php echo $course['type'] === 'document' ? 'display:block' : 'display:none'; ?>">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="document">
                            Document
                        </label>
                        <input type="file" id="document" name="document" accept=".pdf,.doc,.docx" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        <?php if ($course['document_path']): ?>
                            <p class="text-sm text-gray-600 mt-1">Current document: <?php echo basename($course['document_path']); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="dashboard.php" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none">
                            Update Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('type').addEventListener('change', function() {
            const urlField = document.getElementById('urlField');
            const documentField = document.getElementById('documentField');
            
            if (this.value === 'video') {
                urlField.style.display = 'block';
                documentField.style.display = 'none';
            } else {
                urlField.style.display = 'none';
                documentField.style.display = 'block';
            }
        });

        document.getElementById('editCourseForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('/Youdemy/controllers/teacher/edit-course.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert('Course updated successfully');
                    window.location.href = 'dashboard.php';
                } else {
                    alert(data.error || 'Failed to update course');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while updating the course');
            }
        });
    </script>
</body>
</html>
