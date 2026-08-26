<?php
// auth/login.php
require_once '../config/database.php';
session_start();

// ===== LOCKOUT LOGIC =====
$lockout_time = 30;
$max_attempts = 3;

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['last_attempt_time'])) {
    $_SESSION['last_attempt_time'] = 0;
}

$error = '';
$remaining_time = 0;
$is_locked = false;

if ($_SESSION['login_attempts'] >= $max_attempts) {
    $elapsed = time() - $_SESSION['last_attempt_time'];
    if ($elapsed < $lockout_time) {
        $is_locked = true;
        $remaining_time = $lockout_time - $elapsed;
    } else {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = 0;
        $is_locked = false;
    }
}

// ===== PROCESS LOGIN =====
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$is_locked) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = isset($_POST['role']) ? $_POST['role'] : 'resident';
    
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = 0;
        
        if ($role == 'admin' && $user['role'] != 'admin') {
            $_SESSION['login_error'] = 'You are not authorized as admin.';
            header("Location: ../index.php");
            exit();
        }
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../resident/dashboard.php");
        }
        exit();
    } else {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();
        
        $remaining = $max_attempts - $_SESSION['login_attempts'];
        if ($remaining > 0) {
            $error = "Invalid email or password. $remaining attempt(s) remaining.";
        } else {
            $error = "Too many failed attempts. Please wait 30 seconds.";
            $is_locked = true;
            $remaining_time = $lockout_time;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Login | Barangay Sto. Niño</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #f0f5f2;
        }

        /* ===== LEFT SIDE - IMAGE ===== */
        .login-image {
            flex: 1;
            min-height: 100vh;
            background: url('https://thumbs.dreamstime.com/b/barangay-benoni-part-camiguin-island-philippines-96707769.jpg') center center / cover no-repeat;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 50, 35, 0.65);
        }

        .login-image-content {
            position: relative;
            z-index: 1;
            color: white;
            text-align: center;
            padding: 40px;
            max-width: 500px;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-image-content h1 {
            font-size: 3rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 15px rgba(0,0,0,0.3);
            margin-bottom: 20px;
        }

        .login-image-content p {
            font-size: 1.2rem;
            opacity: 0.95;
            line-height: 1.6;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .login-image-content .badge-group {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .login-image-content .badge-group span {
            background: rgba(0, 0, 0, 0.4);
            padding: 8px 22px;
            border-radius: 60px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.25);
            backdrop-filter: blur(5px);
        }

        /* ===== RIGHT SIDE - FORM ===== */
        .login-form-wrapper {
            flex: 0.9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 30px;
            background: white;
        }

        .login-container {
            width: 100%;
            max-width: 480px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .login-header .logo-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 15px;
            box-shadow: 0 8px 18px rgba(21, 128, 61, 0.15);
            border: 3px solid white;
            display: block;
        }

        .login-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0b4127;
        }

        .login-header p {
            color: #6b7c6f;
            font-size: 0.95rem;
            margin-top: 4px;
        }

        .role-tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 30px;
            background: #f1f5f9;
            padding: 5px;
            border-radius: 60px;
        }

        .tab-btn {
            flex: 1;
            padding: 12px 16px;
            border: none;
            background: transparent;
            font-size: 0.9rem;
            font-weight: 700;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #5b6e64;
        }

        .tab-btn i {
            font-size: 1rem;
        }

        .tab-btn.active {
            background: #0a4a2e;
            color: white;
            box-shadow: 0 4px 12px rgba(10, 74, 46, 0.3);
        }

        .tab-btn.admin-tab.active {
            background: #c53030;
            box-shadow: 0 4px 12px rgba(197, 48, 48, 0.3);
        }

        .login-form {
            display: none;
        }

        .login-form.active-form {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .input-group {
            margin-bottom: 22px;
        }

        .input-group label {
            display: block;
            font-weight: 600;
            color: #2c4b3e;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .input-group label i {
            color: #1a5d3a;
            margin-right: 8px;
            width: 18px;
        }

        .input-group input {
            width: 100%;
            padding: 14px 18px;
            border: 1.5px solid #e2e9ef;
            border-radius: 28px;
            font-size: 15px;
            font-family: inherit;
            background: #fafdfb;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: #166534;
            background: white;
            box-shadow: 0 0 0 4px rgba(22, 101, 52, 0.08);
        }

        .input-group input:hover {
            border-color: #166534;
        }

        .input-group input:disabled {
            background: #f0f0f0;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .password-wrapper {
            position: relative;
            width: 100%;
        }

        .password-wrapper input {
            width: 100%;
            padding: 14px 50px 14px 18px;
            border: 1.5px solid #e2e9ef;
            border-radius: 28px;
            font-size: 15px;
            font-family: inherit;
            background: #fafdfb;
            transition: all 0.3s ease;
        }

        .password-wrapper input:focus {
            outline: none;
            border-color: #166534;
            background: white;
            box-shadow: 0 0 0 4px rgba(22, 101, 52, 0.08);
        }

        .password-wrapper input:hover {
            border-color: #166534;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #7a8f85;
            cursor: pointer;
            font-size: 18px;
            padding: 6px;
            transition: all 0.3s ease;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            width: 36px;
            height: 36px;
        }

        .password-toggle:hover {
            color: #166534;
            background: rgba(22, 101, 52, 0.08);
        }

        button[type="submit"] {
            width: 100%;
            padding: 16px;
            background: linear-gradient(105deg, #0f5c3a, #1f8a5c);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
        }

        button[type="submit"]:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -10px #166534;
            background: linear-gradient(105deg, #0a4a2e, #166534);
        }

        button[type="submit"]:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .login-btn-admin {
            background: linear-gradient(105deg, #b91c1c, #c53030) !important;
        }

        .login-btn-admin:hover:not(:disabled) {
            background: linear-gradient(105deg, #991b1b, #b91c1c) !important;
            box-shadow: 0 12px 25px -10px #c53030 !important;
        }

        .register-link {
            text-align: center;
            margin-top: 22px;
            color: #6b7c6f;
            font-size: 15px;
        }

        .register-link a {
            color: #166534;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: #0f5c3a;
            text-decoration: underline;
        }

        .error-box {
            background: #fff5f5;
            color: #c53030;
            padding: 14px 18px;
            border-radius: 28px;
            margin-top: 18px;
            text-align: center;
            font-size: 14px;
            border-left: 4px solid #e53e3e;
        }

        .error-box i {
            margin-right: 8px;
        }

        .lockout-timer {
            background: #fef3c7;
            color: #92400e;
            padding: 14px 18px;
            border-radius: 28px;
            margin-top: 18px;
            text-align: center;
            font-size: 14px;
            border-left: 4px solid #f59e0b;
            font-weight: 600;
        }

        .lockout-timer i {
            margin-right: 8px;
        }

        .attempts-warning {
            font-size: 12px;
            color: #92400e;
            text-align: center;
            margin-top: 10px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            body {
                flex-direction: column;
            }
            .login-image {
                min-height: 250px;
                padding: 30px;
            }
            .login-image-content h1 {
                font-size: 2.2rem;
            }
            .login-image-content p {
                font-size: 1rem;
            }
            .login-form-wrapper {
                min-height: auto;
                padding: 30px 20px;
            }
            .login-container {
                max-width: 100%;
            }
        }

        @media (max-width: 600px) {
            .login-image-content .badge-group span {
                font-size: 0.7rem;
                padding: 5px 14px;
            }
            .login-header h2 {
                font-size: 1.4rem;
            }
            .login-container {
                padding: 0 5px;
            }
            .login-image-content h1 {
                font-size: 1.8rem;
            }
            .tab-btn {
                font-size: 0.8rem;
                padding: 10px 12px;
            }
            .tab-btn span {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- ===== LEFT SIDE: IMAGE ===== -->
    <div class="login-image">
        <div class="login-image-content">
            <h1>Welcome Back</h1>
            <p>Sign in to your Barangay Sto. Niño account to request documents, apply for permits, and track your applications.</p>
            <div class="badge-group">
                <span><i class="fas fa-check-circle"></i> Real-time tracking</span>
                <span><i class="fas fa-shield-alt"></i> Secure processing</span>
                <span><i class="fas fa-envelope"></i> Notifications</span>
            </div>
        </div>
    </div>

    <!-- ===== RIGHT SIDE: FORM ===== -->
    <div class="login-form-wrapper">
        <div class="login-container">
            <div class="login-header">
                <img src="../images/logo.jpg" alt="Logo" class="logo-img" onerror="this.src='https://via.placeholder.com/80?text=Logo'">
                <h2>Sign In</h2>
                <p>Access your Barangay Sto. Niño account</p>
            </div>

            <!-- Role Tabs -->
            <div class="role-tabs">
                <button class="tab-btn resident-tab active" id="residentTabBtn">
                    <i class="fas fa-user"></i> <span>Resident</span>
                </button>
                <button class="tab-btn admin-tab" id="adminTabBtn">
                    <i class="fas fa-shield-alt"></i> <span>Admin</span>
                </button>
            </div>

            <!-- ===== RESIDENT LOGIN ===== -->
            <div id="residentForm" class="login-form active-form">
                <form method="POST" action="">
                    <input type="hidden" name="role" value="resident">
                    
                    <div class="input-group">
                        <label><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" name="email" placeholder="resident@stonino.gov.ph" required <?= $is_locked ? 'disabled' : '' ?>>
                    </div>

                    <div class="input-group">
                        <label><i class="fas fa-lock"></i> Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" placeholder="Enter your password" required <?= $is_locked ? 'disabled' : '' ?>>
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" id="submitBtn" <?= $is_locked ? 'disabled' : '' ?>>
                        <i class="fas fa-arrow-right-to-bracket"></i> Sign In
                    </button>
                </form>

                <div class="register-link">
                    New to the barangay? <a href="register.php">Create an account</a>
                </div>
            </div>

            <!-- ===== ADMIN LOGIN ===== -->
            <div id="adminForm" class="login-form">
                <form method="POST" action="">
                    <input type="hidden" name="role" value="admin">
                    
                    <div class="input-group">
                        <label><i class="fas fa-envelope"></i> Admin Email</label>
                        <input type="email" name="email" placeholder="admin@barangaystonino.gov.ph" required <?= $is_locked ? 'disabled' : '' ?>>
                    </div>

                    <div class="input-group">
                        <label><i class="fas fa-key"></i> Admin Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="adminPassword" placeholder="Enter admin password" required <?= $is_locked ? 'disabled' : '' ?>>
                            <button type="button" class="password-toggle" id="toggleAdminPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="login-btn-admin" <?= $is_locked ? 'disabled' : '' ?>>
                        <i class="fas fa-shield-alt"></i> Sign In as Admin
                    </button>
                </form>
            </div>

            <!-- ===== ERRORS & LOCKOUT ===== -->
            <?php if($error): ?>
                <div class="error-box"><i class="fas fa-exclamation-triangle"></i> <?= $error ?></div>
            <?php endif; ?>

            <?php if($is_locked): ?>
                <div class="lockout-timer" id="lockoutTimer">
                    <i class="fas fa-clock"></i> Too many failed attempts. Please wait <span id="countdown"><?= $remaining_time ?></span> seconds.
                </div>
            <?php endif; ?>

            <?php if(!$is_locked && $_SESSION['login_attempts'] > 0): ?>
                <div class="attempts-warning">
                    <i class="fas fa-exclamation-circle"></i> <?= $max_attempts - $_SESSION['login_attempts'] ?> attempt(s) remaining
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // ===== TAB SWITCHING =====
        const residentTab = document.getElementById('residentTabBtn');
        const adminTab = document.getElementById('adminTabBtn');
        const residentForm = document.getElementById('residentForm');
        const adminForm = document.getElementById('adminForm');

        residentTab.addEventListener('click', function() {
            residentTab.classList.add('active');
            adminTab.classList.remove('active');
            residentForm.classList.add('active-form');
            adminForm.classList.remove('active-form');
        });

        adminTab.addEventListener('click', function() {
            adminTab.classList.add('active');
            residentTab.classList.remove('active');
            adminForm.classList.add('active-form');
            residentForm.classList.remove('active-form');
        });

        // ===== PASSWORD TOGGLE =====
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        const toggleAdminPassword = document.getElementById('toggleAdminPassword');
        const adminPassword = document.getElementById('adminPassword');
        
        toggleAdminPassword.addEventListener('click', function() {
            const type = adminPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            adminPassword.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        // ===== COUNTDOWN TIMER (LOCKOUT) =====
        <?php if($is_locked): ?>
        let remaining = <?= $remaining_time ?>;
        const countdownEl = document.getElementById('countdown');
        const lockoutTimerEl = document.getElementById('lockoutTimer');
        const submitBtn = document.getElementById('submitBtn');
        const inputs = document.querySelectorAll('input');

        const timer = setInterval(function() {
            remaining--;
            if (countdownEl) {
                countdownEl.textContent = remaining;
            }
            
            if (remaining <= 0) {
                clearInterval(timer);
                if (lockoutTimerEl) {
                    lockoutTimerEl.innerHTML = '<i class="fas fa-check-circle"></i> You can now try again.';
                    lockoutTimerEl.style.background = '#e6f7ec';
                    lockoutTimerEl.style.color = '#166534';
                    lockoutTimerEl.style.borderLeftColor = '#166534';
                }
                inputs.forEach(input => input.disabled = false);
                if (submitBtn) submitBtn.disabled = false;
                
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            }
        }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>