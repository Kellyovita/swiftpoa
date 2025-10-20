<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$conn = db();

// ✅ Ensure vendor logged in
if (!isset($_SESSION['vendor_id'])) {
    header("Location: login.php");
    exit;
}

$vendorId   = (int) $_SESSION['vendor_id'];
$vendorName = $_SESSION['vendor_name'] ?? "Vendor";

// Helper: safely count
function countQuery($conn, $sql, $vendorId) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) return 0;
    $stmt->bind_param("i", $vendorId);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['total'] ?? 0;
    $stmt->close();
    return $count;
}

// Fetch counts
$myOrders = countQuery($conn, "SELECT COUNT(*) AS total FROM orders WHERE vendor_id = ?", $vendorId);
$pendingOrders = countQuery($conn, "SELECT COUNT(*) AS total FROM orders WHERE vendor_id = ? AND status = 'pending'", $vendorId);
$assignedOrders = countQuery($conn, "SELECT COUNT(*) AS total FROM orders WHERE vendor_id = ? AND status = 'assigned'", $vendorId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SwiftPOA Vendor Dashboard</title>
<style>
* { box-sizing: border-box; margin:0; padding:0; font-family: Arial, sans-serif; }

.dashboard { display: flex; height: 100vh; overflow: hidden; }

.sidebar {
    width: 220px; background: #000; color: #fff;
    display: flex; flex-direction: column; padding-top: 20px;
}
.sidebar .logo { text-align: center; margin-bottom: 30px; }
.sidebar .logo img { width: 100px; margin-bottom: 10px; }
.sidebar .logo h2 { color: #ffb400; }
.sidebar .menu { list-style: none; padding-left: 0; }
.sidebar .menu li { margin: 15px 0; }
.sidebar .menu li a {
    text-decoration: none; color: #fff;
    padding: 10px 20px; display: block;
    border-radius: 5px; transition: 0.3s;
}
.sidebar .menu li a:hover, 
.sidebar .menu li a.active { background: #ffb400; color: #000; }
.sidebar .menu li a.logout { margin-top: 50px; background: #ff4d4d; }
.sidebar .menu li a.logout:hover { background: #cc0000; }

.main-content { flex: 1; background: #f7f7f7; padding: 20px 30px; overflow-y: auto; }
.main-content header h1 { color: #333; margin-bottom: 20px; }

.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px; margin-bottom: 30px;
}
.card {
    background: #ffb400; color: #000;
    padding: 20px; border-radius: 10px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.2);
    text-align: center; transition: 0.3s;
}
.card:hover { transform: translateY(-5px); }
.card a { color: inherit; text-decoration: none; }

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
            <h3>Vendor</h3>
        </div>
        <ul class="menu">
            <li><a href="vendor.php" class="active">Dashboard</a></li>
            <li><a href="vendor_orders.php">My Orders Management</a></li>
            <li><a href="request_rider.php">Request Rider</a></li>
            <!-- <li><a href="track_orders.php">Track Orders</a></li> -->
            <li><a href="vendor_profile.php">Profile</a></li>
            <li><a href="logout.php" class="logout">Logout</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header>
            <h1>Welcome, <?= htmlspecialchars($vendorName) ?></h1>
        </header>

        <div class="cards">
            <div class="card">
                <a href="vendor_orders.php"><h3>My Orders</h3></a>
                <p><?= $myOrders ?></p>
            </div>
            <div class="card">
                <a href="request_rider.php"><h3>Pending Requests</h3></a>
                <p><?= $pendingOrders ?></p>
            </div>
        
            <div class="card">
                <a href="vendor_profile.php"><h3>Profile</h3></a>
                <p>Manage</p>
            </div>
        </div>
    </main>
</div>
</body>
</html>
