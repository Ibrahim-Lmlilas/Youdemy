<?php
session_start();
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? '';
unset($_SESSION['errors'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../../assets/css/login.css" rel="stylesheet">
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4 ">
<div class="flex justify-between items-center pb-6">
        <a href="../../public/index.php" class="text-gray-600 hover:text-gray-800 flex items-center gap-2 group">
                  <svg class="w-5 h-5 transform group-hover:-translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
              Back to Home
        </a>
    </div>
    <div class="card">
        <div class="card-content">
            <div class="text-center mb-6">
                <div class="avatar w-24 h-24 mx-auto mb-4">
                    <img src="../../assets/img/C.jpg" class="w-full h-full object-cover rounded-full shadow-lg transform hover:scale-105 transition-transform duration-300">
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Welcome back!</h2>
                <p class="text-gray-600 mt-1 text-sm">Sign in to your account</p>

                <?php if($success): ?>
                    <div class="mt-4 p-2 bg-green-100 text-green-700 rounded">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($errors['login'])): ?>
                    <div class="mt-4 p-2 bg-red-100 text-red-700 rounded">
                        <?php echo $errors['login']; ?>
                    </div>
                <?php endif; ?>
            </div>
<!-- --------------------------------------------------------------------------------------------------------------------------- -->
            <form class="space-y-4" action="/Youdemy/controllers/login_process/login_process.php" method="POST">
                <div>
                    <input type="email" id="email" name="email" required
                           class="w-full form-input <?php echo isset($errors['email']) ? 'border-red-500' : ''; ?>"
                           placeholder="Email">
                    <?php if(isset($errors['email'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['email']; ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <input type="password" id="password" name="password" required
                           class="w-full form-input <?php echo isset($errors['password']) ? 'border-red-500' : ''; ?>"
                           placeholder="Password">
                    <?php if(isset($errors['password'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['password']; ?></p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="w-full btn-primary">
                    Sign in
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-blue-900">
                Don't have an account?
                <a href="./register.php" class="link ml-1 text-blue-700 hover:text-blue-900 transition-colors duration-300">Create one</a>
            </p>
        </div>
</div>

    <script src="../../assets/js/login.js"></script>
</body>
</html>
