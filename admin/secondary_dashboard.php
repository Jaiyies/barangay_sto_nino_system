<?php
// admin/secondary_dashboard.php - SECONDARY ADMIN ONLY
require_once '../config/session.php';
requireLogin();

// Direct check para sigurado
if ($_SESSION['role'] != 'secondary_admin') {
    header("Location: ../resident/dashboard.php");
    exit();
}

$full_name = $_SESSION['full_name'] ?? 'Admin';
$db = new Database();
$conn = $db->getConnection();

// ===== FETCH DATA (View Only) =====
$usersQuery = "SELECT * FROM users ORDER BY created_at DESC";
$usersStmt = $conn->query($usersQuery);
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

$announcementsQuery = "SELECT a.*, u.full_name FROM announcements a JOIN users u ON a.posted_by = u.user_id ORDER BY a.created_at DESC LIMIT 5";
$announcementsStmt = $conn->query($announcementsQuery);
$announcements = $announcementsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secondary Admin | Barangay Sto. Niño</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f5f2; display: flex; min-height: 100vh; }

        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #2c4b3e, #3a5f4f);
            color: white;
            padding: 30px 20px;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 100;
        }
        .sidebar .logo { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar .logo img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid #6b8f7a; padding: 2px; background: white; margin-bottom: 8px; }
        .sidebar .logo h2 { font-size: 1.1rem; font-weight: 700; color: white; }
        .sidebar .logo h2 span { color: #6b8f7a; }
        .sidebar .logo .role-badge { display: inline-block; background: #6b8f7a; color: white; padding: 2px 16px; border-radius: 20px; font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }
        .sidebar .nav-menu { list-style: none; padding: 0; }
        .sidebar .nav-menu li { margin-bottom: 4px; }
        .sidebar .nav-menu li a {
            display: flex; align-items: center; gap: 12px; padding: 10px 16px;
            color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 10px;
            transition: all 0.3s ease; font-weight: 500; font-size: 0.85rem;
        }
        .sidebar .nav-menu li a i { width: 20px; font-size: 1rem; color: #8aa89a; }
        .sidebar .nav-menu li a:hover { background: rgba(255,255,255,0.08); color: white; }
        .sidebar .nav-menu li a.active { background: rgba(107,143,122,0.2); color: white; border-left: 3px solid #6b8f7a; }
        .sidebar .nav-menu li a.active i { color: #6b8f7a; }
        .sidebar .nav-menu .menu-label { font-size: 0.6rem; text-transform: uppercase; color: rgba(255,255,255,0.3); padding: 12px 16px 6px; letter-spacing: 1px; }
        .sidebar .nav-menu li a.disabled { opacity: 0.4; cursor: not-allowed; pointer-events: none; }
        .sidebar .user-info {
            position: absolute; bottom: 20px; left: 20px; right: 20px; padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 12px;
        }
        .sidebar .user-info .avatar { width: 36px; height: 36px; border-radius: 50%; background: #6b8f7a; display: flex; align-items: center; justify-content: center; font-weight: 700; color: white; font-size: 1rem; }
        .sidebar .user-info .user-details { flex: 1; }
        .sidebar .user-info .user-details .name { font-weight: 600; font-size: 0.85rem; color: white; }
        .sidebar .user-info .user-details .role { font-size: 0.6rem; color: #8aa89a; text-transform: uppercase; }
        .sidebar .user-info .logout-btn { color: rgba(255,255,255,0.5); transition: color 0.3s ease; }
        .sidebar .user-info .logout-btn:hover { color: #dc3545; }

        .main-content { margin-left: 260px; flex: 1; padding: 25px 30px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 10px; }
        .top-bar h1 { font-size: 1.6rem; font-weight: 700; color: #0b4127; }
        .top-bar h1 span { color: #4a7c6a; }
        .top-bar .date-time { color: #6b7c6f; font-size: 0.85rem; }
        .menu-toggle { display: none; background: none; border: none; font-size: 1.4rem; color: #0b4127; cursor: pointer; }

        .alert-info {
            background: #e6f0fa; color: #1a56db; padding: 12px 18px; border-radius: 12px;
            margin-bottom: 20px; border-left: 4px solid #1a56db; font-size: 0.9rem;
        }

        .card { background: white; border-radius: 16px; padding: 20px 24px; border: 1px solid #e9f0ec; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 25px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e9f0ec; }
        .card-header h3 { font-size: 1.1rem; font-weight: 700; color: #0b4127; }
        .card-header h3 i { color: #4a7c6a; margin-right: 8px; }

        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        table thead { background: #f8fbfa; }
        table th { text-align: left; padding: 10px 12px; font-weight: 600; color: #2c4b3e; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; }
        table td { padding: 10px 12px; border-bottom: 1px solid #e9f0ec; color: #1a3b2f; }
        table tr:hover td { background: #f8fbfa; }

        .badge { padding: 3px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
        .badge-success { background: #e6f7ec; color: #166534; }
        .badge-danger { background: #fde8e8; color: #b91c1c; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #e6f0fa; color: #1a56db; }

        .restricted-badge {
            background: #fef3c7; color: #92400e; padding: 2px 12px; border-radius: 20px;
            font-size: 0.65rem; font-weight: 600;
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); width: 280px; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 20px; }
            .menu-toggle { display: block; }
        }
        @media (max-width: 480px) { .top-bar { flex-direction: column; align-items: flex-start; } }
    </style>
</head>
<body>

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar" id="sidebar">
        <div class="logo">
            <img src="../images/logo.jpg" alt="Logo" onerror="this.src='https://via.placeholder.com/60?text=Logo'">
            <h2><span>Sto.</span> Niño</h2>
            <div class="role-badge">Secondary Admin</div>
        </div>

        <ul class="nav-menu">
            <li class="menu-label">Main</li>
            <li><a href="#" class="active"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="#users"><i class="fas fa-users"></i> View Users</a></li>
            <li><a href="#announcements"><i class="fas fa-bullhorn"></i> Announcements</a></li>
            <li class="menu-label">Restricted</li>
            <li><a href="#" class="disabled"><i class="fas fa-user-shield"></i> Create Admin <span class="restricted-badge">Restricted</span></a></li>
            <li><a href="#" class="disabled"><i class="fas fa-user-tag"></i> Assign Roles <span class="restricted-badge">Restricted</span></a></li>
        </ul>

        <div class="user-info">
            <div class="avatar"><?= strtoupper(substr($full_name, 0, 1)) ?></div>
            <div class="user-details">
                <div class="name"><?= htmlspecialchars($full_name) ?></div>
                <div class="role">Secondary Admin</div>
            </div>
            <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </aside>

    <!-- ===== MAIN ===== -->
    <main class="main-content">
        <div class="top-bar">
            <div style="display:flex; align-items:center; gap:15px;">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                <h1><span>Secondary</span> Admin Dashboard</h1>
            </div>
            <div class="date-time">
                <i class="far fa-calendar-alt"></i> <?= date('F d, Y') ?> &nbsp;|&nbsp; <i class="far fa-clock"></i> <?= date('h:i A') ?>
            </div>
        </div>

        <div class="alert-info">
            <i class="fas fa-info-circle"></i> You have <strong>view-only</strong> access. Management features are restricted to Head Admin.
        </div>

        <!-- ===== USERS TABLE (VIEW ONLY) ===== -->
        <div class="card" id="users">
            <div class="card-header">
                <h3><i class="fas fa-users"></i> All Users</h3>
                <span class="badge badge-info"><?= count($users) ?> users</span>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['full_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><span class="badge badge-info"><?= $user['role'] ?></span></td>
                            <td>
                                <?php if($user['is_blocked']): ?>
                                    <span class="badge badge-danger">Blocked</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===== ANNOUNCEMENTS (VIEW ONLY) ===== -->
        <div class="card" id="announcements">
            <div class="card-header">
                <h3><i class="fas fa-bullhorn"></i> Announcements</h3>
                <span class="restricted-badge">View Only</span>
            </div>
            <?php if(count($announcements) > 0): ?>
                <?php foreach($announcements as $ann): ?>
                    <div style="padding:12px 0; border-bottom:1px solid #e9f0ec;">
                        <strong style="font-size:0.95rem;"><?= htmlspecialchars($ann['title']) ?></strong>
                        <p style="font-size:0.85rem; color:#4a5568; margin:4px 0;"><?= htmlspecialchars($ann['content']) ?></p>
                        <small style="color:#6b7c6f; font-size:0.7rem;">Posted by <?= htmlspecialchars($ann['full_name']) ?> on <?= date('M d, Y', strtotime($ann['created_at'])) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:#6b7c6f; font-size:0.85rem;">No announcements yet.</p>
            <?php endif; ?>
        </div>

        <div style="text-align:center; color:#6b7c6f; font-size:0.75rem; margin-top:20px; border-top:1px solid #e9f0ec; padding-top:15px;">
            Barangay Sto. Niño Online Services &bull; Secondary Admin Panel &bull; <?= date('Y') ?>
        </div>
    </main>

    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        menuToggle.addEventListener('click', function() { sidebar.classList.toggle('open'); });
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && e.target !== menuToggle && !menuToggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
    </script>
</body>
</html>