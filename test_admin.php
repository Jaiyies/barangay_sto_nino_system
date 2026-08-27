<?php
// test_admin.php - DIRECT ADMIN TEST
require_once 'config/database.php';
session_start();

$message = '';
$user_data = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $conn = $db->getConnection();
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $user_data = $user;
        $password_verify = password_verify($password, $user['password']);
        
        if ($password_verify) {
            $message = "✅ PASSWORD VERIFIED! Login would work!";
            $_SESSION['test_user_id'] = $user['user_id'];
            $_SESSION['test_email'] = $user['email'];
            $_SESSION['test_full_name'] = $user['full_name'];
            $_SESSION['test_role'] = $user['role'];
        } else {
            $message = "❌ Password verification FAILED!";
        }
    } else {
        $message = "❌ User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Test Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        <h4>🔧 Admin Login Test</h4>
                    </div>
                    <div class="card-body">
                        <?php if($message): ?>
                            <div class="alert alert-<?= strpos($message, '✅') !== false ? 'success' : 'danger' ?>">
                                <?= $message ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($user_data && $password_verify ?? false): ?>
                            <div class="alert alert-success">
                                <strong>✅ User Data:</strong><br>
                                Email: <?= $user_data['email'] ?><br>
                                Role: <?= $user_data['role'] ?><br>
                                Full Name: <?= $user_data['full_name'] ?><br>
                                Password Hash: <?= $user_data['password'] ?><br>
                                <br>
                                <a href="admin/dashboard.php" class="btn btn-primary">Go to Admin Dashboard →</a>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" placeholder="admin@barangay.gov.ph" required>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" placeholder="admin123" required>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">Test Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>