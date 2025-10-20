<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$conn = db();

// Make sure user is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['name'] ?? 'Customer';

// Pending orders for this customer
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM orders WHERE customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$pendingOrders = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Messages for this customer
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM messages WHERE customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$messages = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Payments for this customer
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM payments WHERE customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$payments = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SwiftPOA | Customer Dashboard</title>
<style>
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
  font-family: "Poppins", Arial, sans-serif;
}
body {
  background-color: #f4f4f4;
  color: #333;
}
nav {
  background: #000;
  color: #fff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 30px;
}
nav .logo {
  display: flex;
  align-items: center;
  gap: 10px;
}
nav .logo img {
  height: 40px;
}
nav .links a {
  color: #fff;
  text-decoration: none;
  margin: 0 15px;
  transition: 0.3s;
}
nav .links a:hover {
  color: #ffb400;
}
nav .logout {
  background: #ff4d4d;
  padding: 8px 15px;
  border-radius: 5px;
  color: #fff;
  text-decoration: none;
  transition: 0.3s;
}
nav .logout:hover {
  background: #cc0000;
}
.main {
  padding: 40px;
}
h1 {
  margin-bottom: 30px;
}
.cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}
.card {
  background: #ffb400;
  padding: 25px;
  border-radius: 10px;
  text-align: center;
  color: #000;
  box-shadow: 0 3px 6px rgba(0,0,0,0.2);
  transition: transform 0.3s;
}
.card:hover {
  transform: translateY(-5px);
}
.card a {
  color: #000;
  text-decoration: none;
  font-weight: bold;
}
@media(max-width:768px) {
  .cards { grid-template-columns: 1fr; }
  nav .links { display: flex; flex-wrap: wrap; justify-content: center; }
}
</style>
</head>
<body>

<nav>
  <div class="logo">
    <img src="logo.jpg" alt="SwiftPOA Logo">
    <h2>SwiftPOA</h2>
  </div>
  <div class="links">
    <a href="place_order.php" class="active">Place order</a>
    <a href="my_orders.php">My Orders</a>
    <a href="customer_payments.php">Payments</a>
    <a href="customer_message.php">Messages</a>
    <a href="profile.php">Profile</a>
  </div>
  <a href="logout.php" class="logout">Logout</a>
</nav>

<div class="main">
  <h1>Welcome, <?php echo htmlspecialchars($customer_name); ?></h1>

  <div class="cards">
    <div class="card">
      <a href="my_orders.php"><h3>My Pending Orders</h3></a>
      <p><?php echo $pendingOrders; ?></p>
    </div>
    <div class="card">
      <a href="customer_message.php"><h3>Messages</h3></a>
      <p><?php echo $messages; ?></p>
    </div>
    <div class="card">
      <a href="customer_payments.php"><h3>Payments</h3></a>
      <p><?php echo $payments; ?></p>
    </div>
    <div class="card">
      <a href="fees.php"><h3>Transport Fees</h3></a>
      <p>Manage</p>
    </div>
  </div>
</div>

</body>
</html>
