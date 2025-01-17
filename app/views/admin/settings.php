<?php
session_start();

require_once __DIR__ . '/../../models/Admin.php';

// Check if user is logged in and is an admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$admin = new Admin();
$userName = $_SESSION['user_name'] ?? 'Admin';
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
        .sidebar-link svg {
            width: 1.25rem;
            height: 1.25rem;
            margin-right: 0.75rem;
        }
        .content-area {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            position: relative;
            z-index: 1;
        }
        .table-wrapper {
            position: relative;
            max-height: 300px;
            overflow: hidden;
        }
        .table-scroll {
            overflow-y: auto;
            max-height: 300px;
        }
        .table-scroll::-webkit-scrollbar {
            width: 8px;
        }
        .table-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .table-scroll::-webkit-scrollbar-thumb {
            background-color: rgba(0, 53, 114, 0.5);
            border-radius: 20px;
            border: 2px solid transparent;
        }
        table thead {
            position: sticky;
            top: 0;
            background: #f9fafb;
            z-index: 1;
        }
        .user-table {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            overflow: hidden;
            max-height: calc(100vh - 13rem);
            display: flex;
            flex-direction: column;
        }
        nav.bg-white {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
        }
        .logout-btn {
            background: linear-gradient(135deg, #ff4b4b 0%, #ff9797 100%);
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 75, 75, 0.4);
        }
    </style>
</head>
<body>
<?php include '../components/navbar.php'; ?>


    <div class="flex">
    <?php include '../components/sidebar.php'; ?>

        <main class="flex-1 p-8">
            <div class="content-area p-6">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Tags Management -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold text-gray-900">Tags Management</h2>
                            <button onclick="openAddTagModal()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Add New Tag
                            </button>
                        </div>
                        <div class="table-wrapper">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                            <div class="table-scroll">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php 
                                        require_once __DIR__ . '/../../config/Database.php';
                                        $db = new Database();
                                        $sql = "SELECT * FROM tags ORDER BY name";
                                        $tags = $db->query($sql)->fetchAll();
                                        foreach($tags as $tag): ?>
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
                    </div>

                    <!-- Categories Management -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold text-gray-900">Categories Management</h2>
                            <button onclick="openAddCategoryModal()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Add New Category
                            </button>
                        </div>
                        <div class="table-wrapper">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                            <div class="table-scroll">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php 
                                        $sql = "SELECT * FROM categories ORDER BY name";
                                        $categories = $db->query($sql)->fetchAll();
                                        foreach($categories as $category): ?>
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
