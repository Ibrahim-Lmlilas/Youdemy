<?php
session_start();

require_once __DIR__ . '/../../models/Admin.php';

// Check if user is logged in and is an admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$admin = new Admin();
$courses = $admin->getAllCourses();
$userName = $_SESSION['user_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Courses Management</title>
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
        .status-select {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(209, 213, 219, 0.5);
            padding: 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .status-select:focus {
            outline: none;
            border-color: rgb(0, 53, 114);
            box-shadow: 0 0 0 2px rgba(0, 53, 114, 0.2);
        }
        .filter-select {
            appearance: none;
            background-color: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(209, 213, 219, 0.5);
            padding: 0.5rem 2rem 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            line-height: 1.25rem;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            transition: all 0.3s ease;
        }
        .filter-select:focus {
            outline: none;
            border-color: rgb(0, 53, 114);
            box-shadow: 0 0 0 2px rgba(0, 53, 114, 0.2);
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
        <!-- Sidebar -->
        <?php include '../components/sidebar.php'; ?>
        <main class="flex-1 p-8">
            <!-- Search and Filter Section -->
            <div class="content-area p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1 max-w-sm">
                        <input type="text" id="searchCourse" placeholder="Search courses..." 
                               class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2">
                    </div>
                    <div class="ml-4">
                        <select id="filterStatus" class="filter-select">
                            <option value="all">All Status</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Courses Table -->
            <div class="content-area">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">Courses Management</h2>
                    <div class="overflow-x-auto">
                        <div class="max-h-[600px] overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach($courses as $course): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($course['title']) ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900"><?= htmlspecialchars($course['teacher_name']) ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                <?= $course['category_name'] ? htmlspecialchars($course['category_name']) : 'No Category' ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php 
                                                    switch($course['status']) {
                                                        case 'published':
                                                            echo 'bg-green-100 text-green-800';
                                                            break;
                                                        case 'draft':
                                                            echo 'bg-yellow-100 text-yellow-800';
                                                            break;
                                                        case 'archived':
                                                            echo 'bg-gray-100 text-gray-800';
                                                            break;
                                                    }
                                                ?>">
                                                <?= ucfirst(htmlspecialchars($course['status'])) ?>
                                            </span>
                                            <button onclick="updateStatus(<?= $course['id'] ?>, '<?= $course['status'] ?>')" 
                                                class="ml-2 text-indigo-600 hover:text-indigo-900">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                <?= date('M d, Y', strtotime($course['created_at'])) ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-medium">
                                            <button onclick="confirmDelete(<?= $course['id'] ?>)" 
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Delete
                                            </button>
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

    <script>
        function confirmDelete(courseId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create and submit form
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

        function updateStatus(courseId, currentStatus) {
            let newStatus;
            switch(currentStatus) {
                case 'draft':
                    newStatus = 'published';
                    break;
                case 'published':
                    newStatus = 'archived';
                    break;
                case 'archived':
                    newStatus = 'draft';
                    break;
            }

            Swal.fire({
                title: 'Update Status',
                text: `Change status to ${newStatus}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4F46E5',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'update_course_status.php';
                    
                    const courseIdInput = document.createElement('input');
                    courseIdInput.type = 'hidden';
                    courseIdInput.name = 'course_id';
                    courseIdInput.value = courseId;
                    
                    const statusInput = document.createElement('input');
                    statusInput.type = 'hidden';
                    statusInput.name = 'status';
                    statusInput.value = newStatus;
                    
                    form.appendChild(courseIdInput);
                    form.appendChild(statusInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Search functionality
        document.getElementById('searchCourse').addEventListener('input', function(e) {
            const searchValue = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const title = row.querySelector('td:first-child').textContent.toLowerCase();
                const teacher = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const category = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                
                if (title.includes(searchValue) || teacher.includes(searchValue) || category.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Filter functionality
        document.getElementById('filterStatus').addEventListener('change', function(e) {
            const filterValue = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const status = row.querySelector('td:nth-child(4)').textContent.toLowerCase().trim();
                
                if (filterValue === 'all' || status === filterValue) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

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
