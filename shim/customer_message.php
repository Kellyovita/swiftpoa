<?php
// ===========================
// Enable error visibility
// ===========================
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// ===========================
// Ensure the user is logged in
// ===========================
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];

// ===========================
// Connect to the database
// ===========================
$conn = new mysqli("localhost", "root", "", "swiftpoa");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// ===========================
// Fetch all messages for this customer
// ===========================
$query = "SELECT * FROM messages WHERE customer_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
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
<title>Messages - SwiftPOA</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* ====== GLOBAL ====== */
body {
  font-family: 'Poppins', sans-serif;
  background: #f3f3f3;
  margin: 0;
  padding: 0;
  color: #333;
}

/* ====== HEADER ====== */
header {
  background: #111;
  color: #fff;
  padding: 15px 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
header img { height: 45px; }
header a {
  color: white;
  text-decoration: none;
  font-weight: 600;
  transition: 0.3s;
}
header a:hover {
  color: #f4c542;
}

/* ====== CONTAINER ====== */
.container {
  max-width: 900px;
  background: #fff;
  margin: 40px auto;
  border-radius: 10px;
  padding: 25px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
h2 {
  text-align: center;
  margin-bottom: 25px;
  color: #111;
}

/* ====== MESSAGES ====== */
.message {
  border-left: 5px solid #f4c542;
  padding: 15px 20px;
  margin-bottom: 20px;
  background: #fafafa;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}
.message p {
  margin: 0;
  line-height: 1.6;
}
.message small {
  display: block;
  color: gray;
  margin-top: 5px;
}
.message.unread {
  background: #fffbe6;
}

/* ====== FOOTER ====== */
footer {
  text-align: center;
  background: #111;
  color: #fff;
  padding: 15px;
  margin-top: 40px;
  font-size: 14px;
}
</style>
</head>
<body>

<header>
  <img src="logo.jpg" alt="SwiftPOA Logo">
  <h3>Messages</h3>
  <a href="customer.php"><i class="fas fa-arrow-left"></i> Back</a>
</header>

<div class="container">
  <h2><i class="fas fa-envelope"></i> Your Messages</h2>

  <?php if ($result && $result->num_rows > 0): ?>
    <?php while ($msg = $result->fetch_assoc()): ?>
      <div class="message <?php echo $msg['status'] === 'unread' ? 'unread' : ''; ?>">
        <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
        <small>
          <i class="fas fa-clock"></i> <?php echo htmlspecialchars($msg['created_at']); ?>
          <?php if (!empty($msg['order_id'])): ?>
            <br><i class="fas fa-box"></i> Order #<?php echo htmlspecialchars($msg['order_id']); ?>
          <?php endif; ?>
        </small>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p style="text-align:center; font-size:16px;">You have no messages yet.</p>
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
