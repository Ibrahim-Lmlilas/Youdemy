<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once __DIR__ . '/../../config/Database.php';

$db = new Database();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch($action) {
            case 'addTag':
                $name = trim($_POST['name'] ?? '');
                if (!empty($name)) {
                    $sql = "INSERT INTO tags (name) VALUES (?)";
                    $success = $db->query($sql, [$name]);
                    $_SESSION['success_message'] = 'Tag added successfully!';
                }
                break;

            case 'updateTag':
                $id = (int)($_POST['id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                if ($id > 0 && !empty($name)) {
                    $sql = "UPDATE tags SET name = ? WHERE id = ?";
                    $success = $db->query($sql, [$name, $id]);
                    $_SESSION['success_message'] = 'Tag updated successfully!';
                }
                break;

            case 'deleteTag':
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    // Delete tag associations first
                    $sql = "DELETE FROM course_tags WHERE tag_id = ?";
                    $db->query($sql, [$id]);
                    
                    // Then delete the tag
                    $sql = "DELETE FROM tags WHERE id = ?";
                    $success = $db->query($sql, [$id]);
                    $_SESSION['success_message'] = 'Tag deleted successfully!';
                }
                break;

            case 'addCategory':
                $name = trim($_POST['name'] ?? '');
                if (!empty($name)) {
                    $sql = "INSERT INTO categories (name) VALUES (?)";
                    $success = $db->query($sql, [$name]);
                    $_SESSION['success_message'] = 'Category added successfully!';
                }
                break;

            case 'updateCategory':
                $id = (int)($_POST['id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                if ($id > 0 && !empty($name)) {
                    $sql = "UPDATE categories SET name = ? WHERE id = ?";
                    $success = $db->query($sql, [$name, $id]);
                    $_SESSION['success_message'] = 'Category updated successfully!';
                }
                break;

            case 'deleteCategory':
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    // First check if category is used in any courses
                    $sql = "SELECT COUNT(*) as count FROM courses WHERE category_id = ?";
                    $result = $db->query($sql, [$id])->fetch();
                    
                    if ($result['count'] > 0) {
                        $_SESSION['error_message'] = 'Cannot delete category because it is used by courses. Please reassign or delete those courses first.';
                        header('Location: settings.php');
                        exit;
                    }
                    
                    // If not used, delete the category
                    $sql = "DELETE FROM categories WHERE id = ?";
                    $success = $db->query($sql, [$id]);
                    $_SESSION['success_message'] = 'Category deleted successfully!';
                }
                break;
        }
        
        header('Location: settings.php');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}

// Get tags and categories
$sql = "SELECT * FROM tags ORDER BY name";
$tags = $db->query($sql)->fetchAll();

$sql = "SELECT * FROM categories ORDER BY name";
$categories = $db->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Dashboard - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            background:rgb(0, 53, 114);
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
            0% {
                transform: translate(0, 0) rotate(0deg);
            }
            50% {
                transform: translate(30px, 30px) rotate(5deg);
            }
            100% {
                transform: translate(0, 0) rotate(0deg);
            }
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
        .content-area {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-full mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900">Youdemy Admin</h1>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-700 mr-4"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></span>
                    <a href="../auth/logout.php" class="text-red-600 hover:text-red-800">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <aside class="sidebar w-64 flex-shrink-0">
            <nav class="mt-5 px-2">
                <a href="dashboard.php" class="sidebar-link">Dashboard</a>
                <a href="users.php" class="sidebar-link">Users</a>
                <a href="teacher_approvals.php" class="sidebar-link">Teacher Approvals</a>
                <a href="settings.php" class="sidebar-link active">Settings</a>
            </nav>
        </aside>

        <!-- Main content -->
        <main class="flex-1 p-8">
            <div class="content-area p-6">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Tags Management -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold text-gray-900">Tags Management</h2>
                            <button onclick="openAddTagModal()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                Add Tag
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach($tags as $tag): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($tag['name']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button onclick="openEditTagModal(<?= $tag['id'] ?>, '<?= htmlspecialchars($tag['name']) ?>')" 
                                                class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</button>
                                            <button onclick="deleteTag(<?= $tag['id'] ?>)" 
                                                class="text-red-600 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Categories Management -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold text-gray-900">Categories Management</h2>
                            <button onclick="openAddCategoryModal()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                Add Category
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach($categories as $category): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($category['name']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button onclick="openEditCategoryModal(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name']) ?>')" 
                                                class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</button>
                                            <button onclick="deleteCategory(<?= $category['id'] ?>)" 
                                                class="text-red-600 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Tag Modals -->
    <div id="addTagModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Add New Tag</h3>
                <button onclick="closeAddTagModal()" class="text-gray-400 hover:text-gray-500">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="addTag">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Tag Name</label>
                    <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Add Tag</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editTagModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Edit Tag</h3>
                <button onclick="closeEditTagModal()" class="text-gray-400 hover:text-gray-500">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="updateTag">
                <input type="hidden" name="id" id="editTagId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Tag Name</label>
                    <input type="text" name="name" id="editTagName" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Update Tag</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Category Modals -->
    <div id="addCategoryModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Add New Category</h3>
                <button onclick="closeAddCategoryModal()" class="text-gray-400 hover:text-gray-500">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="addCategory">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Category Name</label>
                    <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Add Category</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editCategoryModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Edit Category</h3>
                <button onclick="closeEditCategoryModal()" class="text-gray-400 hover:text-gray-500">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="updateCategory">
                <input type="hidden" name="id" id="editCategoryId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Category Name</label>
                    <input type="text" name="name" id="editCategoryName" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Update Category</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Tag Functions
        function openAddTagModal() {
            document.getElementById('addTagModal').classList.remove('hidden');
        }

        function closeAddTagModal() {
            document.getElementById('addTagModal').classList.add('hidden');
        }

        function openEditTagModal(id, name) {
            document.getElementById('editTagId').value = id;
            document.getElementById('editTagName').value = name;
            document.getElementById('editTagModal').classList.remove('hidden');
        }

        function closeEditTagModal() {
            document.getElementById('editTagModal').classList.add('hidden');
        }

        function deleteTag(id) {
            if (confirm('Are you sure you want to delete this tag?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="deleteTag">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Category Functions
        function openAddCategoryModal() {
            document.getElementById('addCategoryModal').classList.remove('hidden');
        }

        function closeAddCategoryModal() {
            document.getElementById('addCategoryModal').classList.add('hidden');
        }

        function openEditCategoryModal(id, name) {
            document.getElementById('editCategoryId').value = id;
            document.getElementById('editCategoryName').value = name;
            document.getElementById('editCategoryModal').classList.remove('hidden');
        }

        function closeEditCategoryModal() {
            document.getElementById('editCategoryModal').classList.add('hidden');
        }

        function deleteCategory(id) {
            if (confirm('Are you sure you want to delete this category?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="deleteCategory">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Show success/error messages
        <?php if(isset($_SESSION['success_message'])): ?>
            Swal.fire({
                title: 'Success!',
                text: '<?= $_SESSION['success_message'] ?>',
                icon: 'success'
            });
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['error_message'])): ?>
            Swal.fire({
                title: 'Error!',
                text: '<?= $_SESSION['error_message'] ?>',
                icon: 'error'
            });
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
