<?php
session_start();
include('db.php');
$conn = db();

// Ensure vendor is logged in
if (!isset($_SESSION['vendor_id'])) {
    header("Location: login.php");
    exit;
}

$vendor_id = $_SESSION['vendor_id'];
$vendor_name = $_SESSION['name'] ?? 'Vendor';

// Status filter
$status = isset($_GET['status']) ? $_GET['status'] : 'All';

// Build query with all joins and details
if ($status === 'All') {
    $stmt = $conn->prepare("
        SELECT 
            o.*,
            v.name AS vendor_name,
            v.email AS vendor_email,
            v.phone AS vendor_phone,
            r.name AS rider_name,
            r.phone AS rider_phone,
            r.status AS rider_status
        FROM orders o
        LEFT JOIN vendors v ON o.vendor_id = v.id
        LEFT JOIN riders r ON o.rider_id = r.id
        WHERE o.vendor_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->bind_param("i", $vendor_id);
} else {
    $stmt = $conn->prepare("
        SELECT 
            o.*,
            v.name AS vendor_name,
            v.email AS vendor_email,
            v.phone AS vendor_phone,
            r.name AS rider_name,
            r.phone AS rider_phone,
            r.status AS rider_status
        FROM orders o
        LEFT JOIN vendors v ON o.vendor_id = v.id
        LEFT JOIN riders r ON o.rider_id = r.id
        WHERE o.vendor_id = ? AND o.status = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->bind_param("is", $vendor_id, $status);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Orders - SwiftPOA Vendor Dashboard</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      background: #f7f7f7;
    }
    h1 {
      text-align: center;
      color: #333;
    }
    .btn {
      padding: 8px 14px;
      margin: 5px;
      background: #f4c20d;
      color: black;
      border: none;
      border-radius: 5px;
      text-decoration: none;
      transition: background 0.3s;
    }
    .btn:hover { background: #e5b700; }
    .btn.active {
      background: black;
      color: yellow;
    }
    .btn-logout {
      background: #dc3545;
      color: white;
      float: right;
    }
    .btn-back {
      background: #007bff;
      color: white;
    }
    .filters {
      text-align: center;
      margin: 15px 0;
    }
    .table-container {
      margin-top: 20px;
      overflow-x: auto;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
      border-radius: 6px;
    }
    th, td {
      padding: 10px;
      text-align: left;
      border-bottom: 1px solid #eee;
      vertical-align: top;
    }
    th {
      background: #f4c20d;
      color: #222;
    }
    img {
      max-width: 90px;
      border-radius: 5px;
    }
    .status {
      padding: 4px 8px;
      border-radius: 4px;
      font-weight: bold;
      text-align: center;
      display: inline-block;
    }
    .Pending { background: #ffc107; }
    .Assigned { background: #17a2b8; color: white; }
    .Picked { background: #007bff; color: white; }
    .Delivered { background: #28a745; color: white; }
    .Cancelled { background: #dc3545; color: white; }
  </style>
</head>
<body>

  <h1>Welcome, <?= htmlspecialchars($vendor_name) ?> — Your Orders</h1>
  <a href="vendor.php" class="btn btn-back">← Back to Dashboard</a>
  <!-- <a href="logout.php" class="btn btn-logout">Logout</a> -->

  <div class="filters">
    <a href="vendor_orders.php?status=All" class="btn <?= $status=='All'?'active':'' ?>">All</a>
    <a href="vendor_orders.php?status=Pending" class="btn <?= $status=='Pending'?'active':'' ?>">Pending</a>
    <a href="vendor_orders.php?status=Assigned" class="btn <?= $status=='Assigned'?'active':'' ?>">Assigned</a>
    <a href="vendor_orders.php?status=Picked" class="btn <?= $status=='Picked'?'active':'' ?>">Picked</a>
    <a href="vendor_orders.php?status=Delivered" class="btn <?= $status=='Delivered'?'active':'' ?>">Delivered</a>
    <a href="vendor_orders.php?status=Cancelled" class="btn <?= $status=='Cancelled'?'active':'' ?>">Cancelled</a>
  </div>

  <div class="table-container">
    <table>
      <tr>
        <th>Order ID</th>
        <th>Tracking No.</th>
        <th>Customer Name</th>
        <th>Customer Email</th>
        <th>Customer Phone</th>
        <th>Pickup Location</th>
        <th>Drop Location</th>
        <th>Parcel Description</th>
        <th>Parcel Image</th>
        <th>Rider Details</th>
        <th>Status</th>
        <th>Assigned At</th>
        <th>Created At</th>
        <th>Vendor Info</th>
      </tr>

      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['tracking_number']) ?></td>
            <td><?= htmlspecialchars($row['customer_name']) ?></td>
            <td><?= htmlspecialchars($row['customer_email']) ?></td>
            <td><?= htmlspecialchars($row['customer_phone']) ?></td>
            <td><?= htmlspecialchars($row['pickup_location']) ?></td>
            <td><?= htmlspecialchars($row['drop_location']) ?></td>
            <td><?= nl2br(htmlspecialchars($row['parcel_description'])) ?></td>
            <td>
              <?php if (!empty($row['parcel_image'])): ?>
                <img src="<?= htmlspecialchars($row['parcel_image']) ?>" alt="Parcel">
              <?php else: ?>
                No Image
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($row['rider_name'])): ?>
                <?= htmlspecialchars($row['rider_name']) ?><br>
                📞 <?= htmlspecialchars($row['rider_phone']) ?><br>
                (<?= htmlspecialchars($row['rider_status']) ?>)
              <?php else: ?>
                Not Assigned
              <?php endif; ?>
            </td>
            <td><span class="status <?= $row['status'] ?>"><?= $row['status'] ?></span></td>
            <td><?= $row['assigned_at'] ?: '—' ?></td>
            <td><?= $row['created_at'] ?></td>
            <td>
              <?= htmlspecialchars($row['vendor_name']) ?><br>
              📧 <?= htmlspecialchars($row['vendor_email']) ?><br>
              📞 <?= htmlspecialchars($row['vendor_phone']) ?>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="14" style="text-align:center;">No orders found.</td></tr>
      <?php endif; ?>
    </table>
  </div>

</body>
</html>
