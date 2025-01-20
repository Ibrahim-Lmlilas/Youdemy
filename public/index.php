<?php
require_once '../config/Database.php';
require_once '../models/Course.php';

$course = new Course();
$courses = $course->getAllPublishedCourses();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Youdemy - Online Learning Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes fadeInUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        .rotating-circle {
            animation: rotate 20s linear infinite;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease-out forwards;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        /* Responsive Design */
        @media (max-width: 640px) {
            .hero-title {
                font-size: 2rem;
            }
            .nav-brand-text {
                display: none;
            }
            .course-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Navigation -->
    <nav class="glass-effect shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="nav-brand-text ml-3 text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600">
                        Youdemy
                    </span>
                </div>
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <a href="../views/auth/login.php" 
                       class="text-gray-600 hover:text-gray-900 px-3 py-2 text-sm sm:text-base rounded-lg transition-all duration-300">
                        Login
                    </a>
                    <a href="../views/auth/register.php" 
                       class="gradient-bg text-white px-4 py-2 text-sm sm:text-base rounded-lg font-medium shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                        Register
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Floating Circle -->
    <div class="relative overflow-hidden">
        <!-- Rotating Circle -->
        <div class="absolute top-0 right-0 -mr-40 -mt-40">
            <div class="w-96 h-96 border-4 border-purple-500/30 rounded-full rotating-circle"></div>
            <div class="absolute top-1/2 left-1/2 w-80 h-80 border-4 border-blue-500/20 rounded-full rotating-circle" style="animation-direction: reverse;"></div>
            <div class="absolute top-1/2 left-1/2 w-64 h-64 border-4 border-indigo-500/10 rounded-full rotating-circle"></div>
        </div>

        <div class="gradient-bg text-white py-20 relative">
            <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-center">
                <!-- Hero Text -->
                <div class="md:w-1/2 text-center md:text-left z-10">
                    <h1 class="hero-title text-4xl md:text-5xl lg:text-6xl font-bold mb-6">
                        Learn Without Limits
                    </h1>
                    <p class="text-xl text-gray-100 mb-8">
                        Start, switch, or advance your career with our online courses
                    </p>
                    <a href="../views/auth/register.php" 
                       class="inline-block bg-white text-purple-600 px-8 py-3 rounded-lg font-medium shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                        Get Started
                    </a>
                </div>
                <!-- Hero Image -->
                <div class="md:w-1/2 mt-12 md:mt-0 floating">
                    <img src="https://img.freepik.com/free-vector/online-learning-isometric-concept_1284-17947.jpg" 
                         alt="Learning Illustration" 
                         class="max-w-md mx-auto ">
                </div>
            </div>
        </div>
    </div>

    <!-- About Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Why Choose Youdemy?</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Discover a new way of learning with our innovative platform</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="text-center p-6 rounded-lg card-hover">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Expert Instructors</h3>
                    <p class="text-gray-600">Learn from industry professionals with years of experience</p>
                </div>

                <!-- Feature 2 -->
                <div class="text-center p-6 rounded-lg card-hover">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Quality Content</h3>
                    <p class="text-gray-600">High-quality video courses and learning materials</p>
                </div>

                <!-- Feature 3 -->
                <div class="text-center p-6 rounded-lg card-hover">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Learn Fast</h3>
                    <p class="text-gray-600">Accelerate your learning with our proven methods</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Courses Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Featured Courses</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Start your learning journey with our most popular courses</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($courses as $index => $course): ?>
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
                        <!-- Course Header -->
                        <div class="gradient-bg text-white p-4">
                            <div class="flex items-center justify-between">
                                <span class="px-3 py-1 bg-white/20 rounded-full text-sm">
                                    <?php echo ucfirst(htmlspecialchars($course['type'])); ?>
                                </span>
                                <span class="px-3 py-1 bg-white/20 rounded-full text-sm truncate max-w-[150px]">
                                    <?php echo htmlspecialchars($course['category_name']); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Course Content -->
                        <div class="p-6">
                            <div class="flex items-center gap-4 mb-4">
                                <?php if ($course['type'] === 'video'): ?>
                                    <div class="p-2 bg-blue-100 rounded-lg">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                <?php else: ?>
                                    <div class="p-2 bg-purple-100 rounded-lg">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <h3 class="text-lg font-bold text-gray-900 line-clamp-1">
                                    <?php echo htmlspecialchars($course['title']); ?>
                                </h3>
                            </div>

                            <!-- Teacher Info -->
                            <div class="flex items-center mb-6 text-gray-600">
                                <div class="p-2 bg-gray-100 rounded-lg mr-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium">
                                    By <?php echo htmlspecialchars($course['teacher_name']); ?>
                                </span>
                            </div>

                            <!-- Enroll Button -->
                            <a href="../views/auth/login.php" 
                               class="group w-full gradient-bg text-white py-3 rounded-lg font-medium text-center shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                Enroll Now
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="gradient-bg text-white py-16">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- Logo & Description -->
                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-2xl font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-white to-purple-200">
                        Youdemy
                    </h3>
                    <p class="text-gray-200 mb-4">
                        Transforming lives through online education. Join our community of learners and start your journey today.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-200 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-200 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-200 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-200 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="#" class="text-gray-200 hover:text-white transition-colors">Our Courses</a></li>
                        <li><a href="#" class="text-gray-200 hover:text-white transition-colors">Teachers</a></li>
                        <li><a href="#" class="text-gray-200 hover:text-white transition-colors">Contact</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact Us</h4>
                    <ul class="space-y-2">
                        <li class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-gray-200">info@youdemy.com</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <span class="text-gray-200">+1 234 567 890</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="pt-8 border-t border-gray-700 text-center">
                <p class="text-gray-400"> 2024 Youdemy. All rights reserved.</p>
                <div class="mt-2 text-sm text-gray-400">
                    <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                    <span class="mx-2">|</span>
                    <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Animation pour les éléments au scroll
        function animateOnScroll() {
            const elements = document.querySelectorAll('.card-hover');
            elements.forEach((element, index) => {
                element.style.animationDelay = `${index * 0.1}s`;
            });
        }
        
        window.addEventListener('load', animateOnScroll);
        window.addEventListener('scroll', animateOnScroll);
    </script>
</body>
</html>
