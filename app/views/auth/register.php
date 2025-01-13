<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        .card {
            background: rgb(73, 106, 255);
            border-radius: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            width: 100%;
            max-width: 360px;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            top: -10px;
            right: -10px;
            width: 120px;
            height: 120px;
            background: rgb(0, 53, 114);
            border-radius: 50%;
            z-index: 0;
        }
        .card::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: -20px;
            width: 180px;
            height: 180px;
            background: rgb(33, 50, 135);
            border-radius: 50%;
            z-index: 0;
            opacity: 0.5;
        }
        .card-content {
            position: relative;
            z-index: 1;
        }
        .avatar {
            animation: float 3s ease-in-out infinite;
            position: relative;
            background: rgb(0, 53, 114);
            border-radius: 50%;
            padding: 3px;
        }
        .avatar img {
            background: white;
            border-radius: 50%;
            padding: 5px;
        }
        .avatar::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 6px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            filter: blur(3px);
            animation: shadow 3s ease-in-out infinite;
        }
        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
            100% {
                transform: translateY(0px);
            }
        }
        @keyframes shadow {
            0% {
                width: 60%;
                opacity: 0.3;
            }
            50% {
                width: 50%;
                opacity: 0.2;
            }
            100% {
                width: 60%;
                opacity: 0.3;
            }
        }
        .form-input {
            background: white;
            border: none;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-input:focus {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: rgb(0, 53, 114);
            color: white;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            background: rgb(33, 50, 135);
        }
        .link {
            color: rgb(0, 53, 114);
            font-weight: 500;
            transition: all 0.2s;
        }
        .link:hover {
            color: rgb(33, 50, 135);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="card">
        <div class="card-content">
            <div class="text-center mb-6">
                <div class="avatar w-24 h-24 mx-auto mb-4">
                    <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZD0iTTIwIDRINEMyLjkgNCAyIDQuOSAyIDZWMThDMiAxOS4xIDIuOSAyMCA0IDIwSDIwQzIxLjEgMjAgMjIgMTkuMSAyMiAxOFY2QzIyIDQuOSAyMS4xIDQgMjAgNFpNMjAgMThINFY2SDIwVjE4Wk0xOCA4SDZWMTBIMThWOFpNMTUgMTJINlYxNEgxNVYxMloiIGZpbGw9IiMwMDM1NzIiLz48L3N2Zz4=" alt="Certificate" class="w-full h-full">
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Create Account</h2>
                <p class="text-gray-600 mt-1 text-sm">Join our learning community</p>
            </div>

            <form class="space-y-4" action="/register" method="POST">
                <div>
                    <input type="text" id="name" name="name" required
                        class="w-full form-input"
                        placeholder="Full Name">
                </div>

                <div>
                    <input type="email" id="email" name="email" required
                        class="w-full form-input"
                        placeholder="Email">
                </div>

                <div>
                    <input type="password" id="password" name="password" required
                        class="w-full form-input"
                        placeholder="Password">
                </div>

                <div>
                    <select id="role" name="role" required
                        class="w-full form-input">
                        <option value="" disabled selected>Choose your role</option>
                        <option value="student">Learn on Youdemy</option>
                        <option value="teacher">Teach on Youdemy</option>
                    </select>
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

    <script>
        // Add smooth transition when navigating between pages
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const href = link.getAttribute('href');
                document.body.style.opacity = '0';
                document.body.style.transform = 'scale(0.98)';
                document.body.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                
                setTimeout(() => {
                    window.location.href = href;
                }, 300);
            });
        });

        // Fade in page on load
        window.addEventListener('load', () => {
            document.body.style.opacity = '0';
            document.body.style.transform = 'scale(0.98)';
            document.body.style.transition = 'opacity 0.5s ease, transform 0.2s ease';
            
            requestAnimationFrame(() => {
                document.body.style.opacity = '1';
                document.body.style.transform = 'scale(1)';
            });
        });

        // Add class to form inputs
        document.querySelectorAll('input, select').forEach(input => {
            input.classList.add('form-input');
        });
    </script>
</body>
</html>
