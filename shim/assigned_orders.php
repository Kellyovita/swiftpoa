<?php
session_start();
require_once 'db.php';
$conn = db();

// Ensure rider is logged in
if (!isset($_SESSION['rider_id'])) {
    header("Location: login.php");
    exit;
}

$rider_id = $_SESSION['rider_id'];
$rider_name = $_SESSION['name'] ?? 'Rider';

// Fetch assigned orders
$stmt = $conn->prepare("
    SELECT o.id, o.customer_id, o.pickup_location, o.drop_location, 
           o.status, o.created_at, c.name AS customer_name, c.phone AS customer_phone
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    WHERE o.rider_id = ?
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $rider_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Assigned Orders - SwiftPOA</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f4f4f4;
      margin: 0;
      padding: 0;
    }
    header {
      background: #222;
      color: #fff;
      padding: 15px 25px;
      text-align: center;
    }
    header h2 { margin: 0; }
    .container {
      max-width: 1000px;
      margin: 30px auto;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h3 {
      color: #222;
      text-align: center;
      margin-bottom: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    th, td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    th {
      background: #f4c20d;
      color: #000;
    }
    tr:hover { background: #f9f9f9; }
    .status {
      font-weight: bold;
      border-radius: 5px;
      padding: 5px 10px;
      text-transform: capitalize;
    }
    .assigned { background: #f4c20d; color: #000; }
    .picked-for-delivery { background: #3498db; color: #fff; }
    .delivered { background: #27ae60; color: #fff; }
    .btn {
      background: #222;
      color: #fff;
      padding: 8px 12px;
      border-radius: 5px;
      text-decoration: none;
      font-size: 14px;
      border: none;
      cursor: pointer;
    }
    .btn:hover {
      background: #f4c20d;
      color: #000;
    }
    .btn:disabled {
      background: #ccc;
      color: #666;
      cursor: not-allowed;
    }
    footer {
      text-align: center;
      padding: 15px;
      background: #222;
      color: #fff;
      margin-top: 40px;
    }
  </style>
</head>
<body>

<header>
  <h2><i class="fas fa-motorcycle"></i> Welcome, <?= htmlspecialchars($rider_name) ?> - Assigned Orders</h2>
</header>

<div class="container">
  <h3><i class="fas fa-box"></i> Orders Assigned to You</h3>

  <?php if ($result->num_rows > 0): ?>
  <table id="ordersTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Customer</th>
        <th>Pickup</th>
        <th>Drop</th>
        <th>Status</th>
        <th>Contact</th>
        <th>Assigned On</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr id="order-<?= $row['id'] ?>">
          <td><?= htmlspecialchars($row['id']) ?></td>
          <td><?= htmlspecialchars($row['customer_name']) ?></td>
          <td><?= htmlspecialchars($row['pickup_location']) ?></td>
          <td><?= htmlspecialchars($row['drop_location']) ?></td>
          <td class="status-cell">
            <span class="status <?= strtolower(str_replace(' ', '-', $row['status'])) ?>">
              <?= htmlspecialchars($row['status']) ?>
            </span>
          </td>
          <td><?= htmlspecialchars($row['customer_phone']) ?></td>
          <td><?= htmlspecialchars($row['created_at']) ?></td>
          <td>
            <?php if ($row['status'] === 'Assigned'): ?>
              <button class="btn update-status" data-id="<?= $row['id'] ?>" data-status="Picked for Delivery">
                Picked for Delivery
              </button>
            <?php elseif ($row['status'] === 'Picked for Delivery'): ?>
              <button class="btn update-status" data-id="<?= $row['id'] ?>" data-status="Delivered">
                Mark as Delivered
              </button>
            <?php else: ?>
              <button class="btn" disabled>Completed</button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <?php else: ?>
    <p style="text-align:center;color:#888;">No orders assigned to you yet.</p>
  <?php endif; ?>
</div>

<footer>
  <p>&copy; <?= date('Y') ?> SwiftPOA. All Rights Reserved.</p>
</footer>

<!-- AJAX Script -->
<script>
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".update-status").forEach(button => {
    button.addEventListener("click", () => {
      const orderId = button.dataset.id;
      const newStatus = button.dataset.status;

      if (!confirm(`Are you sure you want to mark this order as "${newStatus}"?`)) return;

      fetch("update_order_status.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `order_id=${orderId}&new_status=${encodeURIComponent(newStatus)}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const row = document.querySelector(`#order-${orderId}`);
          const statusCell = row.querySelector(".status-cell span");

          // Update status visually
          statusCell.textContent = newStatus;
          statusCell.className = `status ${newStatus.toLowerCase().replace(/ /g, '-')}`;

          // Update button
          if (newStatus === "Picked for Delivery") {
            button.textContent = "Mark as Delivered";
            button.dataset.status = "Delivered";
          } else {
            button.textContent = "Completed";
            button.disabled = true;
          }
        } else {
          alert("Error updating status. Please try again.");
        }
      })
      .catch(() => alert("Request failed. Please check your internet or server."));
    });
  });
});
</script>

</body>
</html>
