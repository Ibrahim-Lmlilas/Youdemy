<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$userRole = $_SESSION['user_role'] ?? '';

$navItems = [];

if ($userRole === 'student') {
    $navItems = [
        ['path' => '/Youdemy/views/Dashboard/student/dashboard.php', 'name' => 'Browse Courses', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        ['path' => '/Youdemy/views/Dashboard/student/my-courses.php', 'name' => 'My Courses', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
    ];
} elseif ($userRole === 'teacher') {
    $navItems = [
        ['path' => '/Youdemy/views/Dashboard/teacher/dashboard.php', 'name' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['path' => '/Youdemy/views/Dashboard/teacher/add-course.php', 'name' => 'Create Course', 'icon' => 'M12 4v16m8-8H4'],
    ];
} elseif ($userRole === 'admin') {
    $navItems = [
        ['path' => '/Youdemy/views/Dashboard/admin/dashboard.php', 'name' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['path' => '/Youdemy/views/Dashboard/admin/users.php', 'name' => 'Manage Users', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['path' => '/Youdemy/views/Dashboard/admin/teachers.php', 'name' => 'Teacher Approvals', 'icon' => 'M12 4v16m8-8H4'],
        ['path' => '/Youdemy/views/Dashboard/admin/courses.php', 'name' => 'Manage Courses', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
        ['path' => '/Youdemy/views/Dashboard/admin/settings.php', 'name' => 'Settings', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
    ];
}
?>

<link rel="stylesheet" href="/Youdemy/assets/css/sidebar.css">

<!-- Burger Menu Button -->
<button class="menu-toggle" onclick="toggleSidebar()">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
</button>

<!-- Sidebar -->
 
<div class="sidebar " >
    <div class="nav-links ">
        <?php foreach ($navItems as $item): ?>
            <a href="<?php echo $item['path']; ?>" 
               class="nav-link <?php echo basename($item['path']) === $currentPage ? 'active' : ''; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $item['icon']; ?>"/>
                </svg>
                <?php echo $item['name']; ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Overlay for mobile -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const menuToggle = document.querySelector('.menu-toggle');
    
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
    menuToggle.classList.toggle('open');
}

// Close sidebar when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const menuToggle = document.querySelector('.menu-toggle');
    
    if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
        sidebar.classList.remove('open');
        document.querySelector('.sidebar-overlay').classList.remove('show');
        menuToggle.classList.remove('open');
    }
});

// Close sidebar when window is resized to desktop size
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        document.querySelector('.sidebar').classList.remove('open');
        document.querySelector('.sidebar-overlay').classList.remove('show');
        document.querySelector('.menu-toggle').classList.remove('open');
    }
});
</script>
