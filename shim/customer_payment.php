<?php
session_start();

// Check if logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'] ?? 'Customer';

// Database connection
if (file_exists(__DIR__ . '/includes/db.php')) {
    require_once __DIR__ . '/includes/db.php';
    $conn = db();
} else {
    $conn = new mysqli('localhost', 'root', '', 'swiftpoa');
}
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch customer payments
$stmt = $conn->prepare("
    SELECT p.*, o.tracking_number 
    FROM payments p
    LEFT JOIN orders o ON p.order_id = o.id
    WHERE p.customer_id = ?
    ORDER BY p.created_at DESC
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Payments - SwiftPOA</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body {
  font-family: 'Poppins', sans-serif;
  background: #f7f7f7;
  margin: 0;
  color: #333;
}
header {
  background: #111;
  color: #fff;
  padding: 15px 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
header img { height: 45px; }
.container {
  max-width: 1000px;
  margin: 40px auto;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  padding: 25px;
}
h2 {
  text-align: center;
  margin-bottom: 20px;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
th, td {
  border: 1px solid #ddd;
  padding: 10px;
  text-align: center;
}
th {
  background: #222;
  color: #fff;
}
tr:nth-child(even) { background: #f9f9f9; }
.print-btn {
  display: block;
  margin: 20px auto;
  background: #f4c542;
  color: #111;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
}
.print-btn:hover { background: #222; color: #fff; }
footer {
  background: #111;
  color: #fff;
  text-align: center;
  padding: 15px;
  margin-top: 40px;
}
@media print {
  header, .print-btn, footer {
    display: none;
  }
  .container {
    box-shadow: none;
    border: none;
  }
}
</style>
</head>
<body>

<header>
  <img src="logo.jpg" alt="SwiftPOA Logo">
  <h3>My Payments</h3>
  <a href="customer.php" style="color:white;text-decoration:none;">
    <i class="fas fa-arrow-left"></i> Back
  </a>
</header>

<div class="container">
  <h2><i class="fas fa-money-bill-wave"></i> Payment History</h2>

  <?php if ($result->num_rows > 0): ?>
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Order</th>
        <th>Amount (KSh)</th>
        <th>Method</th>
        <th>Status</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 1; while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $i++; ?></td>
        <td><?= htmlspecialchars($row['tracking_number'] ?? 'N/A'); ?></td>
        <td><?= number_format($row['amount'], 2); ?></td>
        <td><?= htmlspecialchars($row['payment_method']); ?></td>
        <td><?= htmlspecialchars($row['payment_status']); ?></td>
        <td><?= htmlspecialchars($row['created_at']); ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <button class="print-btn" onclick="window.print()">
    <i class="fas fa-print"></i> Print Payment Report
  </button>
  <?php else: ?>
    <p style="text-align:center;">You haven’t made any payments yet.</p>
  <?php endif; ?>
</div>

<footer>
  <p>&copy; <?php echo date("Y"); ?> SwiftPOA. All Rights Reserved.</p>
</footer>

</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
