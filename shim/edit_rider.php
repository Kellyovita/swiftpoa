<?php
include __DIR__ . '/db.php';
$conn = db();

if (!isset($_GET['id'])) die("Rider ID missing.");
$id = intval($_GET['id']);

$result = $conn->prepare("SELECT * FROM riders WHERE id = ?");
$result->bind_param("i", $id);
$result->execute();
$rider = $result->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $location = $_POST['location'];
    $status = $_POST['status'];

    $update = $conn->prepare("UPDATE riders SET name=?, email=?, phone=?, location=?, status=? WHERE id=?");
    $update->bind_param("sssssi", $name, $email, $phone, $location, $status, $id);
    $update->execute();

    header("Location: riders.php?success=updated");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit Rider</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px; }
    form { max-width: 500px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; }
    label { display: block; margin-top: 10px; font-weight: bold; }
    input { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; }
    button { margin-top: 15px; padding: 10px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; }
    a { display: inline-block; margin-top: 15px; padding: 10px; background: #f4c542; text-decoration: none; border-radius: 5px; }
  </style>
</head>
<body>
  <h1>Edit Rider</h1>
  <form method="POST">
    <label>Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($rider['name']) ?>" required>
    
    <label>Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($rider['email']) ?>" required>
    
    <label>Phone:</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($rider['phone']) ?>" required>
    
    <label>Location:</label>
    <input type="text" name="location" value="<?= htmlspecialchars($rider['location']) ?>" required>
    
    <label>Status:</label>
    <input type="text" name="status" value="<?= htmlspecialchars($rider['status']) ?>" required>

    <button type="submit">💾 Save Changes</button>
    <a href="riders.php">⬅ Back</a>
  </form>
</body>
</html>
