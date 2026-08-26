<?php
// admin/dashboard.php - HEAD ADMIN ONLY
require_once '../config/session.php';
requireLogin();

// Direct check para sigurado
if ($_SESSION['role'] != 'head_admin') {
    header("Location: ../resident/dashboard.php");
    exit();
}

$full_name = $_SESSION['full_name'] ?? 'Admin';
$db = new Database();
$conn = $db->getConnection();

// ===== PROCESS POST =====
$message = '';
$messageType = '';

// Create Secondary Admin
if (isset($_POST['action']) && $_POST['action'] == 'create_secondary_admin') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $username = $email;
    
    $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check->execute([$email]);
    
    if ($check->rowCount() > 0) {
        $message = "Email already exists!";
        $messageType = 'danger';
    } else {
        $query = "INSERT INTO users (full_name, username, email, password, role) VALUES (?, ?, ?, ?, 'secondary_admin')";
        $stmt = $conn->prepare($query);
        if ($stmt->execute([$full_name, $username, $email, $password])) {
            $message = "Secondary Admin created successfully!";
            $messageType = 'success';
        } else {
            $message = "Failed to create admin.";
            $messageType = 'danger';
        }
    }
}

// Block/Unblock User
if (isset($_POST['action']) && $_POST['action'] == 'toggle_block') {
    $user_id = intval($_POST['user_id']);
    $current_status = $_POST['current_status'];
    $new_status = ($current_status == '0') ? '1' : '0';
    
    $query = "UPDATE users SET is_blocked = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt->execute([$new_status, $user_id])) {
        $message = "User status updated!";
        $messageType = 'success';
    } else {
        $message = "Failed to update user.";
        $messageType = 'danger';
    }
}

// Assign Role
if (isset($_POST['action']) && $_POST['action'] == 'assign_role') {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['new_role'];
    
    $query = "UPDATE users SET role = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt->execute([$new_role, $user_id])) {
        $message = "Role assigned successfully!";
        $messageType = 'success';
    } else {
        $message = "Failed to assign role.";
        $messageType = 'danger';
    }
}

// Post Announcement
if (isset($_POST['action']) && $_POST['action'] == 'post_announcement') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $posted_by = $_SESSION['user_id'];
    
    $query = "INSERT INTO announcements (title, content, posted_by) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    if ($stmt->execute([$title, $content, $posted_by])) {
        $message = "Announcement posted successfully!";
        $messageType = 'success';
    } else {
        $message = "Failed to post announcement.";
        $messageType = 'danger';
    }
}

// ===== FETCH DATA =====
$usersQuery = "SELECT * FROM users WHERE user_id != ? ORDER BY created_at DESC";
$usersStmt = $conn->prepare($usersQuery);
$usersStmt->execute([$_SESSION['user_id']]);
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
    <title>Head Admin | Barangay Sto. Niño</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f5f2; display: flex; min-height: 100vh; }

        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #0d3b26, #1a5d3a);
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
        .sidebar .logo img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid #c9a84c; padding: 2px; background: white; margin-bottom: 8px; }
        .sidebar .logo h2 { font-size: 1.1rem; font-weight: 700; color: white; }
        .sidebar .logo h2 span { color: #c9a84c; }
        .sidebar .logo .role-badge { display: inline-block; background: #c9a84c; color: #0d3b26; padding: 2px 16px; border-radius: 20px; font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }
        .sidebar .nav-menu { list-style: none; padding: 0; }
        .sidebar .nav-menu li { margin-bottom: 4px; }
        .sidebar .nav-menu li a {
            display: flex; align-items: center; gap: 12px; padding: 10px 16px;
            color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 10px;
            transition: all 0.3s ease; font-weight: 500; font-size: 0.85rem;
        }
        .sidebar .nav-menu li a i { width: 20px; font-size: 1rem; color: #a0c0b0; }
        .sidebar .nav-menu li a:hover { background: rgba(255,255,255,0.08); color: white; }
        .sidebar .nav-menu li a.active { background: rgba(201,168,76,0.2); color: white; border-left: 3px solid #c9a84c; }
        .sidebar .nav-menu li a.active i { color: #c9a84c; }
        .sidebar .nav-menu .menu-label { font-size: 0.6rem; text-transform: uppercase; color: rgba(255,255,255,0.3); padding: 12px 16px 6px; letter-spacing: 1px; }
        .sidebar .user-info {
            position: absolute; bottom: 20px; left: 20px; right: 20px; padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 12px;
        }
        .sidebar .user-info .avatar { width: 36px; height: 36px; border-radius: 50%; background: #c9a84c; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #0d3b26; font-size: 1rem; }
        .sidebar .user-info .user-details { flex: 1; }
        .sidebar .user-info .user-details .name { font-weight: 600; font-size: 0.85rem; color: white; }
        .sidebar .user-info .user-details .role { font-size: 0.6rem; color: #a0c0b0; text-transform: uppercase; }
        .sidebar .user-info .logout-btn { color: rgba(255,255,255,0.5); transition: color 0.3s ease; }
        .sidebar .user-info .logout-btn:hover { color: #dc3545; }

        .main-content { margin-left: 260px; flex: 1; padding: 25px 30px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 10px; }
        .top-bar h1 { font-size: 1.6rem; font-weight: 700; color: #0b4127; }
        .top-bar h1 span { color: #8B0000; }
        .top-bar .date-time { color: #6b7c6f; font-size: 0.85rem; }
        .menu-toggle { display: none; background: none; border: none; font-size: 1.4rem; color: #0b4127; cursor: pointer; }

        .alert { padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-success { background: #e6f7ec; color: #166534; border-left: 4px solid #166534; }
        .alert-danger { background: #fde8e8; color: #b91c1c; border-left: 4px solid #b91c1c; }

        .card { background: white; border-radius: 16px; padding: 20px 24px; border: 1px solid #e9f0ec; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 25px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e9f0ec; }
        .card-header h3 { font-size: 1.1rem; font-weight: 700; color: #0b4127; }
        .card-header h3 i { color: #166534; margin-right: 8px; }

        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-weight: 600; color: #2c4b3e; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%; padding: 10px 14px; border: 1.5px solid #e2e9ef; border-radius: 10px;
            font-size: 0.9rem; font-family: inherit; background: #fafdfb; transition: all 0.3s ease;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none; border-color: #166534; box-shadow: 0 0 0 3px rgba(22,101,52,0.08);
        }
        .form-group textarea { resize: vertical; min-height: 80px; }

        .btn { padding: 10px 20px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 0.85rem; }
        .btn-primary { background: #166534; color: white; }
        .btn-primary:hover { background: #0a4a2e; transform: translateY(-2px); }
        .btn-danger { background: #b91c1c; color: white; }
        .btn-danger:hover { background: #991b1b; }
        .btn-success { background: #0d9488; color: white; }
        .btn-success:hover { background: #0f766e; }
        .btn-sm { padding: 5px 12px; font-size: 0.75rem; }

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

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }

        @media (max-width: 1024px) { .grid-2 { grid-template-columns: 1fr; } }
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

    <aside class="sidebar" id="sidebar">
        <div class="logo">
            <img src="../images/logo.jpg" alt="Logo" onerror="this.src='https://via.placeholder.com/60?text=Logo'">
            <h2><span>Sto.</span> Niño</h2>
            <div class="role-badge">Head Admin</div>
        </div>

        <ul class="nav-menu">
            <li class="menu-label">Main</li>
            <li><a href="#" class="active"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="#users"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="#announcements"><i class="fas fa-bullhorn"></i> Announcements</a></li>
            <li><a href="#create-admin"><i class="fas fa-user-shield"></i> Create Admin</a></li>
            <li><a href="#assign-role"><i class="fas fa-user-tag"></i> Assign Roles</a></li>
        </ul>

        <div class="user-info">
            <div class="avatar"><?= strtoupper(substr($full_name, 0, 1)) ?></div>
            <div class="user-details">
                <div class="name"><?= htmlspecialchars($full_name) ?></div>
                <div class="role">Head Admin</div>
            </div>
            <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <div style="display:flex; align-items:center; gap:15px;">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                <h1><span>Head</span> Admin Dashboard</h1>
            </div>
            <div class="date-time">
                <i class="far fa-calendar-alt"></i> <?= date('F d, Y') ?> &nbsp;|&nbsp; <i class="far fa-clock"></i> <?= date('h:i A') ?>
            </div>
        </div>

        <?php if($message): ?>
            <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
        <?php endif; ?>

        <!-- USERS TABLE -->
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
                            <th>Actions</th>
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
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle_block">
                                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                    <input type="hidden" name="current_status" value="<?= $user['is_blocked'] ?>">
                                    <button type="submit" class="btn btn-<?= $user['is_blocked'] ? 'success' : 'danger' ?> btn-sm">
                                        <i class="fas fa-<?= $user['is_blocked'] ? 'unlock' : 'lock' ?>"></i>
                                        <?= $user['is_blocked'] ? 'Unblock' : 'Block' ?>
                                    </button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="assign_role">
                                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                    <select name="new_role" onchange="this.form.submit()" style="padding:4px 8px; border-radius:6px; border:1px solid #e2e9ef; font-size:0.75rem;">
                                        <option value="resident" <?= $user['role']=='resident'?'selected':'' ?>>Resident</option>
                                        <option value="staff" <?= $user['role']=='staff'?'selected':'' ?>>Staff</option>
                                        <option value="secondary_admin" <?= $user['role']=='secondary_admin'?'selected':'' ?>>Secondary Admin</option>
                                        <option value="head_admin" <?= $user['role']=='head_admin'?'selected':'' ?>>Head Admin</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TWO COLUMN -->
        <div class="grid-2">
            <div class="card" id="create-admin">
                <div class="card-header">
                    <h3><i class="fas fa-user-shield"></i> Create Secondary Admin</h3>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="create_secondary_admin">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" placeholder="Juan Dela Cruz" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="admin@barangay.gov.ph" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="text" name="password" placeholder="Default password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Admin</button>
                </form>
            </div>

            <div class="card" id="announcements">
                <div class="card-header">
                    <h3><i class="fas fa-bullhorn"></i> Post Announcement</h3>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="post_announcement">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" placeholder="Announcement title" required>
                    </div>
                    <div class="form-group">
                        <label>Content</label>
                        <textarea name="content" rows="3" placeholder="Write your announcement here..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Post Announcement</button>
                </form>

                <hr style="margin:20px 0; border-color:#e9f0ec;">
                <h4 style="font-size:0.9rem; color:#0b4127; margin-bottom:12px;">Recent Announcements</h4>
                <?php if(count($announcements) > 0): ?>
                    <?php foreach($announcements as $ann): ?>
                        <div style="padding:10px 0; border-bottom:1px solid #e9f0ec;">
                            <strong style="font-size:0.9rem;"><?= htmlspecialchars($ann['title']) ?></strong>
                            <p style="font-size:0.85rem; color:#4a5568; margin:4px 0;"><?= htmlspecialchars(substr($ann['content'], 0, 100)) ?>...</p>
                            <small style="color:#6b7c6f; font-size:0.7rem;">Posted by <?= htmlspecialchars($ann['full_name']) ?> on <?= date('M d, Y', strtotime($ann['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color:#6b7c6f; font-size:0.85rem;">No announcements yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div style="text-align:center; color:#6b7c6f; font-size:0.75rem; margin-top:20px; border-top:1px solid #e9f0ec; padding-top:15px;">
            Barangay Sto. Niño Online Services &bull; Head Admin Panel &bull; <?= date('Y') ?>
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