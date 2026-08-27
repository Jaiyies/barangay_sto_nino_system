<?php
// resident/dashboard.php
require_once '../config/session.php';
requireLogin();

// Kunin ang pangalan ng user mula sa session
$full_name = $_SESSION['full_name'] ?? 'Resident';

// Get request counts
require_once '../config/database.php';
$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

$countQuery = "SELECT 
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
    COUNT(*) as total
FROM document_requests WHERE user_id = ?";
$stmt = $conn->prepare($countQuery);
$stmt->execute([$user_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Barangay Sto. Niño</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* RESET & BASE */
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

        /* ===== LEFT SIDE ===== */
        .dashboard-sidebar {
            flex: 1;
            min-height: 100vh;
            background: linear-gradient(145deg, #0d3b26, #1a5d3a);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: white;
            text-align: center;
        }

        .dashboard-sidebar .logo-text {
            margin-bottom: 30px;
        }

        .dashboard-sidebar .logo-text .service-text {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            color: #c9a84c;
            letter-spacing: 2px;
        }

        .dashboard-sidebar .logo-text .city-text {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #e0e0e0;
            letter-spacing: 0.5px;
        }

        .dashboard-sidebar .logo-text .sos-text {
            display: block;
            font-size: 3rem;
            font-weight: 800;
            color: #c9a84c;
            letter-spacing: 4px;
            line-height: 1.1;
        }

        .dashboard-sidebar .logo-text .barangay-text {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            color: #a0c0b0;
            letter-spacing: 4px;
        }

        .dashboard-sidebar h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .dashboard-sidebar p {
            font-size: 1rem;
            opacity: 0.85;
            max-width: 400px;
            line-height: 1.6;
        }

        .dashboard-sidebar .user-badge {
            margin-top: 30px;
            background: rgba(255, 255, 255, 0.1);
            padding: 12px 30px;
            border-radius: 60px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .dashboard-sidebar .user-badge i {
            margin-right: 10px;
            color: #c9a84c;
        }

        /* ===== RIGHT SIDE ===== */
        .dashboard-content {
            flex: 0.9;
            background: white;
            display: flex;
            flex-direction: column;
            padding: 40px 45px;
            min-height: 100vh;
        }

        .dashboard-content .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9f0ec;
        }

        .dashboard-content .top-bar h2 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #0b4127;
        }

        .dashboard-content .top-bar h2 span {
            color: #8B0000;
        }

        .dashboard-content .top-bar .logout-btn {
            background: transparent;
            border: 1.5px solid #dc3545;
            color: #dc3545;
            padding: 10px 22px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dashboard-content .top-bar .logout-btn:hover {
            background: #dc3545;
            color: white;
        }

        .greeting-section {
            margin-bottom: 35px;
        }

        .greeting-section h3 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a3b2f;
        }

        .greeting-section p {
            color: #6b7c6f;
            margin-top: 4px;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 45px;
        }

        .action-card {
            background: #fafdfb;
            border: 1px solid #e9f0ec;
            border-radius: 28px;
            padding: 25px 20px;
            text-align: center;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
            display: block;
            cursor: pointer;
        }

        .action-card:hover {
            transform: translateY(-4px);
            border-color: #166534;
            box-shadow: 0 12px 25px -10px rgba(22, 101, 52, 0.15);
        }

        .action-card i {
            font-size: 2.2rem;
            color: #166534;
            margin-bottom: 12px;
        }

        .action-card h4 {
            font-weight: 700;
            color: #0b4127;
            margin-bottom: 5px;
        }

        .action-card p {
            font-size: 0.85rem;
            color: #6b7c6f;
        }

        .requests-summary {
            background: #fafdfb;
            border-radius: 28px;
            padding: 25px 30px;
            border: 1px solid #e9f0ec;
        }

        .requests-summary h4 {
            font-weight: 700;
            color: #0b4127;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .summary-stats {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stat-item .stat-label {
            color: #6b7c6f;
            font-size: 0.9rem;
        }

        .stat-item .stat-number {
            font-weight: 700;
            font-size: 1.2rem;
            color: #0b4127;
        }

        .stat-item .stat-number.pending {
            color: #f59e0b;
        }
        .stat-item .stat-number.approved {
            color: #166534;
        }
        .stat-item .stat-number.rejected {
            color: #dc3545;
        }

        .requests-summary .no-requests {
            font-size: 0.85rem;
            color: #6b7c6f;
            margin-top: 10px;
        }

        .requests-summary .no-requests a {
            color: #166534;
            font-weight: 600;
            text-decoration: none;
        }

        .requests-summary .no-requests a:hover {
            text-decoration: underline;
        }

        @media (max-width: 992px) {
            body {
                flex-direction: column;
            }
            .dashboard-sidebar {
                min-height: 200px;
                padding: 30px;
            }
            .dashboard-sidebar h1 {
                font-size: 1.8rem;
            }
            .dashboard-sidebar .logo-text .sos-text {
                font-size: 2.2rem;
            }
            .dashboard-content {
                padding: 25px;
            }
            .quick-actions {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 600px) {
            .dashboard-content {
                padding: 20px;
            }
            .dashboard-content .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .quick-actions {
                grid-template-columns: 1fr;
            }
            .summary-stats {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>

    <!-- ===== LEFT SIDEBAR ===== -->
    <div class="dashboard-sidebar">
        <div class="logo-text">
            <small class="service-text">SERVICE TO GOD</small>
            <span class="city-text">City of Parañaque</span>
            <strong class="sos-text">S.O.S.</strong>
            <small class="barangay-text">BARANGAY</small>
        </div>
        <h1>Welcome Back!</h1>
        <p>Manage your document requests and event permits easily.</p>
        <div class="user-badge">
            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($full_name) ?>
        </div>
    </div>

    <!-- ===== RIGHT CONTENT ===== -->
    <div class="dashboard-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h2><span>Sto.</span> Niño Dashboard</h2>
            <a href="../auth/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- Greeting -->
        <div class="greeting-section">
            <h3>Hi, <?= htmlspecialchars($full_name) ?>! 👋</h3>
            <p>What would you like to do today?</p>
        </div>

        <!-- ===== QUICK ACTIONS ===== -->
        <div class="quick-actions">
            <!-- 1. REQUEST DOCUMENT -->
            <a href="request_document.php" class="action-card">
                <i class="fas fa-file-alt"></i>
                <h4>Request Document</h4>
                <p>Barangay Clearance, Indigency, Residency</p>
            </a>
            
            <!-- 2. APPLY PERMIT -->
            <a href="event_permit.php" class="action-card">
                <i class="fas fa-calendar-check"></i>
                <h4>Apply for Permit</h4>
                <p>Community event permits</p>
            </a>
            
            <!-- 3. TRACK REQUESTS -->
            <a href="track_requests.php" class="action-card">
                <i class="fas fa-search"></i>
                <h4>Track Requests</h4>
                <p>Check status of your applications</p>
            </a>
            
            <!-- 4. MY PROFILE -->
            <a href="profile.php" class="action-card">
                <i class="fas fa-user-edit"></i>
                <h4>My Profile</h4>
                <p>Update your personal information</p>
            </a>
        </div>

        <!-- ===== REQUESTS SUMMARY ===== -->
        <div class="requests-summary">
            <h4><i class="fas fa-chart-simple" style="margin-right: 10px; color: #166534;"></i> Your Requests Summary</h4>
            <div class="summary-stats">
                <div class="stat-item">
                    <span class="stat-label">Pending:</span>
                    <span class="stat-number pending"><?= $stats['pending'] ?? 0 ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Approved:</span>
                    <span class="stat-number approved"><?= $stats['approved'] ?? 0 ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Rejected:</span>
                    <span class="stat-number rejected"><?= $stats['rejected'] ?? 0 ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Total Requests:</span>
                    <span class="stat-number"><?= $stats['total'] ?? 0 ?></span>
                </div>
            </div>
            <?php if(($stats['total'] ?? 0) == 0): ?>
                <p class="no-requests">
                    <i class="fas fa-info-circle"></i> You haven't made any requests yet. 
                    <a href="request_document.php">Submit a new request →</a>
                </p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>