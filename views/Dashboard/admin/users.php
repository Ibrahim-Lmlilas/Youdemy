<?php
session_start();

require_once '../../../models/Admin.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$admin = new Admin();
$users = $admin->getAllUsers();
$userName = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css/Dashboard.css">
</head>
<body>
<?php include '../../components/navbar.php'; ?>


    <div class="flex">

    <?php include '../../components/sidebar.php'; ?>

        <main class="flex-1 p-8">
            <div class="content-area p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-900">Manage Users</h2>
                    <div class="flex space-x-4">

                        <input type="text"  id="searchInput"   placeholder="Search users..."  class="search-input px-4 py-2 rounded-lg focus:outline-none">


                    </div>
                </div>

                <div class="user-table">
                    <div class="table-container">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach($users as $user): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['name']) ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($user['email']) ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?= ucfirst(htmlspecialchars($user['role'])) ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php 
                                                switch($user['status']) {
                                                    case 'active':
                                                        echo 'bg-green-100 text-green-800';
                                                        break;
                                                    case 'pending':
                                                        echo 'bg-yellow-100 text-yellow-800';
                                                        break;
                                                    default:
                                                        echo 'bg-gray-100 text-gray-800';
                                                }
                                            ?>">
                                            <?= ucfirst(htmlspecialchars($user['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                                        <?php if($user['role'] !== 'admin'): ?>
                                            <?php if($user['status'] === 'active'): ?>
                                                <button onclick="confirmSuspend(<?= $user['id'] ?>)" 
                                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors duration-200">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                    </svg>
                                                    Suspend
                                                </button>
                                            <?php endif; ?>
                                            <?php if($user['status'] === 'suspended'): ?>
                                                <button onclick="confirmActivate(<?= $user['id'] ?>)" 
                                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Activate
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../../../assets/js/User.js"></script>
    <script src="../../../assets/js/searchInputUser.js"></script>
</body>
</html>
