<?php
// test_dashboard.php
session_start();

if (!isset($_SESSION['test_user_id'])) {
    header("Location: test_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4>✅ TEST DASHBOARD</h4>
            </div>
            <div class="card-body">
                <h5>Welcome, <?= htmlspecialchars($_SESSION['test_full_name']) ?>!</h5>
                <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['test_email']) ?></p>
                <p><strong>Role:</strong> <?= htmlspecialchars($_SESSION['test_role']) ?></p>
                
                <hr>
                
                <?php if($_SESSION['test_role'] == 'head_admin' || $_SESSION['test_role'] == 'secondary_admin'): ?>
                    <div class="alert alert-success">
                        <strong>✅ Admin Access Granted!</strong> You can see admin features here.
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <strong>👤 Resident Access</strong> You can see resident features here.
                    </div>
                <?php endif; ?>
                
                <a href="test_login.php" class="btn btn-secondary">Back to Login</a>
                <a href="auth/logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>