<?php
include __DIR__ . '/db.php';
$conn = db();

if (!isset($_GET['id'])) {
    die("Vendor ID not provided.");
}

$id = intval($_GET['id']);

// Fetch vendor details
$stmt = $conn->prepare("SELECT * FROM vendors WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Vendor not found.");
}

$vendor = $result->fetch_assoc();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $location = $_POST['location'];

    $update = $conn->prepare("UPDATE vendors SET name=?, email=?, phone=?, location=? WHERE id=?");
    $update->bind_param("ssssi", $name, $email, $phone, $location, $id);

    if ($update->execute()) {
        header("Location: vendors.php?success=updated");
        exit();
    } else {
        echo "Error updating vendor.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit Vendor</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f9f9f9;
      padding: 20px;
    }
    h1 {
      text-align: center;
      color: #333;
    }
    form {
      max-width: 500px;
      margin: auto;
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    label {
      display: block;
      margin-top: 10px;
      font-weight: bold;
    }
    input {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    .btn {
      display: inline-block;
      margin-top: 15px;
      padding: 10px 15px;
      background: #4CAF50;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
    }
    .btn:hover { background: #45a049; }
    .back {
      background: #f4c542;
      color: black;
    }
    .back:hover { background: #e6b800; }
  </style>
</head>
<body>
  <h1>Edit Vendor</h1>
  <form method="POST">
    <label>Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($vendor['name']) ?>" required>

    <label>Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($vendor['email']) ?>" required>

    <label>Phone:</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($vendor['phone']) ?>" required>

    <label>Location:</label>
    <input type="text" name="location" value="<?= htmlspecialchars($vendor['location']) ?>" required>

    <button type="submit" class="btn">💾 Update</button>
    <a href="vendors.php" class="btn back">⬅ Back</a>
  </form>
</body>
</html>
