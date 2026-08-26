<?php
// test_login.php - PANG-TEST LANG NG LOGIN
require_once 'config/database.php';
session_start();

$error = '';
$debug = '';

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
        $debug = "<strong>✅ User found in database:</strong><br>";
        $debug .= "Email: " . $user['email'] . "<br>";
        $debug .= "Role: " . $user['role'] . "<br>";
        $debug .= "Full Name: " . $user['full_name'] . "<br>";
        $debug .= "Password hash: " . $user['password'] . "<br>";
        
       if ($password == $user['password']) {
            $debug .= "<strong style='color:green;'>✅ Password verified!</strong><br>";
            
            $_SESSION['test_user_id'] = $user['user_id'];
            $_SESSION['test_email'] = $user['email'];
            $_SESSION['test_full_name'] = $user['full_name'];
            $_SESSION['test_role'] = $user['role'];
            
            $debug .= "<strong style='color:blue;'>✅ Session set!</strong><br>";
            $debug .= "Redirect target: ";
            if ($user['role'] == 'head_admin' || $user['role'] == 'secondary_admin') {
                $debug .= "admin/dashboard.php";
            } else {
                $debug .= "resident/dashboard.php";
            }
            $debug .= "<br><br>";
            $debug .= "<a href='test_dashboard.php' style='display:inline-block; padding:10px 20px; background:green; color:white; text-decoration:none; border-radius:8px;'>Go to Test Dashboard →</a>";
        } else {
            $error = "❌ Password verification FAILED!";
        }
    } else {
        $error = "❌ User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4>🔧 TEST LOGIN</h4>
                    </div>
                    <div class="card-body">
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if($debug): ?>
                            <div class="alert alert-info"><?= $debug ?></div>
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
                            <button type="submit" class="btn btn-primary w-100">Test Login</button>
                        </form>
                        
                        <hr>
                        <p class="text-muted text-center small">
                            Use: admin@barangay.gov.ph / admin123
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>