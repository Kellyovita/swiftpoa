<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admins') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SwiftPOA Admin Dashboard</title>
<style>
/* Reset */
* { box-sizing: border-box; margin:0; padding:0; font-family: Arial, sans-serif; }

/* Layout */
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

/* Tables */
.table-section {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.1);
}
.table-section h2 { margin-bottom: 15px; color: #333; }
table { width: 100%; border-collapse: collapse; }
table th, table td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
table th { background: #000; color: #ffb400; }
table tr:hover { background: #f1f1f1; }

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
            <h3>Administrator</h3>
        </div>
        <ul class="menu">
    <li><a href="dashboard.php" class="active">Dashboard</a></li>
    <li><a href="vendors.php">Vendors</a></li>
    <li><a href="riders.php">Riders</a></li>
    <li><a href="staffs.php">Staff</a></li>
    <li><a href="admin_payments.php" class="active">Payments</a></li>
    <li><a href="orders.php">Orders</a></li>
    <li><a href="stats.php">Statistics</a></li>
    <li><a href="admin_message.php">Messages</a></li>
    <li><a href="blogs.php">Blogs</a></li>
    <li><a href="admin_profile.php">Profile</a></li>
    <li><a href="login.php" class="logout">Logout</a></li>
</ul>

    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header>
            <h1>
                Welcome, 
                <?php 
                if (!empty($_SESSION['name'])) {
                    echo htmlspecialchars($_SESSION['name']);
                } elseif (!empty($_SESSION['email'])) {
                    echo htmlspecialchars($_SESSION['email']);
                } else {
                    echo "Admin";
                }
                ?>
            </h1>
        </header>

        <!-- Dashboard Cards -->
<?php
require_once __DIR__ . '/includes/db.php'; 
$conn = db();

// Count vendors
$result = $conn->query("SELECT COUNT(*) AS total FROM vendors");
$vendors = $result->fetch_assoc()['total'];

// Count riders
$result = $conn->query("SELECT COUNT(*) AS total FROM riders");
$riders = $result->fetch_assoc()['total'];

// Count staff
$result = $conn->query("SELECT COUNT(*) AS total FROM staff");
$staffs = $result->fetch_assoc()['total'];

// Count pending orders
$result = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status = 'pending'");
$pendingOrders = $result->fetch_assoc()['total'];
?>

<div class="cards">
    <div class="card">
        <a href="vendors.php"><h3>Total Vendors</h3></a>
        <p><?php echo $vendors; ?></p>
    </div>
    <div class="card">
        <a href="riders.php"><h3>Total Riders</h3></a>
        <p><?php echo $riders; ?></p>
    </div>
    <div class="card">
        <a href="staffs.php"><h3>Total Staff</h3></a>
        <p><?php echo $staffs; ?></p>
    </div>
    <div class="card">
        <a href="orders.php"><h3>Pending Orders</h3></a>
        <p><?php echo $pendingOrders; ?></p>
    </div>
</div>

    </main>
</div>
</body>
</html>
