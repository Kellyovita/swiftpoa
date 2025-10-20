<?php
session_start();
include('includes/db.php');
$conn = db();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // User types, their redirect pages, and password hashing methods
    $userTypes = [
        "admins"    => ["redirect" => "dashboard.php", "type" => "md5"],
        "vendors"   => ["redirect" => "vendor.php",    "type" => "hash"],
        "riders"    => ["redirect" => "rider.php",     "type" => "hash"],
        "staff"     => ["redirect" => "staff.php",     "type" => "hash"],
        "customers" => ["redirect" => "customer.php",  "type" => "hash"]
    ];

    $found = false;

    foreach ($userTypes as $table => $info) {
        $stmt = $conn->prepare("SELECT * FROM $table WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $found = true;
            $isValid = false;

            // Verify password based on type
            if ($info['type'] === "md5") {
                $isValid = ($user['password'] === md5($password));
            } else {
                $isValid = password_verify($password, $user['password']);
            }

            if ($isValid) {
                // Common session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email']   = $user['email'];
                $_SESSION['role']    = $table;

                // Role-specific session data
                switch ($table) {
                    case "admins":
                        $_SESSION['admin_id']   = $user['id'];
                        $_SESSION['admin_name'] = $user['name'];
                        break;
                    case "vendors":
                        $_SESSION['vendor_id']   = $user['id'];
                        $_SESSION['vendor_name'] = $user['name'];
                        break;
                    case "riders":
                        $_SESSION['rider_id']   = $user['id'];
                        $_SESSION['rider_name'] = $user['name'];
                        break;
                    case "staff":
                        $_SESSION['staff_id']   = $user['id'];
                        $_SESSION['staff_name'] = $user['name'];
                        break;
                    case "customers":
                        $_SESSION['customer_id']    = $user['id'];
                        $_SESSION['customer_name']  = $user['name'];
                        $_SESSION['customer_email'] = $user['email']; // ✅ Added this line
                        break;
                }

                // Redirect to correct dashboard
                header("Location: " . $info['redirect']);
                exit();
            } else {
                $error = "Invalid password.";
            }
            break; // Stop loop after finding the user
        }
    }

    if (!$found) {
        $error = "No account found with that email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SwiftPOA Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f5f5f5, #ddd);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .login-box {
      width: 360px;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
      text-align: center;
    }
    .login-box img {
      width: 90px;
      margin-bottom: 20px;
    }
    .login-box h2 {
      margin-bottom: 20px;
      color: #333;
    }
    input[type="email"], input[type="password"] {
      width: 90%;
      padding: 10px;
      margin: 8px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    button {
      width: 95%;
      padding: 10px;
      background: #faa403;
      border: none;
      color: #111;
      font-weight: bold;
      border-radius: 6px;
      cursor: pointer;
      margin-top: 10px;
      transition: 0.3s;
    }
    button:hover {
      background: #111;
      color: #faa403;
    }
    .error {
      color: red;
      margin: 10px 0;
      font-size: 14px;
    }
    a {
      display: block;
      margin-top: 15px;
      color: #555;
      text-decoration: none;
    }
    a:hover {
      color: #faa403;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <img src="logo.jpg" alt="SwiftPOA Logo">
    <h2>Login</h2>

    <?php if($error): ?>
      <div class="error"><i class="fa fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit"><i class="fa fa-sign-in-alt"></i> Login</button>
    </form>
    <a href="create_account.php">Don’t have an account? Create one</a>
  </div>
</body>
</html>
