<?php
// resident/event_permit.php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

$success = '';
$error = '';

// ===== HANDLE PERMIT APPLICATION =====
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_name = trim($_POST['event_name']);
    $event_type = trim($_POST['event_type']);
    $event_description = trim($_POST['event_description']);
    $event_date = $_POST['event_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $venue = trim($_POST['venue']);
    $expected_attendees = intval($_POST['expected_attendees']);

    // ===== VALIDATE TIME RULES =====
    $startHour = intval(date('H', strtotime($start_time)));
    $endHour = intval(date('H', strtotime($end_time)));

    // Rule 1: Start time must be at least 8 AM
    if ($startHour < 8) {
        $error = "❌ Event cannot start before 8:00 AM.";
    }
    // Rule 2: End time must be at most 10 PM
    elseif ($endHour > 22) {
        $error = "❌ Event cannot end after 10:00 PM.";
    }
    // Rule 3: End time must be after start time
    elseif (strtotime($end_time) <= strtotime($start_time)) {
        $error = "❌ End time must be after start time.";
    }
    else {
        // ===== GENERATE PERMIT NUMBER =====
        $permit_number = 'EVT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        // ===== INSERT QUERY =====
        $query = "INSERT INTO event_permits 
                  (user_id, event_name, event_type, event_description, event_date, event_time, end_time, venue, expected_attendees, permit_number, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($query);

        if ($stmt->execute([$user_id, $event_name, $event_type, $event_description, 
                           $event_date, $start_time, $end_time, $venue, $expected_attendees, $permit_number])) {
            $success = "✅ Event permit application submitted! Permit Number: " . $permit_number;
        } else {
            $error = "❌ Failed to submit application. Please try again.";
        }
    }
}

// ===== GET USER'S PERMITS =====
$permitQuery = "SELECT * FROM event_permits WHERE user_id = ? ORDER BY application_date DESC";
$permitStmt = $conn->prepare($permitQuery);
$permitStmt->execute([$user_id]);
$permits = $permitStmt->fetchAll(PDO::FETCH_ASSOC);

// ===== GET EVENT TYPES =====
$event_types = [
    'community_festival' => 'Community Festival',
    'sports_event' => 'Sports Event',
    'cultural_event' => 'Cultural Event',
    'religious_event' => 'Religious Event',
    'fundraising' => 'Fundraising',
    'educational' => 'Educational Event',
    'others' => 'Others'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Permit | Barangay Sto. Niño</title>
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
            background: #f0f5f2;
            min-height: 100vh;
            display: flex;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
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

        .sidebar .logo-text .sos-text {
            display: block;
            font-size: 3rem;
            font-weight: 800;
            color: #c9a84c;
            letter-spacing: 4px;
            line-height: 1.1;
        }

        .sidebar .logo-text .barangay-text {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            color: #a0c0b0;
            letter-spacing: 4px;
        }

        .sidebar h1 {
            font-size: 2rem;
            font-weight: 700;
            margin: 20px 0 10px;
        }

        .sidebar p {
            font-size: 1rem;
            opacity: 0.85;
            max-width: 350px;
            line-height: 1.6;
        }

        .sidebar .user-badge {
            margin-top: 30px;
            background: rgba(255, 255, 255, 0.1);
            padding: 12px 30px;
            border-radius: 60px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-weight: 600;
        }

        .sidebar .user-badge i {
            color: #c9a84c;
            margin-right: 10px;
        }

        .sidebar .back-link {
            margin-top: 25px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .sidebar .back-link:hover {
            color: white;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            flex: 0.9;
            background: white;
            padding: 40px 45px;
            min-height: 100vh;
            overflow-y: auto;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9f0ec;
        }

        .top-bar h2 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #0b4127;
        }

        .top-bar h2 span {
            color: #8B0000;
        }

        .top-bar .logout-btn {
            background: transparent;
            border: 1.5px solid #dc3545;
            color: #dc3545;
            padding: 10px 22px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .top-bar .logout-btn:hover {
            background: #dc3545;
            color: white;
        }

        /* ===== FORM ===== */
        .form-card {
            background: #fafdfb;
            border-radius: 28px;
            padding: 30px 35px;
            border: 1px solid #e9f0ec;
            margin-bottom: 40px;
        }

        .form-card h3 {
            font-weight: 700;
            color: #0b4127;
            margin-bottom: 20px;
            font-size: 1.2rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #2c4b3e;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .form-group label i {
            color: #166534;
            margin-right: 6px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 18px;
            border: 1.5px solid #e2e9ef;
            border-radius: 28px;
            font-size: 0.95rem;
            font-family: inherit;
            background: white;
            transition: all 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #166534;
            box-shadow: 0 0 0 4px rgba(22, 101, 52, 0.08);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .time-note {
            font-size: 0.75rem;
            color: #6b7c6f;
            margin-top: 4px;
        }

        .time-note i {
            color: #166534;
        }

        .btn-submit {
            background: linear-gradient(105deg, #0f5c3a, #1f8a5c);
            color: white;
            border: none;
            padding: 14px 35px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -10px #166534;
        }

        /* ===== PERMITS TABLE ===== */
        .table-card {
            background: #fafdfb;
            border-radius: 28px;
            padding: 25px 30px;
            border: 1px solid #e9f0ec;
        }

        .table-card h3 {
            font-weight: 700;
            color: #0b4127;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        thead {
            background: #e9f0ec;
            border-radius: 16px;
        }

        thead th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #1a3b2f;
        }

        tbody td {
            padding: 12px 15px;
            border-bottom: 1px solid #e9f0ec;
        }

        .badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-approved { background: #d1fae5; color: #065f46; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .badge-completed { background: #dbeafe; color: #1e40af; }

        .empty-state {
            text-align: center;
            padding: 30px;
            color: #6b7c6f;
        }

        .empty-state i {
            font-size: 2.5rem;
            color: #c9d5cf;
            margin-bottom: 10px;
        }

        /* ===== ALERTS ===== */
        .alert {
            padding: 14px 20px;
            border-radius: 28px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #166534;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #dc3545;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            body {
                flex-direction: column;
            }
            .sidebar {
                min-height: 200px;
                padding: 30px;
            }
            .sidebar h1 {
                font-size: 1.6rem;
            }
            .sidebar .logo-text .sos-text {
                font-size: 2.2rem;
            }
            .main-content {
                padding: 25px;
            }
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }

        @media (max-width: 600px) {
            .main-content {
                padding: 20px;
            }
            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .form-card {
                padding: 20px;
            }
            .table-card {
                padding: 20px;
            }
            table {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>

    <!-- ===== SIDEBAR ===== -->
    <div class="sidebar">
        <div class="logo-text">
            <strong class="sos-text">S.O.S.</strong>
            <small class="barangay-text">BARANGAY</small>
        </div>
        <h1>Event Permit</h1>
        <p>Apply for community event permits and track your applications.</p>
        <div class="user-badge">
            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['full_name']) ?>
        </div>
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h2><span>Sto.</span> Niño Event Permit</h2>
            <a href="../auth/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- Alerts -->
        <?php if($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <!-- ===== APPLY FORM ===== -->
        <div class="form-card">
            <h3><i class="fas fa-pen-to-square" style="color: #166534;"></i> Apply for Event Permit</h3>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Event Name</label>
                        <input type="text" name="event_name" placeholder="e.g., Barangay Fiesta 2026" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-list"></i> Event Type</label>
                        <select name="event_type" required>
                            <option value="">Select Type</option>
                            <?php foreach($event_types as $key => $value): ?>
                                <option value="<?= $key ?>"><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Event Description</label>
                    <textarea name="event_description" placeholder="Brief description of the event" rows="3"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-calendar-day"></i> Event Date</label>
                        <input type="date" name="event_date" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Start Time</label>
                        <input type="time" name="start_time" required>
                        <div class="time-note"><i class="fas fa-info-circle"></i> Earliest: 8:00 AM</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> End Time</label>
                        <input type="time" name="end_time" required>
                        <div class="time-note"><i class="fas fa-info-circle"></i> Latest: 10:00 PM</div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-map-pin"></i> Venue</label>
                        <input type="text" name="venue" placeholder="e.g., Barangay Covered Court" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-users"></i> Expected Attendees</label>
                    <input type="number" name="expected_attendees" placeholder="Estimated number" min="1">
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Submit Application
                </button>
            </form>
        </div>

        <!-- ===== MY PERMITS ===== -->
        <div class="table-card">
            <h3><i class="fas fa-list-check" style="color: #166534;"></i> My Permits</h3>

            <?php if(count($permits) > 0): ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Permit #</th>
                                <th>Event Name</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Venue</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($permits as $permit): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($permit['permit_number']) ?></strong></td>
                                    <td><?= htmlspecialchars($permit['event_name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($permit['event_date'])) ?></td>
                                    <td>
                                        <?= date('g:i A', strtotime($permit['event_time'])) ?> - 
                                        <?= date('g:i A', strtotime($permit['end_time'])) ?>
                                    </td>
                                    <td><?= htmlspecialchars($permit['venue']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $permit['status'] ?>">
                                            <?= ucfirst($permit['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-plus"></i>
                    <p>You haven't applied for any event permits yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>