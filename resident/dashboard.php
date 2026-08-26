<?php
// resident/dashboard.php
require_once '../config/session.php';
requireLogin();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand">🏛️ Barangay Sto. Niño</span>
            <span class="text-white">Welcome, <?= $_SESSION['full_name'] ?></span>
            <a href="../auth/logout.php" class="btn btn-light btn-sm">Logout</a>
        </div>
    </nav>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success">
                    <h4><i class="fas fa-check-circle"></i> Welcome to Resident Dashboard!</h4>
                    <p>This is your resident dashboard. More features coming soon!</p>
                </div>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-file-alt fa-3x text-primary"></i>
                        <h5 class="mt-3">Document Request</h5>
                        <p class="text-muted small">Request Barangay Clearance, Certificate of Indigency, Proof of Residency</p>
                        <a href="#" class="btn btn-primary btn-sm">Coming Soon</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-check fa-3x text-success"></i>
                        <h5 class="mt-3">Event Permit</h5>
                        <p class="text-muted small">Apply and monitor community event permits</p>
                        <a href="#" class="btn btn-success btn-sm">Coming Soon</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-search fa-3x text-warning"></i>
                        <h5 class="mt-3">Track Requests</h5>
                        <p class="text-muted small">Real-time tracking of your applications</p>
                        <a href="#" class="btn btn-warning btn-sm">Coming Soon</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>