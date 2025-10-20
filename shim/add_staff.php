<?php
include __DIR__ . '/db.php';
$conn = db();

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $role     = trim($_POST['role']);
    $password = $_POST['password'] ?? '';

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM staff WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "❌ A staff member with this email already exists!";
    } else {
        // Hash password securely
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO staff (name, email, phone, role, password) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sssss", $name, $email, $phone, $role, $password_hash);

        if ($stmt->execute()) {
            $message = "✅ Successfully added <b>" . htmlspecialchars($name) . "</b>!";
        } else {
            $error = "❌ Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $check->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Staff - SwiftPOA</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f6f9;
        text-align: center;
        padding: 40px;
    }
    .form-container {
        max-width: 430px;
        margin: auto;
        padding: 25px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h1 {
        color: #222;
        margin-bottom: 15px;
    }
    label {
        display: block;
        margin: 10px 0 5px;
        font-weight: bold;
        text-align: left;
    }
    input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        margin-bottom: 10px;
    }
    button {
        background: #ffcc00;
        color: #222;
        border: none;
        padding: 10px 20px;
        margin-top: 10px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        transition: background 0.3s ease;
    }
    button:hover {
        background: #e6b800;
    }
    .logo {
        max-width: 120px;
        margin-bottom: 20px;
    }
    .message {
        margin: 15px 0;
        font-weight: bold;
        color: green;
    }
    .error {
        margin: 15px 0;
        font-weight: bold;
        color: red;
    }
    .links {
        margin-top: 20px;
    }
    .links a {
        display: inline-block;
        margin: 5px;
        padding: 8px 16px;
        text-decoration: none;
        background: #222;
        color: #fff;
        border-radius: 6px;
        font-size: 14px;
    }
    .links a:hover {
        background: #444;
    }
  </style>
</head>
<body>
  <img src="logo.jpg" alt="SwiftPOA Logo" class="logo">
  <div class="form-container">
    <h1>Add Staff</h1>

    <?php if ($message): ?>
      <p class="message"><?= $message ?></p>
      <div class="links">
        <a href="dashboard.php">⬅ Back to Dashboard</a>
        <a href="staffs.php">📋 View Staff</a>
      </div>
    <?php elseif ($error): ?>
      <p class="error"><?= $error ?></p>
      <div class="links">
        <a href="dashboard.php">⬅ Back to Dashboard</a>
      </div>
    <?php endif; ?>

    <?php if (!$message): ?>
    <form method="POST">
      <label>Name:</label>
      <input type="text" name="name" required>

      <label>Email:</label>
      <input type="email" name="email" required>

      <label>Phone:</label>
      <input type="text" name="phone">

      <label>Role:</label>
      <input type="text" name="role">

      <label>Password:</label>
      <input type="password" name="password" required>

      <button type="submit">💾 Save Staff</button>
    </form>
    <?php endif; ?>
  </div>
</body>
</html>
