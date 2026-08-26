<?php
// resident/profile.php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$db = new Database();
$conn = $db->getConnection();

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get user data
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $address = trim($_POST['address']);
    $contact_number = trim($_POST['contact_number']);
    
    // Check if password is being changed
    if (!empty($_POST['new_password'])) {
        $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
        $query = "UPDATE users SET full_name = ?, address = ?, contact_number = ?, password = ? WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $result = $stmt->execute([$full_name, $address, $contact_number, $new_password, $user_id]);
    } else {
        $query = "UPDATE users SET full_name = ?, address = ?, contact_number = ? WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $result = $stmt->execute([$full_name, $address, $contact_number, $user_id]);
    }
    
    if ($result) {
        $_SESSION['full_name'] = $full_name;
        $message = "Profile updated successfully!";
        // Refresh user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "Failed to update profile. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Barangay Sto. Niño</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f5f2;
        }
        .profile-card {
            background: white;
            border-radius: 32px;
            padding: 35px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            border: 1px solid #eef3f0;
        }
        .profile-header {
            display: flex;
            align-items: center;
            gap: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #eef3f0;
            margin-bottom: 25px;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d3b26, #1a5d3a);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            font-weight: 700;
            flex-shrink: 0;
        }
        .profile-name h3 {
            font-weight: 700;
            color: #1a3b2f;
            margin-bottom: 5px;
        }
        .profile-name p {
            color: #6b7c6f;
            margin-bottom: 0;
        }
        .badge-role {
            background: #e6f7ec;
            color: #166534;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        .form-control {
            border-radius: 28px;
            padding: 14px 20px;
            border: 1.5px solid #e2e9ef;
            font-family: 'Inter', sans-serif;
        }
        .form-control:focus {
            border-color: #166534;
            box-shadow: 0 0 0 4px rgba(22, 101, 52, 0.08);
        }
        .form-label {
            font-weight: 600;
            color: #2c4b3e;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .btn-save {
            background: linear-gradient(105deg, #0f5c3a, #1f8a5c);
            color: white;
            padding: 14px 40px;
            border: none;
            border-radius: 50px;
            font-weight: 700;
            transition: all 0.3s ease;
        }
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -10px #166534;
            color: white;
        }
        .btn-back {
            background: transparent;
            color: #166534;
            border: 2px solid #166534;
            padding: 14px 40px;
            border-radius: 50px;
            font-weight: 700;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .btn-back:hover {
            background: #166534;
            color: white;
        }
        .alert {
            border-radius: 28px;
        }
        .readonly-field {
            background: #f8faf9;
            cursor: not-allowed;
        }
        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            .profile-card {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <!-- BACK BUTTON -->
                <a href="dashboard.php" class="btn-back mb-4 d-inline-flex align-items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>

                <!-- PROFILE CARD -->
                <div class="profile-card">
                    <?php if($message): ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $message ?></div>
                    <?php endif; ?>
                    <?php if($error): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?= $error ?></div>
                    <?php endif; ?>

                    <!-- HEADER -->
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                        </div>
                        <div class="profile-name">
                            <h3><?= htmlspecialchars($user['full_name']) ?></h3>
                            <p><?= htmlspecialchars($user['email']) ?></p>
                            <span class="badge-role"><i class="fas fa-user"></i> <?= ucfirst($user['role']) ?></span>
                        </div>
                    </div>

                    <!-- FORM -->
                    <form method="POST">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-user"></i> Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" class="form-control readonly-field" value="<?= htmlspecialchars($user['email']) ?>" readonly disabled>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-phone"></i> Contact Number</label>
                                <input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($user['contact_number']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-calendar"></i> Member Since</label>
                                <input type="text" class="form-control readonly-field" value="<?= date('F d, Y', strtotime($user['created_at'])) ?>" readonly disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label"><i class="fas fa-home"></i> Address</label>
                                <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($user['address']) ?></textarea>
                            </div>
                            <div class="col-12">
                                <hr>
                                <h6 class="fw-bold text-muted"><i class="fas fa-lock"></i> Change Password</h6>
                                <p class="text-muted small">Leave blank if you don't want to change your password.</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Enter new password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" id="confirm_password" class="form-control" placeholder="Confirm new password">
                                <small id="passwordMatch" class="text-muted"></small>
                            </div>
                        </div>

                        <div class="d-flex gap-3 mt-4">
                            <button type="submit" class="btn-save flex-grow-1">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password match validation
        const newPass = document.querySelector('input[name="new_password"]');
        const confirmPass = document.getElementById('confirm_password');
        const passwordMatch = document.getElementById('passwordMatch');

        confirmPass.addEventListener('input', function() {
            if (this.value.length === 0) {
                passwordMatch.textContent = '';
                return;
            }
            if (this.value === newPass.value) {
                passwordMatch.innerHTML = '<i class="fas fa-check-circle" style="color:#28a745;"></i> Passwords match';
                passwordMatch.style.color = '#28a745';
            } else {
                passwordMatch.innerHTML = '<i class="fas fa-exclamation-circle" style="color:#dc3545;"></i> Passwords do not match';
                passwordMatch.style.color = '#dc3545';
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const newPassVal = newPass.value;
            const confirmVal = confirmPass.value;
            
            if (newPassVal !== '' && newPassVal !== confirmVal) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>