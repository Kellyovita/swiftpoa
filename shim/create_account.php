<?php
include __DIR__ . '/db.php';
$conn = db();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $password = $_POST['password'] ?? '';

    // Hash password (secure)
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO customers(name, email, phone, location, password) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("sssss", $name, $email, $phone, $location, $password_hash);

    if ($stmt->execute()) {
        $message = "✅ You have successfully created an account <b>" . htmlspecialchars($name) . "</b> Please go back to login";
    } else {
        $message = "❌ Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Vendor - SwiftPOA</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    body {
        font-family: Arial, sans-serif;
        background: #f9f9f9;
        text-align: center;
        padding: 40px;
    }
    .form-container {
        max-width: 400px;
        margin: auto;
        padding: 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h1 {
        color: #222;
    }
    label {
        display: block;
        margin: 10px 0 5px;
        font-weight: bold;
        text-align: left;
    }
    input, select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
    }
    button {
        background: #ffcc00;
        color: #222;
        border: none;
        padding: 10px 20px;
        margin-top: 15px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
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
    }
    .links a:hover {
        background: #444;
    }
  </style>
</head>
<body>
  <img src="logo.jpg" alt="SwiftPOA Logo" class="logo">
  <div class="form-container">
    <h1>🎉Welcome🎉</h1>
    <h2>Create an account</h2>
    <?php if ($message): ?>
      <p class="message"><?= $message ?></p>
    <?php endif; ?>
    <form method="post">
      <label>Name:</label>
      <input type="text" name="name" required>

      <label>Email:</label>
      <input type="email" name="email" required>

      <label>Phone:</label>
      <input type="text" name="phone">

      <label>Your place of residence:</label>
      <input type="text" name="location">

      <label>Password:</label>
      <input type="password" name="password" required>

      <button type="submit">Create account</button>
      <br>
      <br>
      <a href="login.php">You already have an account? Back to login</a>
    </form>
  </div>
</body>
</html>
