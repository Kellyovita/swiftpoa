<?php
// staff.php - Staff Dashboard

session_start();

// If staff not logged in, redirect to login
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/includes/db.php';
$conn = db();

// Helper to safely get int count or 0
function get_count($conn, $sql) {
    $res = $conn->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
        return (int)$row['total'];
    }
    return 0;
}

// Count pending orders
$pendingOrders = get_count($conn, "SELECT COUNT(*) AS total FROM orders WHERE status = 'pending'");

// Count messages
$messages = get_count($conn, "SELECT COUNT(*) AS total FROM messages");

// Count payments
$payments = get_count($conn, "SELECT COUNT(*) AS total FROM payments");

// Determine staff display name safely
$staffDisplay = 'Staff';
if (!empty($_SESSION['staff'])) {
    $staffDisplay = $_SESSION['staff'];
} elseif (!empty($_SESSION['name'])) {
    $staffDisplay = $_SESSION['name'];
} elseif (!empty($_SESSION['email'])) {
    $staffDisplay = $_SESSION['email'];
}

// Escape for output
$staffDisplayEsc = htmlspecialchars($staffDisplay, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>SwiftPOA Staff Dashboard</title>
<style>
/* Reset */
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

/* Main Content */
.main-content { flex: 1; background: #f7f7f7; padding: 20px 30px; overflow-y: auto; }
.main-content header h1 { color: #333; margin-bottom: 20px; }

/* Cards */
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.card {
    background: #ffb400;
    color: #000;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.2);
    text-align: center;
    transition: 0.3s;
}
.card:hover { transform: translateY(-5px); }

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
            <li><a href="staff.php" class="active">Dashboard</a></li>
            <li><a href="staff_orders.php">Orders Management</a></li>
            <!-- <li><a href="fees.php">Transportation Fee</a></li> -->
            <li><a href="payments.php">Payments</a></li>
            <li><a href="staff_message.php">Messages</a></li>
            <li><a href="staff_profile.php">Profile</a></li>
            <li><a href="logout.php" class="logout">Logout</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header>
            <h1>Welcome, <?php echo $staffDisplayEsc; ?></h1>
        </header>

        <!-- Staff Cards -->
        <div class="cards">
            <div class="card">
                <a href="orders.php"><h3>Pending Orders</h3></a>
                <p><?php echo (int)$pendingOrders; ?></p>
            </div>
            <div class="card">
                <a href="message.php"><h3>Messages</h3></a>
                <p><?php echo (int)$messages; ?></p>
            </div>
            <div class="card">
                <a href="payments.php"><h3>Payments</h3></a>
                <p><?php echo (int)$payments; ?></p>
            </div>
            <div class="card">
                <a href="fees.php"><h3>Transport Fees</h3></a>
                <p>Manage</p>
            </div>
        </div>
    </main>
</div>
</body>
</html>
