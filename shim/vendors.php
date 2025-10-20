<?php
// Always include db.php safely
include __DIR__ . '/db.php';

// Connect to DB
$conn = db();

// Fetch Vendors
$result = $conn->query("SELECT * FROM vendors");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Vendors - SwiftPOA Admin</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f9f9f9;
      margin: 0;
      padding: 20px;
    }
    h1 {
      color: #333;
    }
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
    table th, table td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: left;
    }
    table th {
      background: #f4c542;
      color: black;
    }
    .actions a {
      margin: 0 5px;
      padding: 6px 12px;
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
  <h1>Vendors</h1>

  <!-- Back to Dashboard button -->
  <a href="dashboard.php" class="btn">⬅ Back to Dashboard</a>

  <!-- Add Vendor button -->
  <a href="add_vendor.php" class="btn">+ Add Vendor</a>

  <table>
    <tr>
      <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Location</th><th>Actions</th>
    </tr>
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['id']) ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td><?= htmlspecialchars($row['location']) ?></td>
        <td class="actions">
          <a href="edit_vendor.php?id=<?= $row['id'] ?>" class="edit">✏ Edit</a>
          <a href="delete_vendor.php?id=<?= $row['id'] ?>" class="delete" onclick="return confirm('Are you sure you want to delete this vendor?');">🗑 Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="6">No vendors found.</td></tr>
    <?php endif; ?>
  </table>
</body>
</html>
