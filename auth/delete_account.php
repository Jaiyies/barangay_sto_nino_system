<?php
// auth/delete_account.php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $password = $_POST['password'];
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get user
    $query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verify password
    if (password_verify($password, $user['password'])) {
        // Delete user (soft delete)
        $deleteQuery = "UPDATE users SET deleted_at = NOW(), status = 'inactive' WHERE user_id = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        
        if ($deleteStmt->execute([$user_id])) {
            $_SESSION['delete_success'] = "Account deleted successfully.";
            session_destroy();
            header("Location: ../index.php");
            exit();
        } else {
            $error = "Failed to delete account. Please try again.";
        }
    } else {
        $error = "Incorrect password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account | Barangay Sto. Niño</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f5f2;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .delete-card {
            background: white;
            border-radius: 32px;
            padding: 40px 35px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 30px 55px -15px rgba(0,0,0,0.2);
            border: 1px solid #eef3f0;
            text-align: center;
        }
        .delete-card .icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 15px;
        }
        .delete-card h2 {
            font-weight: 700;
            color: #0b4127;
        }
        .delete-card p {
            color: #6b7c6f;
            font-size: 0.95rem;
            margin: 10px 0 25px;
        }
        .delete-card .warning {
            background: #fef3c7;
            color: #92400e;
            padding: 12px 18px;
            border-radius: 16px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            border-left: 4px solid #f59e0b;
        }
        .form-control {
            border-radius: 28px;
            padding: 14px 20px;
            border: 1.5px solid #e2e9ef;
            font-family: 'Inter', sans-serif;
            margin-bottom: 15px;
        }
        .form-control:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 4px rgba(220,53,69,0.08);
        }
        .btn-danger {
            background: linear-gradient(105deg, #b91c1c, #c53030);
            color: white;
            padding: 15px;
            border-radius: 44px;
            font-weight: 700;
            border: none;
            width: 100%;
            transition: all 0.25s;
        }
        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 28px -12px #c53030;
        }
        .btn-secondary {
            background: #6b7c6f;
            color: white;
            padding: 15px;
            border-radius: 44px;
            font-weight: 700;
            border: none;
            width: 100%;
            transition: all 0.25s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            margin-top: 10px;
        }
        .btn-secondary:hover {
            background: #5a6b5f;
            transform: translateY(-3px);
        }
        .alert {
            border-radius: 16px;
        }
    </style>
</head>
<body>
    <div class="delete-card">
        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        <h2>Delete Account</h2>
        <p>Are you sure you want to delete your account? This action <strong>cannot be undone</strong>.</p>
        <div class="warning">
            <i class="fas fa-info-circle"></i> All your data will be permanently removed.
        </div>

        <?php if($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase" style="color:#476b5c;">Confirm Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn-danger">
                <i class="fas fa-trash-alt"></i> Delete Account
            </button>
        </form>
        <a href="<?= $_SESSION['role'] == 'head_admin' || $_SESSION['role'] == 'secondary_admin' ? '../admin/dashboard.php' : '../resident/dashboard.php' ?>" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Cancel
        </a>
    </div>
</body>
</html>