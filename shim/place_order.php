<?php
session_start();

// ==========================
// Include DB connection
// ==========================
if (file_exists(__DIR__ . '/includes/db.php')) {
    require_once __DIR__ . '/includes/db.php';
    $conn = db();
} else {
    $conn = new mysqli('localhost', 'root', '', 'swiftpoa');
}

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$message = '';
$success = false;

// ==========================
// Check if logged in as customer
// ==========================
$customer_name  = $_SESSION['customer_name'] ?? 'Guest';
$customer_email = $_SESSION['email'] ?? 'guest@swiftpoa.com';
$customer_id    = $_SESSION['customer_id'] ?? null;

// ==========================
// Handle order form submission
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup      = trim($_POST['pickup'] ?? '');
    $drop        = trim($_POST['drop'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');

    // Input validation
    if ($pickup === '' || $drop === '' || $description === '' || $phone === '') {
        $message = "<p style='color:red;text-align:center;'>Please fill in all required fields.</p>";
    } else {
        // Generate unique tracking number
        $tracking_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

        // Handle file upload
        $relativePath = null;
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (in_array($_FILES['image']['type'], $allowed_types)) {
                $uploadsDir = __DIR__ . '/uploads';
                if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0777, true);
                $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', $_FILES['image']['name']);
                $targetFile = $uploadsDir . '/' . $fileName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $relativePath = 'uploads/' . $fileName;
                } else {
                    $message = "<p style='color:red;text-align:center;'>Failed to upload the image.</p>";
                }
            } else {
                $message = "<p style='color:red;text-align:center;'>Only JPG, PNG, or GIF files are allowed.</p>";
            }
        }

        // Insert order
        if ($message === '') {
            $stmt = $conn->prepare("
                INSERT INTO orders (
                    customer_id, customer_name, customer_email, customer_phone,
                    pickup_location, drop_location, parcel_description,
                    parcel_image, status, tracking_number
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)
            ");

            if ($stmt) {
                $stmt->bind_param(
                    "issssssss",
                    $customer_id,
                    $customer_name,
                    $customer_email,
                    $phone,
                    $pickup,
                    $drop,
                    $description,
                    $relativePath,
                    $tracking_number
                );

                if ($stmt->execute()) {
                    $success = true;
                    $order_id = $stmt->insert_id;

                    // =====================================
                    // 1️⃣ Message for Customer
                    // =====================================
                    $msg = "✅ Your order #$order_id has been placed successfully!
Pickup: $pickup → Drop: $drop
Description: $description
Tracking: $tracking_number
Status: Pending.";

                    $msgStmt = $conn->prepare("INSERT INTO messages (customer_id, order_id, message) VALUES (?, ?, ?)");
                    if ($msgStmt) {
                        $msgStmt->bind_param("iis", $customer_id, $order_id, $msg);
                        $msgStmt->execute();
                        $msgStmt->close();
                    }

                    // =====================================
                    // 2️⃣ Message for Admin
                    // =====================================
                    $adminMsg = "📦 *New Order Received!*
Order ID: $order_id
Tracking No: $tracking_number
Customer: $customer_name
Email: $customer_email
Phone: $phone
Pickup: $pickup
Drop: $drop
Description: $description
Status: Pending.";

                    // Option A: Store message in DB (for dashboard notifications)
                    $adminStmt = $conn->prepare("INSERT INTO messages (customer_id, order_id, message, is_admin) VALUES (?, ?, ?, 1)");
                    if ($adminStmt) {
                        $adminStmt->bind_param("iis", $customer_id, $order_id, $adminMsg);
                        $adminStmt->execute();
                        $adminStmt->close();
                    }

                    // Option B: (Optional) Send an email to admin
                    // Uncomment this section if your server supports mail()
                    /*
                    $adminEmail = "Shimsheldon24@gmail.com"; // or your admin email
                    $subject = "New Order Received - SwiftPOA";
                    $headers = "From: noreply@swiftpoa.com\r\nContent-Type: text/plain; charset=UTF-8";
                    mail($adminEmail, $subject, $adminMsg, $headers);
                    */
                } else {
                    $message = "<p style='color:red;text-align:center;'>Insert failed: " . htmlspecialchars($stmt->error) . "</p>";
                }
                $stmt->close();
            } else {
                $message = "<p style='color:red;text-align:center;'>Prepare failed: " . htmlspecialchars($conn->error) . "</p>";
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SwiftPOA - Place Order</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  body{font-family:Arial,Helvetica,sans-serif;margin:0;background:#f9f9f9;color:#333}
  header{background:#222;color:#fff;padding:15px 30px;display:flex;justify-content:space-between;align-items:center}
  header img{height:40px}
  nav ul{list-style:none;display:flex;gap:18px;margin:0;padding:0}
  nav a{color:#fff;text-decoration:none;font-weight:700}
  .form-container{max-width:700px;background:#fff;margin:40px auto;padding:28px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.08)}
  .form-container h2{text-align:center;margin-bottom:18px;color:#222}
  .form-group{margin-bottom:12px}
  label{display:block;font-weight:700;margin-bottom:6px;color:#444}
  input[type="text"], input[type="tel"], textarea, input[type="file"]{width:100%;padding:10px;border-radius:6px;border:1px solid #ccc}
  textarea{min-height:110px;resize:vertical}
  .btn-submit{display:block;width:100%;background:#222;color:#fff;padding:12px;border:none;border-radius:8px;font-weight:700;cursor:pointer}
  .btn-submit:hover{background:#f4c542;color:#222}
  footer{background:#f4c542;color:#fff;padding:18px;text-align:center;margin-top:30px}
  .message{text-align:center;margin-bottom:12px}
  .success{color:green;font-weight:600;text-align:center;}
  .success a{color:#222;font-weight:bold;text-decoration:none;}
</style>
</head>
<body>

<header>
  <img src="logo.jpg" alt="SwiftPOA Logo">
  <nav>
    <ul>
      <li><a href="customer.php"><i class="fas fa-home"></i> Back to Dashboard</a></li>
    </ul>
  </nav>
</header>

<div class="form-container">
  <h2><i class="fas fa-box"></i> Place an Order</h2>

  <?php
    if (!empty($message)) echo "<div class='message'>{$message}</div>";
    if ($success) {
        echo "
        <div class='success'>
            <p><i class='fas fa-check-circle'></i> Order placed successfully!</p>
            <p><strong>Tracking Number:</strong> {$tracking_number}</p>
            <p><a href='my_orders.php'>View My Orders</a></p>
        </div>";
    }
  ?>

  <?php if (!$success): ?>
  <form method="post" enctype="multipart/form-data" action="">
    <div class="form-group">
      <label for="pickup"><i class="fas fa-map-marker-alt"></i> Pickup Location</label>
      <input id="pickup" name="pickup" type="text" required>
    </div>

    <div class="form-group">
      <label for="drop"><i class="fas fa-location-arrow"></i> Drop Location</label>
      <input id="drop" name="drop" type="text" required>
    </div>

    <div class="form-group">
      <label for="description"><i class="fas fa-align-left"></i> Parcel Description</label>
      <textarea id="description" name="description" required></textarea>
    </div>

    <div class="form-group">
      <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
      <input id="phone" name="phone" type="tel" required placeholder="e.g. 0706 516 771">
    </div>

    <div class="form-group">
      <label for="image"><i class="fas fa-image"></i> Upload Parcel Image</label>
      <input id="image" name="image" type="file" accept="image/*" required>
    </div>

    <button class="btn-submit" type="submit"><i class="fas fa-paper-plane"></i> Submit Order</button>
  </form>
  <?php endif; ?>
</div>

<footer>
  <p>&copy; <?php echo date('Y'); ?> SwiftPOA. All Rights Reserved.</p>
  <p><i class="fas fa-map-marker-alt"></i> KTDA Plaza | 
     <a href="tel:+254700688470"><i class="fas fa-phone"></i> +254 700 688470</a> | 
     <a href="mailto:Shimsheldon24@gmail.com"><i class="fas fa-envelope"></i> Shimsheldon24@gmail.com</a></p>
  <p><a href="www.vision-hub.com">By Visionhub</a></p>
</footer>

</body>
</html>
