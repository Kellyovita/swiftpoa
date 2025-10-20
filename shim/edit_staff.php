<?php
include __DIR__ . '/db.php';
$conn = db();

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: staffs.php?msg=Invalid+staff+ID");
    exit();
}

// Fetch staff data
$stmt = $conn->prepare("SELECT * FROM staff WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

if (!$staff) {
    echo "Staff not found.";
    exit();
}

// Update staff if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role  = $_POST['role'];

    $update = $conn->prepare("UPDATE staff SET name=?, email=?, phone=?, role=? WHERE id=?");
    $update->bind_param("ssssi", $name, $email, $phone, $role, $id);

    if ($update->execute()) {
        header("Location: staffs.php?msg=Staff+updated+successfully");
        exit();
    } else {
        echo "Error updating staff: " . $update->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Staff - SwiftPOA Admin</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    body {
        font-family: Arial, sans-serif;
        background: #f9f9f9;
        margin: 0;
        padding: 0;
    }
    .container {
        max-width: 500px;
        margin: 50px auto;
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        text-align: center;
    }
    .logo {
        width: 80px;
        margin-bottom: 20px;
    }
    h1 {
        color: #333;
        margin-bottom: 20px;
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
        border: 1px solid #ddd;
        border-radius: 6px;
        margin-bottom: 15px;
    }
    button {
        background: #ffcc00;
        color: #000;
        font-weight: bold;
        border: none;
        padding: 12px 20px;
        border-radius: 8px;
        cursor: pointer;
        width: 100%;
        margin-top: 10px;
    }
    button:hover {
        background: #e6b800;
    }
    .btn {
        display: inline-block;
        margin-top: 15px;
        text-decoration: none;
        background: #444;
        color: #fff;
        padding: 10px 16px;
        border-radius: 8px;
    }
    .btn:hover {
        background: #222;
    }
  </style>
</head>
<body>
  <div class="container">
    <img src="logo.jpg" alt="SwiftPOA Logo" class="logo">
    <h1>Edit Staff</h1>

    <form method="POST">
      <label>Name:</label>
      <input type="text" name="name" value="<?= htmlspecialchars($staff['name']) ?>" required>

      <label>Email:</label>
      <input type="email" name="email" value="<?= htmlspecialchars($staff['email']) ?>" required>

      <label>Phone:</label>
      <input type="text" name="phone" value="<?= htmlspecialchars($staff['phone']) ?>">

      <label>Role:</label>
      <input type="text" name="role" value="<?= htmlspecialchars($staff['role']) ?>">

      <button type="submit">Update Staff</button>
    </form>
    <a href="staffs.php" class="btn">⬅ Back to Staff List</a>
  </div>
</body>
</html>
