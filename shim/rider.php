<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$conn = db();

// Uncomment when login is active
// if (!isset($_SESSION['rider_id'])) {
//     header("Location: login.php");
//     exit;
// }

$rider_id = $_SESSION['rider_id'] ?? null;

// If no rider ID found, stop execution
if (!$rider_id) {
    die("<p style='color:red;text-align:center;'>Rider not logged in. Please log in first.</p>");
}

/* -----------------------------
   Dashboard Statistics Section
------------------------------*/

// Count assigned orders
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM orders WHERE rider_id = ? AND status = 'Assigned'");
$stmt->bind_param("i", $rider_id);
$stmt->execute();
$pendingOrders = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Count messages (no receiver_type column in table)
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM messages WHERE receiver_id = ?");
$stmt->bind_param("i", $rider_id);
$stmt->execute();
$messages = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Count payments linked to this rider
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM payments 
    INNER JOIN orders ON payments.order_id = orders.id 
    WHERE orders.rider_id = ?
");
$stmt->bind_param("i", $rider_id);
$stmt->execute();
$payments = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SwiftPOA Rider Dashboard</title>
<style>
/* ====== Global Reset ====== */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Poppins', Arial, sans-serif;
}

/* ====== Layout ====== */
.dashboard {
    display: flex;
    height: 100vh;
    overflow: hidden;
}

/* ====== Sidebar ====== */
.sidebar {
    width: 230px;
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
.sidebar .logo h3 {
    font-size: 16px;
    color: #ccc;
}
.sidebar .menu {
    list-style: none;
    padding-left: 0;
}
.sidebar .menu li {
    margin: 12px 0;
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
    margin-top: 40px;
    background: #ff4d4d;
}
.sidebar .menu li a.logout:hover {
    background: #cc0000;
}

/* ====== Main Content ====== */
.main-content {
    flex: 1;
    background: #f7f7f7;
    padding: 25px 40px;
    overflow-y: auto;
}
.main-content header h1 {
    color: #333;
    margin-bottom: 25px;
}

/* ====== Cards ====== */
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}
.card {
    background: #ffb400;
    color: #000;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.2);
    text-align: center;
    transition: 0.3s ease;
}
.card a {
    text-decoration: none;
    color: inherit;
}
.card:hover {
    transform: translateY(-6px);
}
.card h3 {
    margin-bottom: 8px;
}
.card p {
    font-size: 24px;
    font-weight: bold;
}

/* ====== Responsive ====== */
@media(max-width:768px) {
    .dashboard {
        flex-direction: column;
    }
    .sidebar {
        width: 100%;
        flex-direction: row;
        overflow-x: auto;
        justify-content: space-around;
    }
    .sidebar .menu {
        display: flex;
        flex-direction: row;
    }
    .sidebar .menu li {
        margin: 0 10px;
    }
    .main-content {
        padding: 15px;
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
            <h3>Rider Panel</h3>
        </div>
        <ul class="menu">
            <li><a href="rider.php" class="active">Dashboard</a></li>
            <li><a href="assigned_orders.php">My Orders</a></li>
            <li><a href="rider_payment.php">My Payments</a></li>
            <li><a href="rider_message.php">My Messages</a></li>
            <li><a href="rider_profile.php">Profile</a></li>
            <li><a href="logout.php" class="logout">Logout</a></li>
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
                        echo "Rider";
                    }
                ?>
            </h1>
        </header>

        <!-- Dashboard Cards -->
        <div class="cards">
            <div class="card">
                <a href="assigned_orders.php">
                    <h3>My Assigned Orders</h3>
                    <p><?php echo $pendingOrders; ?></p>
                </a>
            </div>

            <div class="card">
                <a href="rider_message.php">
                    <h3>My Messages</h3>
                    <p><?php echo $messages; ?></p>
                </a>
            </div>

            <div class="card">
                <a href="rider_payment.php">
                    <h3>Track Payments</h3>
                    <p><?php echo $payments; ?></p>
                </a>
            </div>
        </div>
    </main>
</div>
</body>
</html>
