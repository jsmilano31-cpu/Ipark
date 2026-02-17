<?php
session_start();
require_once 'db_connect.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step'])) {
    $step = (int)$_POST['step'];
    
    // Store form data in session
    if ($step === 1) {
        $_SESSION['reg_data']['first_name'] = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
        $_SESSION['reg_data']['last_name'] = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
        $_SESSION['reg_data']['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        
        // Redirect to step 2
        header('Location: register.php?step=2');
        exit();
    } elseif ($step === 2) {
        $_SESSION['reg_data']['phone_number'] = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);
        $_SESSION['reg_data']['address'] = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
        
        // Redirect to step 3
        header('Location: register.php?step=3');
        exit();
    } elseif ($step === 3) {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($password !== $confirm_password) {
            $_SESSION['error'] = 'Passwords do not match';
            header('Location: register.php?step=3');
            exit();
        }
        
        try {
            // Check if email already exists
            $sql = "SELECT id FROM ipark_users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $_SESSION['reg_data']['email']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $_SESSION['error'] = 'Email already registered';
                header('Location: register.php?step=1');
                exit();
            }
            
            // Insert new user
            $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO ipark_users (first_name, last_name, email, password, phone_number, address) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssss",
                $_SESSION['reg_data']['first_name'],
                $_SESSION['reg_data']['last_name'],
                $_SESSION['reg_data']['email'],
                $hashed_pw,
                $_SESSION['reg_data']['phone_number'],
                $_SESSION['reg_data']['address']
            );
            $stmt->execute();
            
            // Clear registration data
            unset($_SESSION['reg_data']);
            
            $_SESSION['success'] = 'Registration successful! Please login.';
            header('Location: index.php');
            exit();
            
        } catch(PDOException $e) {
            $_SESSION['error'] = 'Registration failed. Please try again.';
            header('Location: register.php?step=3');
            exit();
        }
    }
}

// Get current step
$current_step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$total_steps = 3;

// Validate step number
if ($current_step < 1 || $current_step > $total_steps) {
    $current_step = 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - IPark</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #f1f5f9;
            --accent: #22d3ee;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #0f172a;
            --light: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --border-radius: 12px;
            --border-radius-lg: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(rgba(30, 41, 59, 0.7), rgba(99, 102, 241, 0.5)), url('assets/bg.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Inter', sans-serif;
            color: #222;
        }

        .register-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .register-card {
            background: var(--light);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-xl);
            padding: 3rem;
            width: 100%;
            max-width: 600px;
            border: 1px solid var(--gray-200);
            animation: fadeInUp 0.8s ease;
        }

        .register-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
        }

        .register-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .register-subtitle {
            color: var(--gray-600);
            font-size: 1rem;
            margin-bottom: 0;
        }

        /* Progress Steps */
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3rem;
            position: relative;
            padding: 0 1rem;
        }

        .steps::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--gray-200);
            transform: translateY(-50%);
            z-index: 1;
        }

        .step {
            position: relative;
            z-index: 2;
            background: var(--light);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--gray-400);
            border: 2px solid var(--gray-200);
            transition: all 0.3s ease;
        }

        .step.active {
            background: var(--primary);
            color: var(--light);
            border-color: var(--primary);
        }

        .step.completed {
            background: var(--success);
            color: var(--light);
            border-color: var(--success);
        }

        .step-label {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: var(--gray-600);
            white-space: nowrap;
        }

        /* Form Styles */
        .register-form {
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
            gap: 1rem;
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
            flex: 1;
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

        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 500;
            animation: slideIn 0.3s ease;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .alert-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .alert-close:hover {
            opacity: 1;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .register-container {
                padding: 1rem;
            }
            
            .register-card {
                padding: 2rem;
            }

            .register-title {
                font-size: 1.5rem;
            }

            .logo {
                max-width: 120px;
            }

            .btn-group {
                flex-direction: column;
            }

            .steps {
                padding: 0 0.5rem;
            }

            .step-label {
                display: none;
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
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <img src="assets/images/logo.png" alt="IPark Logo" class="logo">
                <h1 class="register-title">Create Your Account</h1>
                <p class="register-subtitle">Join IPark for smart parking solutions</p>
            </div>

            <!-- Progress Steps -->
            <div class="steps">
                <div class="step <?php echo $current_step >= 1 ? 'active' : ''; ?> <?php echo $current_step > 1 ? 'completed' : ''; ?>">
                    1
                    <span class="step-label">Personal Info</span>
                </div>
                <div class="step <?php echo $current_step >= 2 ? 'active' : ''; ?> <?php echo $current_step > 2 ? 'completed' : ''; ?>">
                    2
                    <span class="step-label">Contact Details</span>
                </div>
                <div class="step <?php echo $current_step >= 3 ? 'active' : ''; ?>">
                    3
                    <span class="step-label">Security</span>
                </div>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span style="font-size: 1.25rem;">⚠️</span>
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="register-form">
                <input type="hidden" name="step" value="<?php echo $current_step; ?>">
                
                <?php if($current_step === 1): ?>
                    <!-- Step 1: Personal Information -->
                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" 
                               value="<?php echo $_SESSION['reg_data']['first_name'] ?? ''; ?>"
                               placeholder="Enter your first name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" 
                               value="<?php echo $_SESSION['reg_data']['last_name'] ?? ''; ?>"
                               placeholder="Enter your last name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo $_SESSION['reg_data']['email'] ?? ''; ?>"
                               placeholder="Enter your email address" required>
                    </div>

                <?php elseif($current_step === 2): ?>
                    <!-- Step 2: Contact Information -->
                    <div class="form-group">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone_number" name="phone_number" 
                               value="<?php echo $_SESSION['reg_data']['phone_number'] ?? ''; ?>"
                               placeholder="Enter your phone number" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" 
                                  placeholder="Enter your address" rows="3" required><?php echo $_SESSION['reg_data']['address'] ?? ''; ?></textarea>
                    </div>

                <?php elseif($current_step === 3): ?>
                    <!-- Step 3: Security -->
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Create a password" required 
                               minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" 
                               title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters">
                        <small style="color: var(--gray-500); margin-top: 0.5rem; display: block;">
                            Password must be at least 8 characters long and include uppercase, lowercase, and numbers
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm your password" required>
                    </div>
                <?php endif; ?>

                <div class="btn-group">
                    <?php if($current_step > 1): ?>
                        <a href="?step=<?php echo $current_step - 1; ?>" class="btn btn-outline-secondary">
                            <span>← Back</span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if($current_step < $total_steps): ?>
                        <button type="submit" class="btn btn-primary">
                            <span>Continue →</span>
                        </button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary">
                            <span>Create Account</span>
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => alert.remove(), 300);
                });
            }, 5000);

            // Password validation
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (password && confirmPassword) {
                function validatePassword() {
                    if (password.value !== confirmPassword.value) {
                        confirmPassword.setCustomValidity("Passwords don't match");
                    } else {
                        confirmPassword.setCustomValidity('');
                    }
                }

                password.addEventListener('change', validatePassword);
                confirmPassword.addEventListener('keyup', validatePassword);
            }
        });
    </script>
</body>
</html> 