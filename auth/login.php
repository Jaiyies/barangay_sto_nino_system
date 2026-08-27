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
$attempts_left = $max_attempts - $_SESSION['login_attempts'];

if ($_SESSION['login_attempts'] >= $max_attempts) {
    $elapsed = time() - $_SESSION['last_attempt_time'];
    if ($elapsed < $lockout_time) {
        $is_locked = true;
        $remaining_time = $lockout_time - $elapsed;
    } else {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = 0;
        $is_locked = false;
        $attempts_left = $max_attempts;
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
    
    // ===== DUAL CHECK: HASHED or PLAIN TEXT =====
    if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = 0;
        
        // Check if user is trying to login as admin
        if ($role == 'admin' && !in_array($user['role'], ['head_admin', 'secondary_admin'])) {
            $_SESSION['login_error'] = 'You are not authorized as admin.';
            header("Location: ../index.php");
            exit();
        }
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        
        // ===== REDIRECT BASED ON ROLE =====
        if ($user['role'] == 'head_admin') {
            header("Location: ../admin/dashboard.php");
        } elseif ($user['role'] == 'secondary_admin') {
            header("Location: ../admin/secondary_dashboard.php");
        } else {
            header("Location: ../resident/dashboard.php");
        }
        exit();
    } else {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();
        
        $attempts_left = $max_attempts - $_SESSION['login_attempts'];
        if ($attempts_left > 0) {
            $error = "Invalid email or password. <strong>$attempts_left</strong> attempt(s) remaining.";
        } else {
            $error = "Too many failed attempts. Please wait <strong>30 seconds</strong>.";
            $is_locked = true;
            $remaining_time = $lockout_time;
            $attempts_left = 0;
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
        /* ===== COLOR PALETTE ===== */
        :root {
            --primary-900: #0a2e1e;
            --primary-800: #0f3d2a;
            --primary-700: #145233;
            --primary-600: #1a6b42;
            --primary-500: #228b54;
            --primary-400: #3aa86c;
            --primary-300: #6bc48e;
            --primary-200: #a3dbb8;
            --primary-100: #d4f0df;
            --primary-50: #eff8f2;
            
            --accent-900: #7a1a1a;
            --accent-800: #8b1e1e;
            --accent-700: #a52222;
            --accent-600: #c42828;
            --accent-500: #d43a3a;
            
            --neutral-900: #1a2b22;
            --neutral-700: #3d5a4a;
            --neutral-500: #6b8475;
            --neutral-300: #a8bdb2;
            --neutral-100: #dde8e1;
            --neutral-50: #f4f8f5;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; min-height: 100vh; display: flex; background: var(--neutral-50); }

        /* ===== LEFT PANEL ===== */
        .login-image {
            flex: 1; min-height: 100vh;
            background: url('https://thumbs.dreamstime.com/b/barangay-benoni-part-camiguin-island-philippines-96707769.jpg') center center / cover no-repeat;
            position: relative; display: flex; align-items: center; justify-content: center;
        }
        .login-image::before { 
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
            background: linear-gradient(135deg, rgba(10,46,30,0.82), rgba(20,82,51,0.72)); 
        }
        .login-image-content { position: relative; z-index: 1; color: white; text-align: center; padding: 40px; max-width: 500px; animation: fadeInUp 0.6s ease-out; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .login-image-content h1 { font-size: 3rem; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 16px; }
        .login-image-content h1 span { color: #ffb3b3; }
        .login-image-content p { font-size: 1.1rem; opacity: 0.95; line-height: 1.6; text-shadow: 0 2px 10px rgba(0,0,0,0.2); }
        .login-image-content .badge-group { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-top: 28px; }
        .login-image-content .badge-group span { 
            background: rgba(255,255,255,0.10); padding: 8px 22px; border-radius: 60px; font-size: 0.8rem; 
            font-weight: 600; border: 1px solid rgba(255,255,255,0.15); backdrop-filter: blur(6px);
            transition: all 0.3s ease;
        }
        .login-image-content .badge-group span:hover { background: rgba(255,255,255,0.20); transform: translateY(-2px); }

        /* ===== RIGHT PANEL ===== */
        .login-form-wrapper { flex: 0.9; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 30px; background: white; position: relative; }
        .login-container { width: 100%; max-width: 460px; position: relative; }

        .login-header { text-align: center; margin-bottom: 32px; }
        .login-header .logo-img { 
            width: 76px; height: 76px; border-radius: 50%; object-fit: cover; margin: 0 auto 14px; 
            box-shadow: 0 8px 24px rgba(26,107,66,0.15); border: 3px solid white; display: block; background: var(--primary-50);
            transition: transform 0.3s ease;
        }
        .login-header .logo-img:hover { transform: scale(1.05); }
        .login-header h2 { font-size: 1.8rem; font-weight: 700; color: var(--primary-800); }
        .login-header h2 span { color: var(--accent-700); }
        .login-header p { color: var(--neutral-500); font-size: 0.95rem; margin-top: 2px; }

        /* ===== ROLE TABS ===== */
        .role-tabs { display: flex; gap: 10px; margin-bottom: 28px; background: var(--neutral-50); padding: 5px; border-radius: 60px; border: 1px solid var(--neutral-100); }
        .tab-btn { 
            flex: 1; padding: 12px 16px; border: none; background: transparent; font-size: 0.9rem; font-weight: 700; 
            border-radius: 50px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; 
            gap: 8px; color: var(--neutral-500); position: relative; overflow: hidden;
        }
        .tab-btn::before {
            content: ''; position: absolute; top: 50%; left: 50%; width: 0; height: 0; border-radius: 50%;
            background: rgba(26,107,66,0.10); transition: all 0.5s ease; transform: translate(-50%, -50%);
        }
        .tab-btn:hover::before { width: 200px; height: 200px; }
        .tab-btn i { font-size: 1rem; transition: transform 0.3s ease; }
        .tab-btn:hover i { transform: scale(1.15); }
        .tab-btn.active { 
            background: var(--primary-600); color: white; box-shadow: 0 4px 16px rgba(26,107,66,0.25);
            animation: tabPop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .tab-btn.admin-tab.active { 
            background: var(--accent-700); box-shadow: 0 4px 16px rgba(165,34,34,0.25);
        }
        @keyframes tabPop {
            0% { transform: scale(0.92); }
            100% { transform: scale(1); }
        }

        /* ===== FORMS ===== */
        .login-form { display: none; }
        .login-form.active-form { display: block; animation: fadeIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(15px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }

        .input-group { margin-bottom: 20px; position: relative; }
        .input-group label { display: block; font-weight: 600; color: var(--primary-800); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .input-group label i { color: var(--primary-500); margin-right: 8px; width: 18px; }
        .input-group input { 
            width: 100%; padding: 14px 18px; border: 1.5px solid var(--neutral-100); border-radius: 28px; 
            font-size: 15px; font-family: inherit; background: var(--neutral-50); transition: all 0.3s ease; 
        }
        .input-group input:focus { 
            outline: none; border-color: var(--primary-500); background: white; 
            box-shadow: 0 0 0 4px rgba(34,139,84,0.10); 
        }
        .input-group input:disabled { background: var(--neutral-100); cursor: not-allowed; opacity: 0.7; }
        .input-group input.shake { animation: shake 0.5s ease; }
        @keyframes shake {
            0%, 100% { transform: translateX(0); border-color: var(--accent-500); }
            20% { transform: translateX(-10px); }
            40% { transform: translateX(10px); }
            60% { transform: translateX(-6px); }
            80% { transform: translateX(6px); }
        }

        .password-wrapper { position: relative; width: 100%; }
        .password-wrapper input { 
            width: 100%; padding: 14px 50px 14px 18px; border: 1.5px solid var(--neutral-100); border-radius: 28px; 
            font-size: 15px; font-family: inherit; background: var(--neutral-50); transition: all 0.3s ease; 
        }
        .password-wrapper input:focus { 
            outline: none; border-color: var(--primary-500); background: white; 
            box-shadow: 0 0 0 4px rgba(34,139,84,0.10); 
        }
        .password-toggle { 
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%); 
            background: transparent; border: none; color: var(--neutral-500); cursor: pointer; font-size: 18px; 
            padding: 6px; transition: all 0.3s ease; border-radius: 50%; display: flex; align-items: center; 
            justify-content: center; z-index: 2; width: 36px; height: 36px; 
        }
        .password-toggle:hover { color: var(--primary-600); background: rgba(26,107,66,0.08); transform: translateY(-50%) scale(1.1); }

        /* ===== SUBMIT BUTTON ===== */
        .submit-wrapper {
            position: relative; margin-top: 8px;
        }
        button[type="submit"] { 
            width: 100%; padding: 16px; background: linear-gradient(105deg, var(--primary-700), var(--primary-500)); 
            color: white; border: none; border-radius: 50px; font-size: 16px; font-weight: 700; cursor: pointer; 
            transition: all 0.3s ease; display: flex; justify-content: center; align-items: center; gap: 12px; 
            position: relative; overflow: hidden;
        }
        button[type="submit"]::after {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.05), transparent);
            transform: rotate(45deg) translateX(-100%);
            transition: all 0.6s ease;
        }
        button[type="submit"]:hover:not(:disabled)::after { transform: rotate(45deg) translateX(100%); }
        button[type="submit"]:hover:not(:disabled) { 
            transform: translateY(-2px); box-shadow: 0 12px 28px -10px var(--primary-600); 
            background: linear-gradient(105deg, var(--primary-800), var(--primary-600)); 
        }
        button[type="submit"]:active:not(:disabled) { transform: scale(0.98); }
        button[type="submit"]:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .login-btn-admin { 
            background: linear-gradient(105deg, var(--accent-800), var(--accent-600)) !important; 
        }
        .login-btn-admin:hover:not(:disabled) { 
            background: linear-gradient(105deg, var(--accent-900), var(--accent-700)) !important; 
            box-shadow: 0 12px 28px -10px var(--accent-600) !important; 
        }

        /* ===== ATTEMPT COUNTER ===== */
        .attempt-counter {
            display: flex; align-items: center; justify-content: space-between;
            margin-top: 12px; padding: 0 4px;
        }
        .attempt-counter .attempt-text {
            font-size: 0.8rem; font-weight: 500; color: var(--neutral-500);
        }
        .attempt-counter .attempt-text.warning { color: #b45309; }
        .attempt-counter .attempt-text.danger { color: var(--accent-600); }
        .attempt-counter .dots {
            display: flex; gap: 6px;
        }
        .attempt-counter .dot {
            width: 10px; height: 10px; border-radius: 50%;
            background: var(--neutral-200); transition: all 0.4s ease;
            border: 1px solid var(--neutral-300);
        }
        .attempt-counter .dot.filled {
            background: var(--neutral-400); border-color: var(--neutral-500);
        }
        .attempt-counter .dot.filled.failed {
            background: var(--accent-500); border-color: var(--accent-600);
            animation: dotPulse 0.5s ease;
        }
        @keyframes dotPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.5); }
            100% { transform: scale(1); }
        }
        .attempt-counter .dot.filled.success {
            background: var(--primary-500); border-color: var(--primary-600);
        }

        .register-link { text-align: center; margin-top: 20px; color: var(--neutral-500); font-size: 14px; }
        .register-link a { color: var(--primary-600); text-decoration: none; font-weight: 700; transition: all 0.3s ease; position: relative; }
        .register-link a::after {
            content: ''; position: absolute; bottom: -2px; left: 0; width: 0; height: 2px;
            background: var(--primary-600); transition: width 0.3s ease;
        }
        .register-link a:hover::after { width: 100%; }
        .register-link a:hover { color: var(--primary-800); }

        /* ===== ERROR MESSAGE ===== */
        .error-box {
            background: #fef2f2; color: var(--accent-600); padding: 10px 18px; 
            border-radius: 28px; margin-bottom: 12px; text-align: center; font-size: 13px; 
            border: 1px solid #fecaca;
            animation: errorSlide 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        @keyframes errorSlide {
            0% { opacity: 0; transform: translateY(-10px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .error-box i { font-size: 14px; }

        /* ===== LOCKOUT OVERLAY ===== */
        .lockout-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(8px);
            z-index: 9999;
            display: flex; align-items: center; justify-content: center;
            animation: overlayFade 0.5s ease;
        }
        @keyframes overlayFade {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
        .lockout-modal {
            background: white; border-radius: 48px; padding: 48px 56px 56px;
            max-width: 440px; width: 92%; text-align: center;
            animation: modalPop 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 50px 80px -20px rgba(0,0,0,0.4);
        }
        @keyframes modalPop {
            0% { opacity: 0; transform: scale(0.85) translateY(30px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }
        .lockout-modal .lock-icon {
            font-size: 3.5rem; color: #f59e0b; margin-bottom: 16px;
            animation: lockBounce 1s ease infinite;
        }
        @keyframes lockBounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .lockout-modal h3 {
            font-size: 1.4rem; font-weight: 700; color: var(--primary-800);
            margin-bottom: 8px;
        }
        .lockout-modal p {
            color: var(--neutral-500); font-size: 0.95rem; margin-bottom: 24px;
        }
        .lockout-modal .timer-circle {
            width: 100px; height: 100px; border-radius: 50%; margin: 0 auto 20px;
            background: conic-gradient(#f59e0b var(--progress, 100%), #e5e7eb var(--progress, 100%));
            display: flex; align-items: center; justify-content: center; position: relative;
            transition: all 0.3s ease;
        }
        .lockout-modal .timer-circle::before {
            content: ''; position: absolute; width: 80px; height: 80px; border-radius: 50%;
            background: white;
        }
        .lockout-modal .timer-number {
            position: relative; z-index: 1; font-size: 2.2rem; font-weight: 800; color: #92400e;
        }
        .lockout-modal .timer-label {
            font-size: 0.8rem; color: var(--neutral-500); font-weight: 500;
        }
        .lockout-modal .progress-bar {
            width: 100%; height: 6px; background: var(--neutral-100); border-radius: 12px;
            margin-top: 16px; overflow: hidden;
        }
        .lockout-modal .progress-bar .progress-fill {
            height: 100%; background: linear-gradient(90deg, #f59e0b, #d97706);
            border-radius: 12px; transition: width 0.3s ease;
            width: 100%;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            body { flex-direction: column; }
            .login-image { min-height: 220px; padding: 30px; }
            .login-image-content h1 { font-size: 2rem; }
            .login-image-content p { font-size: 0.95rem; }
            .login-form-wrapper { min-height: auto; padding: 30px 20px; }
            .login-container { max-width: 100%; }
        }
        @media (max-width: 600px) {
            .login-image-content .badge-group span { font-size: 0.7rem; padding: 5px 14px; }
            .login-header h2 { font-size: 1.4rem; }
            .login-container { padding: 0 5px; }
            .login-image-content h1 { font-size: 1.6rem; }
            .tab-btn { font-size: 0.8rem; padding: 10px 12px; }
            .tab-btn span { display: none; }
            .lockout-modal { padding: 32px 24px 40px; }
            .lockout-modal .timer-circle { width: 80px; height: 80px; }
            .lockout-modal .timer-circle::before { width: 64px; height: 64px; }
            .lockout-modal .timer-number { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

    <!-- ===== LEFT: IMAGE PANEL ===== -->
    <div class="login-image">
        <div class="login-image-content">
            <h1>Barangay <span>Sto. Niño</span></h1>
            <p>Sign in to your account to request documents, apply for permits, and track your applications online.</p>
            <div class="badge-group">
                <span><i class="fas fa-check-circle"></i> Real-time tracking</span>
                <span><i class="fas fa-shield-alt"></i> Secure processing</span>
                <span><i class="fas fa-envelope"></i> Email notifications</span>
            </div>
        </div>
    </div>

    <!-- ===== RIGHT: LOGIN FORM ===== -->
    <div class="login-form-wrapper">
        <div class="login-container">

            <div class="login-header">
                <img src="../images/logo.jpg" alt="Barangay Sto. Niño Logo" class="logo-img" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Ccircle cx=%2250%22 cy=%2250%22 r=%2248%22 fill=%22%231a6b42%22/%3E%3Ctext x=%2250%22 y=%2262%22 font-size=%2244%22 text-anchor=%22middle%22 fill=%22white%22 font-family=%22Arial%22 font-weight=%22bold%22%3ESN%3C/text%3E%3C/svg%3E'">
                <h2>Welcome <span>Back</span></h2>
                <p>Sign in to continue</p>
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

            <!-- Resident Form -->
            <div id="residentForm" class="login-form active-form">
                <form method="POST" action="" id="loginForm">
                    <input type="hidden" name="role" value="resident">
                    <div class="input-group">
                        <label for="emailInput"><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" name="email" id="emailInput" placeholder="resident@stonino.gov.ph" autocomplete="email" required>
                    </div>
                    <div class="input-group">
                        <label for="password"><i class="fas fa-lock"></i> Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" placeholder="Enter your password" autocomplete="current-password" required>
                            <button type="button" class="password-toggle" id="togglePassword"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>

                    <?php if($error && !$is_locked): ?>
                        <div class="error-box"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
                    <?php endif; ?>

                    <div class="submit-wrapper">
                        <button type="submit" id="submitBtn">
                            <i class="fas fa-arrow-right-to-bracket"></i> Sign In
                        </button>
                    </div>
                </form>

                <?php if(!$is_locked && $_SESSION['login_attempts'] > 0): ?>
                <div class="attempt-counter">
                    <span class="attempt-text <?= $attempts_left <= 1 ? 'danger' : 'warning' ?>">
                        <i class="fas fa-exclamation-circle"></i> <?= $attempts_left ?> attempt(s) remaining
                    </span>
                    <div class="dots">
                        <?php for($i = 0; $i < $max_attempts; $i++): ?>
                            <span class="dot <?= $i < $_SESSION['login_attempts'] ? 'filled failed' : '' ?>"></span>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="register-link">New to the barangay? <a href="register.php">Create an account</a></div>
            </div>

            <!-- Admin Form -->
            <div id="adminForm" class="login-form">
                <form method="POST" action="" id="adminLoginForm">
                    <input type="hidden" name="role" value="admin">
                    <div class="input-group">
                        <label for="adminEmailInput"><i class="fas fa-envelope"></i> Admin Email</label>
                        <input type="email" name="email" id="adminEmailInput" placeholder="admin@barangaystonino.gov.ph" autocomplete="email" required>
                    </div>
                    <div class="input-group">
                        <label for="adminPassword"><i class="fas fa-key"></i> Admin Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="adminPassword" placeholder="Enter admin password" autocomplete="current-password" required>
                            <button type="button" class="password-toggle" id="toggleAdminPassword"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>

                    <?php if($error && !$is_locked): ?>
                        <div class="error-box"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
                    <?php endif; ?>

                    <div class="submit-wrapper">
                        <button type="submit" class="login-btn-admin">
                            <i class="fas fa-shield-alt"></i> Sign In as Admin
                        </button>
                    </div>
                </form>

                <?php if(!$is_locked && $_SESSION['login_attempts'] > 0): ?>
                <div class="attempt-counter">
                    <span class="attempt-text <?= $attempts_left <= 1 ? 'danger' : 'warning' ?>">
                        <i class="fas fa-exclamation-circle"></i> <?= $attempts_left ?> attempt(s) remaining
                    </span>
                    <div class="dots">
                        <?php for($i = 0; $i < $max_attempts; $i++): ?>
                            <span class="dot <?= $i < $_SESSION['login_attempts'] ? 'filled failed' : '' ?>"></span>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- ===== LOCKOUT OVERLAY ===== -->
    <?php if($is_locked): ?>
    <div class="lockout-overlay" id="lockoutOverlay">
        <div class="lockout-modal">
            <div class="lock-icon"><i class="fas fa-lock"></i></div>
            <h3>Too Many Attempts</h3>
            <p>For security, please wait <strong>30 seconds</strong> before trying again.</p>
            <div class="timer-circle" id="lockoutCircle" style="--progress: 100%;">
                <span class="timer-number" id="countdown"><?= $remaining_time ?></span>
            </div>
            <div class="timer-label">seconds remaining</div>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill" style="width: 100%;"></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ===== SCRIPTS ===== -->
    <script>
        // ===== ROLE TABS =====
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

        <?php if($error && !$is_locked): ?>
        const emailInput = document.getElementById('emailInput');
        const passInput = document.getElementById('password');
        if (emailInput) emailInput.classList.add('shake');
        if (passInput) passInput.classList.add('shake');
        setTimeout(() => {
            if (emailInput) emailInput.classList.remove('shake');
            if (passInput) passInput.classList.remove('shake');
        }, 600);
        <?php endif; ?>

        <?php if($is_locked): ?>
        let remaining = <?= $remaining_time ?>;
        const totalTime = <?= $lockout_time ?>;
        const countdownEl = document.getElementById('countdown');
        const lockoutCircle = document.getElementById('lockoutCircle');
        const progressFill = document.getElementById('progressFill');
        const overlay = document.getElementById('lockoutOverlay');

        function updateTimer() {
            const progress = (remaining / totalTime) * 100;
            if (lockoutCircle) lockoutCircle.style.setProperty('--progress', progress + '%');
            if (progressFill) progressFill.style.width = progress + '%';
            if (countdownEl) countdownEl.textContent = remaining;
        }

        const timer = setInterval(function() {
            remaining--;
            updateTimer();
            
            if (remaining <= 0) {
                clearInterval(timer);
                if (overlay) {
                    overlay.style.transition = 'opacity 0.5s ease';
                    overlay.style.opacity = '0';
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);
                } else {
                    window.location.reload();
                }
            }
        }, 1000);

        updateTimer();
        <?php endif; ?>
    </script>

</body>
</html>