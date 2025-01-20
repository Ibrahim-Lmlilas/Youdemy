<?php
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
?>

<!-- Top Navigation -->
<nav class="bg-white shadow-lg">
    <div class="max-w-full mx-auto px-4">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <img class="h-8 w-auto" src="/Youdemy/assets/img/C.jpg" alt="Logo">
                </div>
                
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($user_name); ?></span>
                
                <a href="/Youdemy/views/auth/logout.php" class="logout-btn bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-all duration-200">Logout</a>
            </div>
        </div>
    </div>
</nav>
