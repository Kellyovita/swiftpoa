<?php 
include __DIR__ . '/db.php';
$conn = db();

$message = "";
$error = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $status   = $_POST['status'] ?? 'Available';
    $password = trim($_POST['password'] ?? '');

    if ($name && $email && $password) {
        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM riders WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "❌ A rider with this email already exists!";
        } else {
            // Hash password securely
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO riders (name, email, phone, location, status, password) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("ssssss", $name, $email, $phone, $location, $status, $hashedPassword);

            if ($stmt->execute()) {
                $success = true;
                $message = "✅ Successfully added <b>" . htmlspecialchars($name) . "</b> as a rider!";
            } else {
                $error = "❌ Error inserting rider: " . $stmt->error;
            }

            $stmt->close();
        }
        $check->close();
    } else {
        $error = "⚠️ Please fill in all required fields.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add Rider - SwiftPOA</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f6f9;
      margin: 0;
      padding: 40px;
      text-align: center;
    }
    .container {
      max-width: 450px;
      margin: auto;
      background: #fff;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .logo {
      width: 100px;
      margin-bottom: 20px;
    }
    h1 {
      color: #222;
      margin-bottom: 20px;
    }
    form {
      text-align: left;
    }
    label {
      font-weight: bold;
      color: #333;
      display: block;
      margin-bottom: 5px;
    }
    input, select {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }
    button {
      background: #ffcc00;
      color: #000;
      padding: 12px 20px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s;
      font-weight: bold;
    }
    button:hover {
      background: #e6b800;
    }
    .message {
      margin: 15px 0;
      font-weight: bold;
      padding: 12px;
      border-radius: 6px;
    }
    .success {
      background: #d4edda;
      color: #155724;
    }
    .error {
      background: #f8d7da;
      color: #721c24;
    }
    .actions {
      margin-top: 20px;
    }
    .actions a {
      text-decoration: none;
      display: inline-block;
      background: #000;
      color: #fff;
      padding: 10px 16px;
      border-radius: 6px;
      margin: 5px;
      transition: 0.3s;
      font-size: 14px;
    }
    .actions a:hover {
      background: #333;
    }
  </style>
</head>
<body>
  <div class="container">
    <img src="logo.jpg" alt="SwiftPOA Logo" class="logo">
    <h1>Add Rider</h1>

    <?php if ($success): ?>
      <div class="message success"><?= $message ?></div>
      <div class="actions">
        <a href="dashboard.php">⬅ Back to Dashboard</a>
        <a href="riders.php">📋 View Riders</a>
      </div>
    <?php elseif ($error): ?>
      <div class="message error"><?= $error ?></div>
      <div class="actions">
        <a href="dashboard.php">⬅ Back to Dashboard</a>
      </div>
    <?php else: ?>
      <form method="post">
        <label>Name *</label>
        <input name="name" required>

        <label>Email *</label>
        <input type="email" name="email" required>

        <label>Phone</label>
        <input name="phone">

        <label>Location</label>
        <input name="location">

        <label>Status</label>
        <select name="status">
          <option value="Available">Available</option>
          <option value="Unavailable">Unavailable</option>
        </select>

        <label>Password *</label>
        <input type="password" name="password" required>

        <button type="submit">💾 Save Rider</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
