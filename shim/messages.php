<?php
include('db.php');
$conn = db();

// Fetch all messages
$result = $conn->query("SELECT * FROM messages ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Messages - SwiftPOA Admin</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    th { background: #f4c20d; color: black; }
    tr:nth-child(even) { background: #f9f9f9; }
  </style>
</head>
<body>
  <h1>Messages</h1>

  <table>
    <tr>
      <th>ID</th>
      <th>Sender Name</th>
      <th>Email</th>
      <th>Message</th>
      <th>Date</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= $row['id'] ?></td>
      <td><?= $row['sender_name'] ?></td>
      <td><?= $row['sender_email'] ?></td>
      <td><?= $row['message'] ?></td>
      <td><?= $row['created_at'] ?></td>
    </tr>
    <?php endwhile; ?>
  </table>
</body>
</html>
