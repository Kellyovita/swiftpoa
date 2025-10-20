<?php
include('db.php');
$conn = db();

// Get order ID from URL
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    die("<p style='color:red;text-align:center;'>Invalid order ID.</p>");
}

$order_id = intval($_GET['order_id']);
$message = "";

// Fetch order details
$order_stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order = $order_result->fetch_assoc();

if (!$order) {
    die("<p style='color:red;text-align:center;'>Order not found.</p>");
}

// Fetch all riders
$riders = $conn->query("SELECT id, name, phone, status FROM riders ORDER BY name ASC");

// Handle assignment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rider_id = intval($_POST['rider_id']);

    if ($rider_id) {
        // Update order to assign rider
        $update_stmt = $conn->prepare("UPDATE orders SET rider_id = ?, status = 'Assigned' WHERE id = ?");
        $update_stmt->bind_param("ii", $rider_id, $order_id);

        if ($update_stmt->execute()) {
            // Prepare message details
            $pickup = htmlspecialchars($order['pickup_location']);
            $drop = htmlspecialchars($order['drop_location']);
            $customer_id = $order['customer_id'];

            // Insert notification message for the rider
            $msg_text = "A new order (#$order_id) has been assigned to you. Pickup: $pickup, Drop: $drop.";
            $msg_stmt = $conn->prepare("INSERT INTO messages (customer_id, receiver_id, order_id, message, status) VALUES (?, ?, ?, ?, 'unread')");
            $msg_stmt->bind_param("iiis", $customer_id, $rider_id, $order_id, $msg_text);
            $msg_stmt->execute();
            $msg_stmt->close();

            $message = "<p style='color:green;text-align:center;'><i class='fas fa-check-circle'></i> Rider assigned successfully and notified!</p>";
        } else {
            $message = "<p style='color:red;text-align:center;'>Error assigning rider: " . htmlspecialchars($update_stmt->error) . "</p>";
        }
        $update_stmt->close();
    } else {
        $message = "<p style='color:red;text-align:center;'>Please select a rider.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Assign Rider - SwiftPOA</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body {
  font-family: 'Poppins', sans-serif;
  background: #f4f4f4;
  margin: 0;
  padding: 0;
}
.container {
  max-width: 700px;
  background: #fff;
  margin: 40px auto;
  padding: 25px;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
h2 {
  text-align: center;
  color: #222;
  margin-bottom: 20px;
}
label {
  display: block;
  margin: 10px 0 5px;
  font-weight: 600;
}
select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
}
.btn {
  background: #222;
  color: #fff;
  padding: 10px 15px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 15px;
}
.btn:hover {
  background: #f4c20d;
  color: #000;
}
a.back {
  display: inline-block;
  margin-top: 15px;
  color: #222;
  text-decoration: none;
}
a.back:hover {
  text-decoration: underline;
}
.message {
  text-align: center;
  margin-bottom: 15px;
}
</style>
</head>
<body>

<div class="container">
  <h2><i class="fas fa-user-tag"></i> Assign Rider to Order #<?= htmlspecialchars($order_id) ?></h2>

  <?= $message ?>

  <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?> <br>
     <strong>Pickup:</strong> <?= htmlspecialchars($order['pickup_location']) ?> <br>
     <strong>Drop:</strong> <?= htmlspecialchars($order['drop_location']) ?> <br>
     <strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>

  <form method="post">
    <label for="rider_id"><i class="fas fa-motorcycle"></i> Select Rider:</label>
    <select name="rider_id" id="rider_id" required>
      <option value="">-- Select Rider --</option>
      <?php while($r = $riders->fetch_assoc()): ?>
        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?> (<?= htmlspecialchars($r['status']) ?>)</option>
      <?php endwhile; ?>
    </select>

    <button type="submit" class="btn"><i class="fas fa-check"></i> Assign Rider</button>
  </form>

  <a href="orders.php" class="back"><i class="fas fa-arrow-left"></i> Back to Orders</a>
</div>

</body>
</html>
