<?php
// confirm_order.php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "swiftpoa";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$order = null;
$message = "";

// Fetch order details
if (isset($_GET['id'])) {
    $order_id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM orders WHERE id = $order_id LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $order = $result->fetch_assoc();
    } else {
        $message = "<p style='color:red;text-align:center;'>❌ Order not found!</p>";
    }
} else {
    $message = "<p style='color:red;text-align:center;'>❌ No order specified!</p>";
}

// ================= STK PUSH FUNCTION ===================
function stkPush($phone, $amount, $order_id) {
    // Clean phone number to Safaricom format
    $phone = preg_replace('/^0/', '254', $phone); // Replace 07... with 2547...

    // Safaricom Daraja credentials
    $consumerKey    = "pOG45QsGqI5imMi3V8bJPA6scnLo3HwVGtXb5HuZ7G1FckfS";
    $consumerSecret = "GHKcXrHwbgWBQiDXRjaMBSZIrfeQP4BHbGSyTO0PU36pV4WL2GroveF99FIG1x6vT";
    $businessShortCode = "6913419"; // Test Paybill/Till
    $passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919"; // from Safaricom Daraja portal

    // Generate timestamp and password
    $timestamp = date("YmdHis");
    $password = base64_encode($businessShortCode . $passkey . $timestamp);

    // Get access token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic " . base64_encode("$consumerKey:$consumerSecret")]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $token = json_decode($response)->access_token ?? null;
    curl_close($ch);

    if (!$token) return false;

    // Prepare STK push request
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest");
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $token"
    ]);

    $callback_url = "https://ovhub.co.ke/callback_url.php"; // change for live site

    $stkData = [
        "BusinessShortCode" => $businessShortCode,
        "Password" => $password,
        "Timestamp" => $timestamp,
        "TransactionType" => "CustomerPayBillOnline",
        "Amount" => $amount,
        "PartyA" => $phone,
        "PartyB" => $businessShortCode,
        "PhoneNumber" => $phone,
        "CallBackURL" => $callback_url,
        "AccountReference" => "SWIFTPOA ORDER $order_id",
        "TransactionDesc" => "Payment for order $order_id"
    ];

    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($stkData));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);

    return $response;
}
// =======================================================

// Handle confirmation + STK push trigger
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['phone'])) {
    $phone = $conn->real_escape_string($_POST['phone']);
    $amount = 50; // example static amount, you can fetch from DB later

    // Update phone in DB (if changed)
    $conn->query("UPDATE orders SET customer_phone = '$phone' WHERE id = $order_id");

    // Trigger STK Push
    $stkResponse = stkPush($phone, $amount, $order_id);

    if ($stkResponse) {
        $message = "<p style='color:green;font-weight:bold;text-align:center;'>✅ STK Push sent to $phone. Please complete payment on your phone.</p>";
    } else {
        $message = "<p style='color:red;text-align:center;'>❌ Failed to initiate STK Push.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Confirm Order - SwiftPOA</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {font-family: Arial, sans-serif; margin:0; background:#f9f9f9; color:#333;}
    header {background:#222; color:#fff; padding:15px 50px; display:flex; justify-content:space-between; align-items:center;}
    header img {height:40px;}
    nav ul {list-style:none; display:flex; gap:20px;}
    nav ul li a {color:#fff; text-decoration:none; font-weight:bold;}
    nav ul li a:hover {color:#f4c542;}
    .container {max-width:700px; background:#fff; margin:40px auto; padding:25px; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.1);}
    h2 {text-align:center; margin-bottom:20px;}
    table {width:100%; border-collapse:collapse; margin-bottom:20px;}
    table td {padding:10px; border-bottom:1px solid #ddd;}
    .form-group {margin-bottom:15px;}
    label {display:block; margin-bottom:6px; font-weight:bold;}
    input[type="tel"] {width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;}
    button {width:100%; background:#222; color:#fff; padding:12px; border:none; border-radius:6px; font-size:1rem; cursor:pointer;}
    button:hover {background:#f4c542; color:#222;}
    footer {background:#222; color:#fff; padding:20px; text-align:center; margin-top:30px;}
  </style>
</head>
<body>
  <header>
    <img src="logo.jpg" alt="SwiftPOA Logo">
    <nav>
      <ul>
        <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="index.php#about"><i class="fas fa-info-circle"></i> About</a></li>
        <li><a href="index.php#contact"><i class="fas fa-phone"></i> Contact</a></li>
      </ul>
    </nav>
  </header>

  <div class="container">
    <h2><i class="fas fa-check-circle"></i> Confirm Your Order</h2>
    <?php echo $message; ?>

    <?php if ($order): ?>
      <table>
        <tr><td><strong>Pickup:</strong></td><td><?php echo htmlspecialchars($order['pickup_location']); ?></td></tr>
        <tr><td><strong>Drop:</strong></td><td><?php echo htmlspecialchars($order['drop_location']); ?></td></tr>
        <tr><td><strong>Description:</strong></td><td><?php echo htmlspecialchars($order['parcel_description']); ?></td></tr>
        <tr><td><strong>Phone:</strong></td><td><?php echo htmlspecialchars($order['customer_phone']); ?></td></tr>
        <tr><td><strong>Status:</strong></td><td><?php echo htmlspecialchars($order['status']); ?></td></tr>
        <?php if (!empty($order['parcel_image'])): ?>
          <tr><td><strong>Image:</strong></td><td><img src="<?php echo $order['parcel_image']; ?>" alt="Parcel" style="max-width:150px;"></td></tr>
        <?php endif; ?>
      </table>

      <form method="POST">
        <div class="form-group">
          <label for="phone"><i class="fas fa-phone"></i> Confirm Phone Number</label>
          <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($order['customer_phone']); ?>" required>
        </div>
        <button type="submit"><i class="fas fa-paper-plane"></i> Confirm & Pay</button>
      </form>
    <?php endif; ?>
  </div>

  <footer>
    <p>&copy; <?php echo date("Y"); ?> SwiftPOA. All Rights Reserved.</p>
  </footer>
</body>
</html>
