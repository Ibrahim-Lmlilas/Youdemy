<?php
session_start();
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register/Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../../assets/css/register.css" rel="stylesheet">
</head> 
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="card">
        <div class="card-content">
            
            <div class="text-center mb-6">
                <div class="avatar w-24 h-24 mx-auto mb-4">
                <img src="../../assets/img/C.jpg" class="w-full h-full object-cover rounded-full shadow-lg transform hover:scale-105 transition-transform duration-300">
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Create Account</h2>
                <p class="text-gray-600 mt-1 text-sm">Join our learning community</p>
            </div>

            <form class="space-y-4" action="/yooudemy/controllers/register_process/register_process.php" method="POST">
                <div>
                    <input type="text" id="name" name="name" required
                           class="w-full form-input <?php echo isset($errors['name']) ? 'border-red-500' : ''; ?>"
                           placeholder="Full Name">
                    <?php if(isset($errors['name'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['name']; ?></p>
                    <?php endif; ?>
                </div>

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

                <div>
                    <select id="role" name="role" required
                            class="w-full form-input <?php echo isset($errors['role']) ? 'border-red-500' : ''; ?>">
                        <option value="" disabled selected>Choose your role</option>
                        <option value="student">Learn on Youdemy</option>
                        <option value="teacher">Teach on Youdemy</option>
                    </select>
                    <?php if(isset($errors['role'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['role']; ?></p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="w-full btn-primary">
                    Create Account
                </button>
            </form>

            <p class="mt-6 text-center text-sm ">
                Already have an account?
                <a href="./login.php" class="link ml-1">Sign in</a>
            </p>
        </div>
    </div>



    <script src="../../assets/js/register.js"></script>
</body>
</html>
