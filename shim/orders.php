<?php
include('db.php');
$conn = db();

// Default filter
$status = isset($_GET['status']) ? $_GET['status'] : 'Pending';

// Fetch all orders (including vendor and customer)
$query = "
    SELECT 
        o.*, 
        r.name AS rider_name,
        v.name AS vendor_name,
        v.email AS vendor_email,
        v.phone AS vendor_phone
    FROM orders o
    LEFT JOIN riders r ON o.rider_id = r.id
    LEFT JOIN vendors v ON o.vendor_id = v.id
    WHERE o.status = ?
    ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Orders - SwiftPOA Admin</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    body { 
      font-family: Arial, sans-serif; 
      margin: 20px; 
      background: #f9f9f9;
    }
    h1 { color: #000; }
    .btn {
      padding: 8px 12px;
      margin: 4px;
      background: #f4c20d;
      color: black;
      border: none;
      cursor: pointer;
      text-decoration: none;
      border-radius: 4px;
      transition: background 0.3s ease;
    }
    .btn:hover { background: #e5b700; }
    .btn.active { background: black; color: yellow; }
    .btn-assign { background: green; color: white; }
    .btn-back { background: #007bff; color: white; }
    table { 
      border-collapse: collapse; 
      width: 100%; 
      margin-top: 20px; 
      background: #fff;
      border-radius: 6px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    th, td { 
      border: 1px solid #ddd; 
      padding: 8px; 
      text-align: left; 
      vertical-align: top; 
    }
    th { background: #f4c20d; color: #000; }
    img { max-width: 100px; border-radius: 6px; }
    .vendor-order td { color: green; font-weight: 600; }
    .no-data { text-align: center; padding: 20px; color: #555; }
  </style>
</head>
<body>
  <h1>Orders Management</h1>

  <!-- Back to Dashboard -->
  <a href="dashboard.php" class="btn btn-back">← Back to Dashboard</a>

  <!-- Filter Buttons -->
  <a href="orders.php?status=Pending" class="btn <?= $status=='Pending'?'active':'' ?>">Pending</a>
  <a href="orders.php?status=Assigned" class="btn <?= $status=='Assigned'?'active':'' ?>">Assigned</a>
  <a href="orders.php?status=Picked" class="btn <?= $status=='Picked'?'active':'' ?>">Picked</a>
  <a href="orders.php?status=Delivered" class="btn <?= $status=='Delivered'?'active':'' ?>">Delivered</a>
  <a href="orders.php?status=Cancelled" class="btn <?= $status=='Cancelled'?'active':'' ?>">Cancelled</a>

  <table>
    <tr>
      <th>ID</th>
      <th>Customer / Vendor</th>
      <th>Email</th>
      <th>Phone</th>
      <th>Pickup Location</th>
      <th>Drop Location</th>
      <th>Parcel Type / Description</th>
      <th>Parcel Image</th>
      <th>Status</th>
      <th>Rider</th>
      <th>Tracking #</th>
      <th>Created</th>
      <th>Action</th>
    </tr>

    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <?php
          // Detect vendor orders
          $isVendorOrder = !empty($row['vendor_id']);
          $rowClass = $isVendorOrder ? 'vendor-order' : '';
          $customerName  = $isVendorOrder ? ($row['vendor_name'] ?: 'Vendor') : ($row['customer_name'] ?? 'Customer');
          $customerEmail = $isVendorOrder ? ($row['vendor_email'] ?? '') : ($row['customer_email'] ?? '');
          $customerPhone = $isVendorOrder ? ($row['vendor_phone'] ?? '') : ($row['customer_phone'] ?? '');
        ?>
        <tr class="<?= $rowClass ?>">
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($customerName) ?></td>
          <td><?= htmlspecialchars($customerEmail) ?></td>
          <td><?= htmlspecialchars($customerPhone) ?></td>
          <td><?= htmlspecialchars($row['pickup_location']) ?></td>
          <td><?= htmlspecialchars($row['drop_location']) ?></td>
          <td><?= nl2br(htmlspecialchars($row['parcel_type'] ?? $row['parcel_description'])) ?></td>
          <td>
            <?php if (!empty($row['parcel_image'])): ?>
              <img src="<?= htmlspecialchars($row['parcel_image']) ?>" alt="Parcel Image">
            <?php else: ?>
              No Image
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($row['status']) ?></td>
          <td><?= htmlspecialchars($row['rider_name'] ?? 'Not Assigned') ?></td>
          <td><?= htmlspecialchars($row['tracking_number'] ?? '-') ?></td>
          <td><?= htmlspecialchars($row['created_at'] ?? '-') ?></td>
          <td>
            <a class="btn btn-assign" href="assign_rider.php?order_id=<?= $row['id'] ?>">Assign Rider</a>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="13" class="no-data">No orders found for this status.</td></tr>
    <?php endif; ?>

  </table>
</body>
</html>
