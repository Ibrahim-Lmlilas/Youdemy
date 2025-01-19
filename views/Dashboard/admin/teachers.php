<?php
session_start();

require_once __DIR__ . '/../../../models/TeacherApprovals.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacherApprovals = new TeacherApprovals();
    
    if (isset($_POST['approve'])) {
        $teacherApprovals->approveTeacher($_POST['teacher_id']);
    } 
    
    elseif (isset($_POST['reject'])) {
        $teacherApprovals->rejectTeacher($_POST['teacher_id']);
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

$teacherApprovals = new TeacherApprovals();
$pendingTeachers = $teacherApprovals->getPendingTeachers();
$userName = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Approvals - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css/Dashboard.css">
    <link rel="stylesheet" href="../../../assets/css/teachers.css">
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
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-4 md:p-6 border-b border-gray-200">
                    <h2 class="text-xl md:text-2xl font-semibold text-gray-900">Teacher Approval Requests</h2>
                </div>

                <div class="p-4 md:p-6">
                    <div class="table-responsive">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-2 md:px-6 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                                    <th scope="col" class="hidden md:table-cell px-3 py-2 md:px-6 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-3 py-2 md:px-6 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-3 py-2 md:px-6 md:py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach($pendingTeachers as $teacher): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($teacher['name']); ?></div>
                                                <div class="text-xs text-gray-500 md:hidden"><?php echo htmlspecialchars($teacher['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="hidden md:table-cell px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($teacher['email']); ?></div>
                                    </td>
                                    <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 md:px-6 md:py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <form method="POST" class="inline-block">
                                            <input type="hidden" name="teacher_id" value="<?php echo $teacher['id']; ?>">
                                            <button type="submit" name="approve" 
                                                class="inline-flex items-center px-2 py-1 md:px-3 md:py-1.5 border border-transparent text-xs md:text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 mr-2">
                                                <svg class="hidden md:inline w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span>Approve</span>
                                            </button>
                                            <button type="submit" name="reject" 
                                                class="inline-flex items-center px-2 py-1 md:px-3 md:py-1.5 border border-transparent text-xs md:text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                                <svg class="hidden md:inline w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                <span>Reject</span>
                                            </button>
                                        </form>
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
</body>
</html>
