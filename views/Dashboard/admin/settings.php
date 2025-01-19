<?php
session_start();

require_once '../../../models/Admin.php';
require_once '../../../controllers/TagCategoryController.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$admin = new Admin();
$controller = new TagCategoryController();
$controller->handleRequest();

$userName = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if(isset($_SESSION['success_message'])): ?>
    <meta name="success_message" content="<?= htmlspecialchars($_SESSION['success_message']) ?>">
    <?php unset($_SESSION['success_message']); endif; ?>
    <?php if(isset($_SESSION['error_message'])): ?>
    <meta name="error_message" content="<?= htmlspecialchars($_SESSION['error_message']) ?>">
    <?php unset($_SESSION['error_message']); endif; ?>
    <title>Settings - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css/Dashboard.css">
    <style>
        @media (max-width: 768px) {
            .table-responsive {
                display: block;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
<?php include '../../components/navbar.php'; ?>

    <div class="flex flex-col md:flex-row min-h-screen">
        <?php include '../../components/sidebar.php'; ?>

        <main class="w-full md:flex-1 p-4 md:p-6">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tags Management -->
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-4 md:p-6 border-b border-gray-200">
                            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                                <h2 class="text-xl font-semibold text-gray-900">Tags Management</h2>
                                <button onclick="openAddTagModal()" 
                                    class="w-full md:w-auto px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Add New Tag
                                </button>
                            </div>
                        </div>
                        
                        <div class="p-4 md:p-6">
                            <div class="table-responsive">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 md:px-6 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th class="px-3 py-2 md:px-6 md:py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php 
                                        require_once '../../../config/Database.php';
                                        $db = new Database();
                                        $sql = "SELECT * FROM tags ORDER BY name";
                                        $tags = $db->query($sql)->fetchAll();
                                        foreach($tags as $tag): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?= htmlspecialchars($tag['name']) ?></div>
                                            </td>
                                            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button onclick="editTag(<?= $tag['id'] ?>, '<?= htmlspecialchars($tag['name']) ?>')" 
                                                    class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
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
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-4 md:p-6 border-b border-gray-200">
                            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                                <h2 class="text-xl font-semibold text-gray-900">Categories Management</h2>
                                <button onclick="openAddCategoryModal()" 
                                    class="w-full md:w-auto px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Add New Category
                                </button>
                            </div>
                        </div>
                        
                        <div class="p-4 md:p-6">
                            <div class="table-responsive">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 md:px-6 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th class="hidden md:table-cell px-3 py-2 md:px-6 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-3 py-2 md:px-6 md:py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php 
                                        $sql = "SELECT * FROM categories ORDER BY name";
                                        $categories = $db->query($sql)->fetchAll();
                                        foreach($categories as $category): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?= htmlspecialchars($category['name']) ?></div>
                                                <div class="text-xs text-gray-500 md:hidden"><?= htmlspecialchars($category['description']) ?></div>
                                            </td>
                                            <td class="hidden md:table-cell px-3 py-2 md:px-6 md:py-4">
                                                <div class="text-sm text-gray-900"><?= htmlspecialchars($category['description']) ?></div>
                                            </td>
                                            <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button onclick="editCategory(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name']) ?>', '<?= htmlspecialchars($category['description']) ?>')" 
                                                    class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
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

    <script src="../../../assets/js/notifications.js"></script>
    <script src="../../../assets/js/tagManagement.js"></script>
    <script src="../../../assets/js/categoryManagement.js"></script>
</body>
</html>
