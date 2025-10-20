<?php
// session_start();
// if (!isset($_SESSION['staff_id'])) {
//     header("Location: login.html");
//     exit;
// }

require_once __DIR__ . '/includes/db.php';
$conn = db();

// Fetch messages (all system notifications)
$stmt = $conn->prepare("SELECT * FROM messages ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Notifications | SwiftPOA</title>
<style>
* { box-sizing: border-box; margin:0; padding:0; font-family: Arial, sans-serif; }

.dashboard { display: flex; height: 100vh; overflow: hidden; }

/* Sidebar */
.sidebar {
    width: 220px;
    background: #000;
    color: #fff;
    display: flex;
    flex-direction: column;
    padding-top: 20px;
}
.sidebar .logo { text-align: center; margin-bottom: 30px; }
.sidebar .logo img { width: 100px; margin-bottom: 10px; }
.sidebar .logo h2 { color: #ffb400; }
.sidebar .menu { list-style: none; padding-left: 0; }
.sidebar .menu li { margin: 15px 0; }
.sidebar .menu li a {
    text-decoration: none;
    color: #fff;
    padding: 10px 20px;
    display: block;
    border-radius: 5px;
    transition: 0.3s;
}
.sidebar .menu li a:hover, .sidebar .menu li a.active { background: #ffb400; color: #000; }
.sidebar .menu li a.logout { margin-top: 50px; background: #ff4d4d; }
.sidebar .menu li a.logout:hover { background: #cc0000; }

/* Main content */
.main-content {
    flex: 1;
    background: #f7f7f7;
    padding: 20px 30px;
    overflow-y: auto;
}
.main-content header h1 { color: #333; margin-bottom: 20px; }

.message-box {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 20px;
}
.message-box.unread { border-left: 6px solid #ffb400; }
.message-box h3 {
    color: #ffb400;
    margin-bottom: 10px;
}
.message-box p {
    color: #333;
    margin-bottom: 10px;
}
.message-box small {
    color: #666;
    font-size: 12px;
}

/* Responsive */
@media(max-width:768px){
    .dashboard { flex-direction: column; }
    .sidebar { width: 100%; flex-direction: row; overflow-x: auto; }
    .sidebar .menu { display: flex; flex-direction: row; }
    .sidebar .menu li { margin: 0 10px; }
    .main-content { padding: 10px; }
}
</style>
</head>
<body>
<div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">
            <img src="logo.jpg" alt="SwiftPOA Logo">
            <h2>SwiftPOA</h2>
        </div>
        <ul class="menu">
            <li><a href="staff.php">Dashboard</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="assign_rider.php">Assign Orders</a></li>
            <li><a href="fees.php">Transportation Fee</a></li>
            <li><a href="payments.php">Payments</a></li>
            <li><a href="staff_message.php" class="active">Messages</a></li>
            <li><a href="staff_profile.php">Profile</a></li>
            <li><a href="logout.php" class="logout">Logout</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header>
            <h1>Staff Notifications</h1>
        </header>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="message-box <?php echo $row['status'] === 'unread' ? 'unread' : ''; ?>">
                    <h3>Notification</h3>
                    <p><?php echo htmlspecialchars($row['message']); ?></p>
                    <small>🕒 <?php echo date('F j, Y, g:i a', strtotime($row['created_at'])); ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No messages available.</p>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
