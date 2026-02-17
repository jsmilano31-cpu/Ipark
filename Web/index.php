<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPark - Smart Parking System</title>
    <link rel="stylesheet" href="styles/styles.css">
    <style>
        /* Additional styles for login page */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
            /* background removed to show body bg */
        }

        .login-card {
            background: var(--light);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-xl);
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            border: 1px solid var(--gray-200);
            animation: fadeInUp 0.8s ease;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
        }

        .login-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-subtitle {
            color: var(--gray-600);
            font-size: 1rem;
            margin-bottom: 0;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--light);
            color: var(--gray-800);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            transform: translateY(-1px);
        }

        .form-control::placeholder {
            color: var(--gray-400);
        }

        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border: 2px solid transparent;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--light);
            box-shadow: var(--shadow);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            text-decoration: none;
            color: var(--light);
        }

        .btn-outline-primary {
            background: transparent;
            color: var(--primary);
            border-color: var(--primary);
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            color: var(--light);
            transform: translateY(-1px);
            text-decoration: none;
        }

        .btn-outline-secondary {
            background: transparent;
            color: var(--gray-600);
            border-color: var(--gray-300);
        }

        .btn-outline-secondary:hover {
            background: var(--gray-600);
            color: var(--light);
            border-color: var(--gray-600);
            transform: translateY(-1px);
            text-decoration: none;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .login-container {
                padding: 1rem;
            }
            
            .login-card {
                padding: 2rem;
            }

            .login-title {
                font-size: 1.5rem;
            }

            .logo {
                max-width: 120px;
            }
        }

        /* Loading animation for better UX */
        .btn:active {
            transform: scale(0.98);
        }

        /* Focus styles for accessibility */
        .btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
        }

        body {
            min-height: 100vh;
            background: linear-gradient(rgba(30, 41, 59, 0.7), rgba(99, 102, 241, 0.5)), url('assets/bg.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Inter', sans-serif;
            color: #222;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="assets/images/logo.png" alt="IPark Logo" class="logo">
                <h1 class="login-title">IPark Parking System</h1>
                <p class="login-subtitle">Welcome back! Please sign in to your account.</p>
            </div>
           
            <form action="login_process.php" method="POST" class="login-form">
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <span>Sign In</span>
                    </button>
                    <a href="register.php" class="btn btn-outline-primary">
                        <span>Create New Account</span>
                    </a>
                    <a href="admin_login.php" class="btn btn-outline-secondary">
                        <span>Admin Portal</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>