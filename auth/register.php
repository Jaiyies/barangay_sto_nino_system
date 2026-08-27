<?php
// auth/register.php
require_once '../config/database.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $conn = $db->getConnection();
    
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    $birthdate = trim($_POST['birthdate']);
    
    $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check->execute([$email]);
    
    if ($check->rowCount() > 0) {
        $error = "Email already registered!";
    } else {
        $full_name = $first_name . ' ' . $last_name;
        $username = $email;
        
        $query = "INSERT INTO users (full_name, username, email, password, contact_number, address, role) 
                  VALUES (?, ?, ?, ?, ?, ?, 'resident')";
        $stmt = $conn->prepare($query);
        
        if ($stmt->execute([$full_name, $username, $email, $password, $contact_number, $address])) {
            
            // ===== SEND WELCOME EMAIL =====
            require_once '../config/mail_config.php';
            
            $subject = "Welcome to Barangay Sto. Niño Online Services!";
            $body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e9ef; border-radius: 10px; }
                        .header { background: #0d3b26; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                        .content { padding: 20px; }
                        .btn { background: #0d3b26; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
                        .footer { text-align: center; padding: 10px; font-size: 12px; color: #6b7c6f; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>🏛️ Barangay Sto. Niño</h2>
                            <p>Online Services Portal</p>
                        </div>
                        <div class='content'>
                            <h3>Welcome, $full_name!</h3>
                            <p>Thank you for registering to Barangay Sto. Niño Online Services.</p>
                            <p>You can now request documents and apply for event permits online.</p>
                            <br>
                            <a href='http://localhost/barangay_sto_nino_system/auth/login.php' class='btn'>Login Now</a>
                        </div>
                        <div class='footer'>
                            &copy; 2026 Barangay Sto. Niño. All rights reserved.
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            sendEmail($email, $subject, $body);
            // ===== END SEND WELCOME EMAIL =====
            
            $_SESSION['register_success'] = "Registration successful! You can now login.";
            header("Location: ../index.php");
            exit();
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Register | Barangay Sto. Niño</title>
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
        .register-image {
            flex: 1;
            min-height: 100vh;
            background: url('https://thumbs.dreamstime.com/b/barangay-benoni-part-camiguin-island-philippines-96707769.jpg') center center / cover no-repeat;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 50, 35, 0.65);
        }

        .register-image-content {
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

        .register-image-content h1 {
            font-size: 3rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 15px rgba(0,0,0,0.3);
            margin-bottom: 20px;
        }

        .register-image-content p {
            font-size: 1.2rem;
            opacity: 0.95;
            line-height: 1.6;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .register-image-content .badge-group {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .register-image-content .badge-group span {
            background: rgba(0, 0, 0, 0.4);
            padding: 8px 22px;
            border-radius: 60px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.25);
            backdrop-filter: blur(5px);
        }

        /* ===== RIGHT SIDE - FORM ===== */
        .register-form-wrapper {
            flex: 0.9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 30px;
            background: white;
        }

        .register-container {
            width: 100%;
            max-width: 520px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .register-header .logo-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 15px;
            box-shadow: 0 8px 18px rgba(21, 128, 61, 0.15);
            border: 3px solid white;
            display: block;
        }

        .register-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0b4127;
        }

        .register-header p {
            color: #6b7c6f;
            font-size: 0.95rem;
            margin-top: 4px;
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

        .input-group input,
        .input-group textarea,
        .input-group select {
            width: 100%;
            padding: 14px 18px;
            border: 1.5px solid #e2e9ef;
            border-radius: 28px;
            font-size: 15px;
            font-family: inherit;
            background: #fafdfb;
            transition: all 0.3s ease;
        }

        .input-group input:focus,
        .input-group textarea:focus,
        .input-group select:focus {
            outline: none;
            border-color: #166534;
            background: white;
            box-shadow: 0 0 0 4px rgba(22, 101, 52, 0.08);
        }

        .input-group input:hover,
        .input-group textarea:hover,
        .input-group select:hover {
            border-color: #166534;
        }

        .input-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .input-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
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

        .password-strength {
            margin-top: 10px;
            height: 4px;
            background: #e2e9ef;
            border-radius: 4px;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.4s ease, background 0.4s ease;
            border-radius: 4px;
        }

        .strength-text {
            font-size: 11px;
            margin-top: 6px;
            color: #7a8f85;
            transition: color 0.3s ease;
        }

        .password-mismatch {
            border-color: #dc3545 !important;
            background: #fff5f5 !important;
        }

        .password-match {
            border-color: #28a745 !important;
            background: #f0fff4 !important;
        }

        .field-error {
            color: #dc3545;
            font-size: 11px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .terms-group {
            margin: 18px 0 12px;
            background: #fafdfb;
            padding: 14px 18px;
            border-radius: 28px;
            border: 1px solid #e9f0ec;
            transition: all 0.3s ease;
        }

        .terms-group:hover {
            border-color: #166534;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            cursor: pointer;
        }

        .checkbox-wrapper input {
            width: 20px;
            height: 20px;
            margin-top: 2px;
            accent-color: #166534;
            cursor: pointer;
            flex-shrink: 0;
        }

        .checkbox-wrapper label {
            font-size: 14px;
            font-weight: 500;
            text-transform: none;
            letter-spacing: normal;
            color: #2c4b3e;
            cursor: pointer;
            line-height: 1.4;
        }

        .terms-link {
            color: #166534;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.3s ease;
        }

        .terms-link:hover {
            color: #0f5c3a;
            text-decoration: underline;
        }

        .terms-error {
            font-size: 11px;
            margin-top: 6px;
            color: #dc3545;
            display: flex;
            align-items: center;
            gap: 6px;
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
            margin-top: 18px;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -10px #166534;
            background: linear-gradient(105deg, #0a4a2e, #166534);
        }

        button[type="submit"]:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .login-link {
            text-align: center;
            margin-top: 22px;
            color: #6b7c6f;
            font-size: 15px;
        }

        .login-link a {
            color: #166534;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
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

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(8px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            max-width: 600px;
            width: 90%;
            border-radius: 32px;
            padding: 35px 30px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 30px 50px rgba(0,0,0,0.3);
            animation: modalPop 0.3s ease-out;
        }

        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-content h3 {
            color: #1a3b2f;
            font-size: 24px;
            margin-bottom: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .modal-content h3 i {
            color: #166534;
        }

        .modal-content p {
            color: #334e42;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 14px;
            padding-left: 12px;
            border-left: 3px solid #166534;
            padding-left: 15px;
        }

        .close-modal {
            background: #166534;
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 18px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            background: #0f5c3a;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(22, 101, 52, 0.3);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            body {
                flex-direction: column;
            }
            .register-image {
                min-height: 280px;
                padding: 30px;
            }
            .register-image-content h1 {
                font-size: 2.2rem;
            }
            .register-image-content p {
                font-size: 1rem;
            }
            .register-form-wrapper {
                min-height: auto;
                padding: 30px 20px;
            }
            .register-container {
                max-width: 100%;
            }
        }

        @media (max-width: 600px) {
            .input-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
            .register-image-content .badge-group span {
                font-size: 0.7rem;
                padding: 5px 14px;
            }
            .register-header h2 {
                font-size: 1.4rem;
            }
            .register-container {
                padding: 0 5px;
            }
            .register-image-content h1 {
                font-size: 1.8rem;
            }
            .modal-content {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>

    <!-- ===== LEFT SIDE: IMAGE ===== -->
    <div class="register-image">
        <div class="register-image-content">
            <h1>Barangay Sto. Niño</h1>
            <p>Digital, transparent, and hassle-free document processing and event permit management for the residents of Barangay Sto. Niño, Parañaque City.</p>
            <div class="badge-group">
                <span><i class="fas fa-check-circle"></i> Real-time tracking</span>
                <span><i class="fas fa-shield-alt"></i> Secure processing</span>
                <span><i class="fas fa-envelope"></i> Notifications</span>
            </div>
        </div>
    </div>

    <!-- ===== RIGHT SIDE: FORM ===== -->
    <div class="register-form-wrapper">
        <div class="register-container">
            <div class="register-header">
                <img src="../images/logo.jpg" alt="Logo" class="logo-img" onerror="this.src='https://via.placeholder.com/80?text=Logo'">
                <h2>Resident Registration</h2>
                <p>Create your Barangay Sto. Niño account</p>
            </div>

            <form method="POST" action="">
                <div class="input-row">
                    <div class="input-group">
                        <label><i class="fas fa-user"></i> First Name</label>
                        <input type="text" name="first_name" placeholder="Enter first name" required>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-user"></i> Last Name</label>
                        <input type="text" name="last_name" placeholder="Enter last name" required>
                    </div>
                </div>

                <div class="input-group">
                    <label><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" name="email" placeholder="resident@stonino.gov.ph" required>
                </div>

                <div class="input-group">
                    <label><i class="fas fa-lock"></i> Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" placeholder="Create a strong password" required>
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="strength-text" id="strengthText"></div>
                </div>

                <div class="input-group">
                    <label><i class="fas fa-check-circle"></i> Confirm Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm your password" required>
                        <button type="button" class="password-toggle" id="toggleConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="confirm-error" class="field-error"></div>
                </div>

                <div class="input-row">
                    <div class="input-group">
                        <label><i class="fas fa-phone"></i> Contact Number</label>
                        <input type="tel" name="contact_number" placeholder="0912 345 6789">
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-calendar"></i> Birthdate</label>
                        <input type="date" name="birthdate">
                    </div>
                </div>

                <div class="input-group">
                    <label><i class="fas fa-home"></i> Complete Address</label>
                    <textarea name="address" rows="3" placeholder="House/Unit, Street, Barangay, City"></textarea>
                </div>

                <div class="terms-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="termsCheckbox" name="terms" value="1">
                        <label for="termsCheckbox">
                            I have read and agree to the <a href="#" id="viewTermsLink" class="terms-link">Terms and Conditions</a>
                        </label>
                    </div>
                    <div id="terms-error" class="terms-error"></div>
                </div>

                <button type="submit" id="submitBtn">
                    <i class="fas fa-user-plus"></i> Register Account
                </button>
            </form>

            <div class="login-link">
                Already have an account? <a href="../index.php">Sign in here</a>
            </div>

            <?php if($error): ?>
                <div class="error-box"><i class="fas fa-exclamation-triangle"></i> <?= $error ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===== TERMS MODAL ===== -->
    <div id="termsModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-file-contract"></i> Terms and Conditions</h3>
            <p><strong>1. Acceptance of Terms</strong><br>By registering for an account on the Barangay Sto. Niño Online Services, you agree to comply with these terms and all applicable local laws and regulations.</p>
            <p><strong>2. Purpose of System</strong><br>This platform is for legitimate document requests, permit applications, tracking, and communication with the Barangay Sto. Niño office.</p>
            <p><strong>3. User Responsibilities</strong><br>You are responsible for maintaining the confidentiality of your account credentials. False or misleading information is prohibited.</p>
            <p><strong>4. Data Privacy</strong><br>Your personal information will be processed in accordance with the Data Privacy Act of 2012 (RA 10173).</p>
            <p><strong>5. Accuracy of Information</strong><br>All information provided must be truthful, complete, and up-to-date. The Barangay reserves the right to verify details.</p>
            <p><strong>6. Account Security</strong><br>You agree to notify Barangay Sto. Niño immediately of any unauthorized use of your account.</p>
            <p><strong>7. Amendments</strong><br>Barangay Sto. Niño reserves the right to modify these terms at any time.</p>
            <p><strong>8. Governing Law</strong><br>These terms are governed by the laws of the Republic of the Philippines.</p>
            <button class="close-modal" id="closeModalBtn"><i class="fas fa-check"></i> I Understand</button>
        </div>
    </div>

    <script>
        // ===== PASSWORD TOGGLE =====
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        const toggleConfirm = document.getElementById('toggleConfirmPassword');
        const confirmPassword = document.getElementById('confirm_password');
        
        toggleConfirm.addEventListener('click', function() {
            const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPassword.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        // ===== PASSWORD STRENGTH =====
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        password.addEventListener('input', function() {
            const pwd = this.value;
            let strength = 0;
            
            if (pwd.length === 0) {
                strengthBar.style.width = '0%';
                strengthBar.style.background = '#e2e9ef';
                strengthText.textContent = '';
                return;
            }
            
            if (pwd.length >= 6) strength++;
            if (pwd.length >= 10) strength++;
            if (/[A-Z]/.test(pwd)) strength++;
            if (/[0-9]/.test(pwd)) strength++;
            if (/[^A-Za-z0-9]/.test(pwd)) strength++;
            
            let percentage = (strength / 5) * 100;
            let color = '';
            let message = '';
            
            if (strength <= 1) {
                color = '#dc3545';
                message = 'Weak password';
            } else if (strength <= 3) {
                color = '#f0ad4e';
                message = 'Fair password';
            } else {
                color = '#28a745';
                message = 'Strong password!';
            }
            
            strengthBar.style.width = percentage + '%';
            strengthBar.style.background = color;
            strengthText.textContent = message;
            strengthText.style.color = color;
        });

        // ===== CONFIRM PASSWORD MATCH =====
        const confirmError = document.getElementById('confirm-error');

        confirmPassword.addEventListener('input', function() {
            const pwd = password.value;
            const confirm = this.value;
            
            if (confirm.length === 0) {
                this.classList.remove('password-mismatch', 'password-match');
                confirmError.innerHTML = '';
                return;
            }
            
            if (pwd === confirm) {
                this.classList.remove('password-mismatch');
                this.classList.add('password-match');
                confirmError.innerHTML = '<i class="fas fa-check-circle" style="color:#28a745;"></i> Passwords match';
                confirmError.style.color = '#28a745';
            } else {
                this.classList.remove('password-match');
                this.classList.add('password-mismatch');
                confirmError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Passwords do not match';
                confirmError.style.color = '#dc3545';
            }
        });

        // ===== TERMS MODAL =====
        const modal = document.getElementById('termsModal');
        const viewTermsLink = document.getElementById('viewTermsLink');
        const closeModalBtn = document.getElementById('closeModalBtn');

        viewTermsLink.addEventListener('click', function(e) {
            e.preventDefault();
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });

        function closeModal() {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        closeModalBtn.addEventListener('click', closeModal);
        window.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });

        // ===== TERMS CHECKBOX =====
        const termsCheckbox = document.getElementById('termsCheckbox');
        const termsError = document.getElementById('terms-error');

        document.querySelector('form').addEventListener('submit', function(e) {
            if (!termsCheckbox.checked) {
                e.preventDefault();
                termsError.innerHTML = '<i class="fas fa-exclamation-circle"></i> You must accept the Terms and Conditions to register';
            } else {
                termsError.innerHTML = '';
            }
        });
    </script>
</body>
</html>