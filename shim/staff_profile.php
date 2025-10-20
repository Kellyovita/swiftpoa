<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$conn = db();

// Make sure staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit;
}

$staff_id = $_SESSION['staff_id'];
$success = $error = "";

// Fetch current staff info
$stmt = $conn->prepare("SELECT name, phone, email, password FROM staff WHERE id = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

// Update profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($name) || empty($phone) || empty($email)) {
        $error = "Please fill in all required fields.";
    } else {
        if (!empty($password)) {
            // Hash new password if provided
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE staff SET name=?, phone=?, email=?, password=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $phone, $email, $hashed, $staff_id);
        } else {
            // Update without password
            $stmt = $conn->prepare("UPDATE staff SET name=?, phone=?, email=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $phone, $email, $staff_id);
        }

        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
            // Refresh data
            $staff['name'] = $name;
            $staff['phone'] = $phone;
            $staff['email'] = $email;
        } else {
            $error = "Error updating profile. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Profile - SwiftPOA</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
body { display: flex; height: 100vh; background: #f7f7f7; }

.sidebar {
  width: 220px; background: #000; color: #fff; display: flex;
  flex-direction: column; padding-top: 20px;
}
.sidebar .logo { text-align: center; margin-bottom: 30px; }
.sidebar .logo img { width: 100px; margin-bottom: 10px; }
.sidebar .logo h2 { color: #ffb400; }
.sidebar .menu { list-style: none; padding-left: 0; }
.sidebar .menu li { margin: 15px 0; }
.sidebar .menu li a {
  text-decoration: none; color: #fff; padding: 10px 20px;
  display: block; border-radius: 5px; transition: 0.3s;
}
.sidebar .menu li a:hover, .sidebar .menu li a.active {
  background: #ffb400; color: #000;
}
.sidebar .menu li a.logout { margin-top: 50px; background: #ff4d4d; }
.sidebar .menu li a.logout:hover { background: #cc0000; }

.main-content {
  flex: 1; padding: 20px 30px; overflow-y: auto;
}
h1 { margin-bottom: 20px; color: #333; }

.form-container {
  background: #fff; padding: 25px; border-radius: 10px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1); max-width: 500px;
}
form input {
  width: 100%; padding: 10px; margin: 8px 0 15px;
  border: 1px solid #ccc; border-radius: 6px;
}
form button {
  background: #ffb400; border: none; padding: 10px 18px;
  border-radius: 6px; font-weight: bold; cursor: pointer;
  transition: 0.3s; color: #000;
}
form button:hover { background: #e09c00; }

.message {
  padding: 10px; margin-bottom: 15px; border-radius: 5px; font-weight: bold;
}
.success { background: #d4edda; color: #155724; }
.error { background: #f8d7da; color: #721c24; }

@media(max-width:768px){
  body { flex-direction: column; }
  .sidebar { width: 100%; flex-direction: row; overflow-x: auto; }
  .sidebar .menu { display: flex; flex-direction: row; }
  .sidebar .menu li { margin: 0 10px; }
  .main-content { padding: 10px; }
}
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
  <div class="logo">
    <img src="logo.jpg" alt="SwiftPOA Logo">
    <h2>SwiftPOA</h2>
  </div>
  <ul class="menu">
    <li><a href="staff.php">Dashboard</a></li>
    <li><a href="staff_orders.php">Orders</a></li>
    <li><a href="assign_rider.php">Assign Orders</a></li>
    <li><a href="fees.php">Transportation Fee</a></li>
    <li><a href="payments.php">Payments</a></li>
    <li><a href="staff_message.php">Messages</a></li>
    <li><a href="staff_profile.php" class="active">Profile</a></li>
    <li><a href="logout.php" class="logout">Logout</a></li>
  </ul>
</aside>

<!-- Main -->
<main class="main-content">
  <h1>My Profile</h1>

  <div class="form-container">
    <?php if ($success): ?><div class="message success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="message error"><?= $error ?></div><?php endif; ?>

    <form method="POST">
      <label>Full Name</label>
      <input type="text" name="name" value="<?= htmlspecialchars($staff['name']) ?>" required>

      <label>Phone Number</label>
      <input type="text" name="phone" value="<?= htmlspecialchars($staff['phone']) ?>" required>

      <label>Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($staff['email']) ?>" required>

      <label>New Password (leave blank to keep current)</label>
      <input type="password" name="password" placeholder="Enter new password">

      <button type="submit">Update Profile</button>
    </form>
  </div>
</main>

</body>
</html>
