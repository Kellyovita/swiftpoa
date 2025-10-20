<?php
include __DIR__ . '/db.php';
$conn = db();

// Fetch Riders
$result = $conn->query("SELECT * FROM riders");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Riders - SwiftPOA Admin</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1 { color: #333; }
    .btn {
      display: inline-block;
      background: #f4c542;
      color: black;
      padding: 10px 15px;
      text-decoration: none;
      font-weight: bold;
      border-radius: 5px;
      margin-bottom: 15px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    th, td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: left;
    }
    th {
      background: #f4c542;
      color: black;
    }
    .actions a {
      margin: 0 5px;
      padding: 5px 10px;
      border-radius: 5px;
      text-decoration: none;
      font-size: 14px;
    }
    .edit {
      background: #4CAF50;
      color: white;
    }
    .delete {
      background: #f44336;
      color: white;
    }
    .edit:hover { background: #45a049; }
    .delete:hover { background: #da190b; }
  </style>
</head>
<body>
  <h1>Riders</h1>

  <!-- Back to Dashboard button -->
  <a href="dashboard.php" class="btn">⬅ Back to Dashboard</a>

  <!-- Add Rider button -->
  <a href="add_rider.php" class="btn">+ Add Rider</a>

  <table>
    <tr>
      <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Location</th><th>Status</th><th>Actions</th>
    </tr>
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['id']) ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td><?= htmlspecialchars($row['location']) ?></td>
        <td><?= htmlspecialchars($row['status']) ?></td>
        <td class="actions">
          <a href="edit_rider.php?id=<?= $row['id'] ?>" class="edit">✏ Edit</a>
          <a href="delete_rider.php?id=<?= $row['id'] ?>" class="delete" onclick="return confirm('Are you sure you want to delete this rider?');">🗑 Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="7">No riders found.</td></tr>
    <?php endif; ?>
  </table>
</body>
</html>
