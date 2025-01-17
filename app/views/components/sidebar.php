<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$userRole = $_SESSION['user_role'] ?? '';

$navItems = [];

if ($userRole === 'student') {
    $navItems = [
        ['path' => '/Youdemy/app/views/student/dashboard.php', 'name' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['path' => '/Youdemy/app/views/student/browse_courses.php', 'name' => 'Browse Courses', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        ['path' => '/Youdemy/app/views/student/my_courses.php', 'name' => 'My Courses', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
    ];
} elseif ($userRole === 'teacher') {
    $navItems = [
        ['path' => '/Youdemy/app/views/teacher/dashboard.php', 'name' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['path' => '/Youdemy/app/views/teacher/courses.php', 'name' => 'Create Course', 'icon' => 'M12 4v16m8-8H4'],
    ];
} elseif ($userRole === 'admin') {
    $navItems = [
        ['path' => '/Youdemy/app/views/admin/dashboard.php', 'name' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['path' => '/Youdemy/app/views/admin/users.php', 'name' => 'Manage Users', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['path' => '/Youdemy/app/views/admin/teacher_approvals.php', 'name' => 'teacher approvals', 'icon' => 'M12 4v16m8-8H4'],
        ['path' => '/Youdemy/app/views/admin/courses.php', 'name' => 'Manage Courses', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
        ['path' => '/Youdemy/app/views/admin/settings.php', 'name' => 'Settings', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z']
    ];
}
?>

<aside class="bg-gray-800 text-white w-64 min-h-screen flex-shrink-0">
    <div class="p-4">
        <div class="flex items-center justify-center mb-8">
            <span class="text-2xl font-bold">Youdemy</span>
        </div>
        <nav>
            <?php foreach ($navItems as $item): ?>
                <?php
                $isActive = strpos($_SERVER['PHP_SELF'], $item['path']) !== false;
                $activeClass = $isActive ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white';
                ?>
                <a href="<?php echo $item['path']; ?>" 
                   class="flex items-center px-4 py-2 mt-2 text-sm font-semibold rounded-lg <?php echo $activeClass; ?> transition-colors duration-200">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
                         xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="<?php echo $item['icon']; ?>">
                        </path>
                    </svg>
                    <?php echo $item['name']; ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</aside>
