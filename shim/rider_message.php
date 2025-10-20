<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$conn = db();

// Check if rider is logged in
if (!isset($_SESSION['rider_id'])) {
    die("<p style='color:red;text-align:center;'>Rider not logged in. Please log in first.</p>");
}

$rider_id = $_SESSION['rider_id'];

// Fetch all messages sent to this rider
$stmt = $conn->prepare("
    SELECT m.id, m.message, m.status, m.order_id, m.created_at,
           c.name AS customer_name, c.phone AS customer_phone
    FROM messages m
    LEFT JOIN customers c ON m.customer_id = c.id
    WHERE m.receiver_id = ?
    ORDER BY m.created_at DESC
");
$stmt->bind_param("i", $rider_id);
$stmt->execute();
$messages = $stmt->get_result();

// Mark all unread messages as read
$conn->query("UPDATE messages SET status = 'read' WHERE receiver_id = $rider_id AND status = 'unread'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rider Messages - SwiftPOA</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* General styling */
body {
    font-family: 'Poppins', sans-serif;
    background: #f7f7f7;
    margin: 0;
    padding: 0;
}

/* Dashboard layout */
.dashboard {
    display: flex;
    height: 100vh;
    overflow: hidden;
}

/* Sidebar */
.sidebar {
    width: 220px;
    background: #000;
    color: #fff;
    display: flex;
    flex-direction: column;
    padding-top: 20px;
}
.sidebar .logo {
    text-align: center;
    margin-bottom: 30px;
}
.sidebar .logo img {
    width: 100px;
    margin-bottom: 10px;
}
.sidebar .logo h2 {
    color: #ffb400;
}
.sidebar .menu {
    list-style: none;
    padding-left: 0;
}
.sidebar .menu li {
    margin: 15px 0;
}
.sidebar .menu li a {
    text-decoration: none;
    color: #fff;
    padding: 10px 20px;
    display: block;
    border-radius: 5px;
    transition: 0.3s;
}
.sidebar .menu li a:hover,
.sidebar .menu li a.active {
    background: #ffb400;
    color: #000;
}
.sidebar .menu li a.logout {
    margin-top: 50px;
    background: #ff4d4d;
}
.sidebar .menu li a.logout:hover {
    background: #cc0000;
}

/* Main content */
.main-content {
    flex: 1;
    background: #fff;
    padding: 20px 30px;
    overflow-y: auto;
}
.main-content h1 {
    color: #333;
    margin-bottom: 20px;
}

/* Messages Table */
table {
    width: 100%;
    border-collapse: collapse;
    background: #fafafa;
    border-radius: 10px;
    overflow: hidden;
}
th, td {
    padding: 12px 15px;
    text-align: left;
}
th {
    background: #ffb400;
    color: #000;
    font-weight: bold;
}
tr:nth-child(even) {
    background: #f2f2f2;
}
tr:hover {
    background: #fff3cd;
}

/* Status styling */
.status-unread {
    color: red;
    font-weight: bold;
}
.status-read {
    color: green;
}

/* Responsive */
@media(max-width:768px){
    .dashboard {
        flex-direction: column;
    }
    .sidebar {
        width: 100%;
        flex-direction: row;
        overflow-x: auto;
    }
    .sidebar .menu {
        display: flex;
        flex-direction: row;
    }
    .sidebar .menu li {
        margin: 0 10px;
    }
    .main-content {
        padding: 10px;
    }
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
            <h3>Rider</h3>
        </div>
        <ul class="menu">
            <li><a href="rider.php">Dashboard</a></li>
            <li><a href="assigned_orders.php">My Orders</a></li>
            <li><a href="rider_payment.php">My Payments</a></li>
            <li><a href="rider_message.php" class="active">My Messages</a></li>
            <li><a href="rider_profile.php">Profile</a></li>
            <li><a href="logout.php" class="logout">Logout</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <h1><i class="fas fa-envelope"></i> My Messages</h1>

        <?php if ($messages->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>From</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $messages->fetch_assoc()): ?>
                <tr>
                    <td>#<?= htmlspecialchars($row['order_id']) ?></td>
                    <td><?= htmlspecialchars($row['customer_name'] ?? 'System') ?> (<?= htmlspecialchars($row['customer_phone'] ?? 'N/A') ?>)</td>
                    <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                    <td class="<?= $row['status'] === 'unread' ? 'status-unread' : 'status-read' ?>">
                        <?= ucfirst($row['status']) ?>
                    </td>
                    <td><?= date("d M Y, h:i A", strtotime($row['created_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p style="text-align:center;color:gray;">No messages found.</p>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
