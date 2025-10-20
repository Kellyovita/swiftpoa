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
// Check if logged in as vendor
// ==========================
$vendor_id    = $_SESSION['vendor_id'] ?? null;
$vendor_name  = $_SESSION['vendor_name'] ?? 'Vendor';
$vendor_email = $_SESSION['vendor_email'] ?? '';

if (!$vendor_id) {
    header("Location: login.php");
    exit;
}

// ==========================
// Handle form submission
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup       = trim($_POST['pickup'] ?? '');
    $drop         = trim($_POST['drop'] ?? '');
    $parcel_type  = trim($_POST['parcel_type'] ?? '');
    $vendor_phone = trim($_POST['vendor_phone'] ?? '');
    $vendor_email = trim($_POST['vendor_email'] ?? '');
    $picker_phone = trim($_POST['picker_phone'] ?? '');
    $picker_email = trim($_POST['picker_email'] ?? '');

    // Input validation
    if (
        $pickup === '' || $drop === '' || $parcel_type === '' ||
        $vendor_phone === '' || $vendor_email === '' ||
        $picker_phone === '' || $picker_email === ''
    ) {
        $message = "<p style='color:red;text-align:center;'>⚠️ Please fill in all required fields.</p>";
    } else {
        $status = 'Pending';
        $tracking_number = 'VR-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

        // Insert order
        $stmt = $conn->prepare("
            INSERT INTO orders (
                vendor_id, pickup_location, drop_location, parcel_type,
                vendor_phone, vendor_email, picker_phone, picker_email, 
                status, tracking_number
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if ($stmt) {
            $stmt->bind_param(
                "isssssssss",
                $vendor_id,
                $pickup,
                $drop,
                $parcel_type,
                $vendor_phone,
                $vendor_email,
                $picker_phone,
                $picker_email,
                $status,
                $tracking_number
            );

            if ($stmt->execute()) {
                $success = true;
                $order_id = $stmt->insert_id;

                // Optional: Send message to admin
                $adminMsg = "📦 New Rider Request from $vendor_name\n"
                          . "Pickup: $pickup\nDrop: $drop\nParcel: $parcel_type\n"
                          . "Vendor: $vendor_email | $vendor_phone\n"
                          . "Picker: $picker_email | $picker_phone\nStatus: Pending";

                $msgStmt = $conn->prepare("INSERT INTO messages (order_id, message, is_admin) VALUES (?, ?, 1)");
                if ($msgStmt) {
                    $msgStmt->bind_param("is", $order_id, $adminMsg);
                    $msgStmt->execute();
                    $msgStmt->close();
                }
            } else {
                $message = "<p style='color:red;text-align:center;'>❌ Error: " . htmlspecialchars($stmt->error) . "</p>";
            }

            $stmt->close();
        } else {
            $message = "<p style='color:red;text-align:center;'>❌ Prepare failed: " . htmlspecialchars($conn->error) . "</p>";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Request Rider | SwiftPOA</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  body{font-family:Arial,Helvetica,sans-serif;margin:0;background:#f9f9f9;color:#333}
  header{background:#000;color:#ffb400;padding:15px 30px;display:flex;justify-content:space-between;align-items:center}
  header img{height:40px}
  nav ul{list-style:none;display:flex;gap:18px;margin:0;padding:0}
  nav a{color:#ffb400;text-decoration:none;font-weight:700}
  .form-container{max-width:700px;background:#fff;margin:40px auto;padding:28px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.08)}
  .form-container h2{text-align:center;margin-bottom:18px;color:#000}
  .form-group{margin-bottom:12px}
  label{display:block;font-weight:700;margin-bottom:6px;color:#444}
  input[type="text"], input[type="tel"], input[type="email"], textarea{width:100%;padding:10px;border-radius:6px;border:1px solid #ccc}
  textarea{min-height:80px;resize:vertical}
  .btn-submit{display:block;width:100%;background:#ffb400;color:#000;padding:12px;border:none;border-radius:8px;font-weight:700;cursor:pointer}
  .btn-submit:hover{background:#000;color:#ffb400}
  footer{background:#000;color:#ffb400;padding:18px;text-align:center;margin-top:30px}
  .message{text-align:center;margin-bottom:12px}
  .success{color:green;font-weight:600;text-align:center;}
  .success a{color:#000;font-weight:bold;text-decoration:none;}
</style>
</head>
<body>

<header>
  <img src="logo.jpg" alt="SwiftPOA Logo">
  <nav>
    <ul>
      <li><a href="vendor.php"><i class="fas fa-home"></i> Back to Dashboard</a></li>
    </ul>
  </nav>
</header>

<div class="form-container">
  <h2><i class="fas fa-motorcycle"></i> Request Rider</h2>

  <?php
    if (!empty($message)) echo "<div class='message'>{$message}</div>";
    if ($success) {
        echo "
        <div class='success'>
            <p><i class='fas fa-check-circle'></i> Rider request submitted successfully!</p>
            <p><strong>Tracking Number:</strong> {$tracking_number}</p>
            <p><a href='vendor.php'>Back to Dashboard</a></p>
        </div>";
    }
  ?>

  <?php if (!$success): ?>
  <form method="post" action="">
    <div class="form-group">
      <label for="pickup"><i class="fas fa-map-marker-alt"></i> Pickup Location</label>
      <input id="pickup" name="pickup" type="text" required>
    </div>

    <div class="form-group">
      <label for="drop"><i class="fas fa-location-arrow"></i> Drop Location</label>
      <input id="drop" name="drop" type="text" required>
    </div>

    <div class="form-group">
      <label for="parcel_type"><i class="fas fa-box"></i> Type of Parcel</label>
      <input id="parcel_type" name="parcel_type" type="text" placeholder="e.g. Documents, Electronics" required>
    </div>

    <h3>Vendor Details</h3>
    <div class="form-group">
      <label for="vendor_phone"><i class="fas fa-phone"></i> Vendor Phone</label>
      <input id="vendor_phone" name="vendor_phone" type="tel" required placeholder="e.g. 0706 516 771">
    </div>
    <div class="form-group">
      <label for="vendor_email"><i class="fas fa-envelope"></i> Vendor Email</label>
      <input id="vendor_email" name="vendor_email" type="email" required value="<?= htmlspecialchars($vendor_email) ?>">
    </div>

    <h3>Picker Details</h3>
    <div class="form-group">
      <label for="picker_phone"><i class="fas fa-phone"></i> Picker Phone</label>
      <input id="picker_phone" name="picker_phone" type="tel" required placeholder="e.g. 0712 345 678">
    </div>
    <div class="form-group">
      <label for="picker_email"><i class="fas fa-envelope"></i> Picker Email</label>
      <input id="picker_email" name="picker_email" type="email" required placeholder="e.g. picker@example.com">
    </div>

    <button class="btn-submit" type="submit"><i class="fas fa-paper-plane"></i> Submit Request</button>
  </form>
  <?php endif; ?>
</div>

<footer>
  <p>&copy; <?= date('Y'); ?> SwiftPOA. All Rights Reserved.</p>
  <p><i class="fas fa-map-marker-alt"></i> KTDA Plaza |
     <a href="tel:+254700688470" style="color:#ffb400;"><i class="fas fa-phone"></i> +254 700 688470</a> |
     <a href="mailto:Shimsheldon24@gmail.com" style="color:#ffb400;"><i class="fas fa-envelope"></i> Shimsheldon24@gmail.com</a></p>
  <p><a href="www.visionhub.co.ke" style="color:#ffb400;">By Visionhub</a></p>
</footer>

</body>
</html>
