<?php
include __DIR__ . '/db.php';
$conn = db();

// Fetch all staff (use correct table name and fallback if created_at doesn't exist)
$result = $conn->query("SELECT * FROM staff ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Staffs - SwiftPOA Admin</title>
  <link rel="stylesheet" href="dashboard.css">
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
    .btn {
        display: inline-block;
        margin-bottom: 15px;
        padding: 8px 16px;
        background: #ffcc00;
        color: #000;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
    }
    .btn:hover {
        background: #e6b800;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    th, td {
        padding: 12px;
        border: 1px solid #ddd;
        text-align: center;
    }
    th {
        background: #222;
        color: #fff;
    }
    tr:nth-child(even) {
        background: #f2f2f2;
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
  <h1>Staffs</h1>

  <!-- Back to Dashboard button -->
  <a href="dashboard.php" class="btn">⬅ Back to Dashboard</a>

  <!-- Add Staff button -->
  <a href="add_staff.php" class="btn">+ Add Staff</a>

  <table>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Email</th>
      <th>Phone</th>
      <th>Role</th>
      <th>Created At</th>
      <th>Actions</th>
    </tr>
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['id']) ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td><?= htmlspecialchars($row['role']) ?></td>
        <td>
          <?= isset($row['created_at']) ? htmlspecialchars($row['created_at']) : 'N/A' ?>
        </td>
        <td class="actions">
          <a href="edit_staff.php?id=<?= $row['id'] ?>" class="edit">✏ Edit</a>
          <a href="delete_staff.php?id=<?= $row['id'] ?>" class="delete" onclick="return confirm('Are you sure you want to delete this staff?');">🗑 Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr>
        <td colspan="7">No staff members found.</td>
      </tr>
    <?php endif; ?>
  </table>
</body>
</html>
