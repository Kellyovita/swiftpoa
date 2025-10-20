<?php
session_start();

// Redirect if customer not logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];

// Database connection
$conn = new mysqli("localhost", "root", "", "swiftpoa");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch orders belonging only to this logged-in customer
$query = "
    SELECT id, pickup_location, drop_location, parcel_description, status, created_at 
    FROM orders 
    WHERE customer_id = ? 
    ORDER BY created_at DESC
";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Orders - SwiftPOA</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { font-family: 'Poppins', sans-serif; margin: 0; background: #f3f4f6; color: #333; }
header { background: linear-gradient(135deg, #111, #333); color: #fff; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; }
header img { height: 50px; }
nav ul { list-style: none; display: flex; gap: 25px; margin: 0; padding: 0; }
nav a { color: #fff; text-decoration: none; font-weight: 600; transition: 0.3s; }
nav a:hover, nav a.active { color: #f4c542; }
.container { max-width: 1100px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1); }
h2 { text-align: center; margin-bottom: 25px; color: #222; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 14px 12px; border-bottom: 1px solid #ddd; text-align: left; }
th { background: #111; color: #fff; }
tr:hover { background: #f8f8f8; }
.status { font-weight: bold; padding: 6px 10px; border-radius: 8px; color: #fff; }
.status.Pending { background: orange; }
.status.Assigned { background: blue; }
.status.Picked { background: purple; }
.status.Delivered { background: green; }
.status.Cancelled { background: red; }
.btn { padding: 8px 14px; background: #111; color: #fff; border-radius: 6px; text-decoration: none; font-weight: bold; transition: 0.3s; }
.btn:hover { background: #f4c542; color: #111; }
.refresh-btn { background: #007BFF; margin-bottom: 15px; }
.refresh-btn:hover { background: #0056b3; }
footer { background: #111; color: #fff; text-align: center; padding: 20px; margin-top: 50px; }
@media (max-width: 768px) {
  nav ul { flex-direction: column; background: #222; position: absolute; top: 60px; right: 0; width: 220px; padding: 15px; display: none; }
  nav ul.show { display: block; }
  nav .menu-toggle { display: block; cursor: pointer; }
  .container { padding: 15px; }
  table, thead, tbody, th, td, tr { display: block; }
  tr { margin-bottom: 15px; background: #fafafa; border-radius: 8px; padding: 10px; }
  th { display: none; }
  td { padding: 10px 0; border: none; position: relative; }
  td::before { content: attr(data-label); font-weight: bold; display: block; color: #333; }
}
</style>
</head>
<body>

<header>
  <img src="logo.jpg" alt="SwiftPOA Logo">
  <nav>
    <div class="menu-toggle"><i class="fas fa-bars"></i></div>
    <ul>
      <li><a href="customer.php"><i class="fas fa-home"></i> Dashboard</a></li>
      <li><a href="place_order.php"><i class="fas fa-plus-circle"></i> Place Order</a></li>
      <li><a href="my_orders.php" class="active"><i class="fas fa-box"></i> My Orders</a></li>
      <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </nav>
</header>

<div class="container">
  <h2><i class="fas fa-box"></i> My Orders</h2>

  <a class="btn refresh-btn" href="my_orders.php"><i class="fas fa-sync"></i> Refresh Orders</a>

  <?php if ($result->num_rows > 0): ?>
  <table>
    <thead>
      <tr>
        <th>Order ID</th>
        <th>Pickup</th>
        <th>Drop</th>
        <th>Description</th>
        <th>Status</th>
        <th>Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td data-label="Order ID"><?php echo htmlspecialchars($row['id']); ?></td>
        <td data-label="Pickup"><?php echo htmlspecialchars($row['pickup_location']); ?></td>
        <td data-label="Drop"><?php echo htmlspecialchars($row['drop_location']); ?></td>
        <td data-label="Description"><?php echo htmlspecialchars($row['parcel_description']); ?></td>
        <td data-label="Status">
          <span class="status <?php echo htmlspecialchars($row['status']); ?>">
            <?php echo htmlspecialchars($row['status']); ?>
          </span>
        </td>
        <td data-label="Date"><?php echo htmlspecialchars($row['created_at']); ?></td>
        <td data-label="Action">
          <a class="btn" href="confirm_order.php?id=<?php echo $row['id']; ?>">Confirm Order</a>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
  <?php else: ?>
    <p style="text-align:center; font-size:16px;">You haven’t placed any orders yet.</p>
  <?php endif; ?>
</div>

<footer>
  <p>&copy; <?php echo date('Y'); ?> SwiftPOA. All rights reserved.</p>
</footer>

<script>
document.querySelector(".menu-toggle").addEventListener("click", () => {
  document.querySelector("nav ul").classList.toggle("show");
});
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
