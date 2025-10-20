<?php
include('db.php');
$conn = db();

// Mark message as read when the button is clicked
if (isset($_GET['mark_read'])) {
    $id = intval($_GET['mark_read']);
    
    // Update message status safely
    $stmt = $conn->prepare("UPDATE messages SET status = 'read' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Redirect back to avoid reloading issue on refresh
    header("Location: admin_messages.php");
    exit;
}

// Fetch all messages in descending order
$result = $conn->query("SELECT * FROM messages ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Messages - SwiftPOA Admin</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #fafafa;
      margin: 20px;
    }
    h1 {
      text-align: center;
      color: #333;
    }
    .actions {
      text-align: center;
      margin-bottom: 20px;
    }
    .actions a {
      text-decoration: none;
      background: #f4c20d;
      color: #000;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: bold;
      transition: background 0.3s;
    }
    .actions a:hover {
      background: #e0b10c;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    th, td {
      border-bottom: 1px solid #ddd;
      padding: 12px;
      text-align: left;
    }
    th {
      background: #f4c20d;
      color: black;
      font-weight: bold;
    }
    tr:nth-child(even) {
      background: #f9f9f9;
    }
    .status {
      font-weight: bold;
      text-transform: capitalize;
    }
    .status.unread {
      color: red;
    }
    .status.read {
      color: green;
    }
    .btn-read {
      background: #4CAF50;
      color: white;
      padding: 6px 10px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 14px;
    }
    .btn-read:hover {
      background: #3e8e41;
    }
  </style>
</head>
<body>

  <h1>📩 System Messages</h1>

  <div class="actions">
    <a href="dashboard.php">⬅ Back to Dashboard</a>
  </div>

  <table>
    <tr>
      <th>ID</th>
      <th>Message</th>
      <th>Performed By</th>
      <th>Status</th>
      <th>Date</th>
      <th>Action</th>
    </tr>
    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['message']) ?></td>
          <td>
            <?php
              $user = "Unknown";
              if (!empty($row['customer_id'])) {
                $uid = intval($row['customer_id']);
                $u = $conn->query("
                  SELECT name FROM vendors WHERE id=$uid
                  UNION
                  SELECT name FROM riders WHERE id=$uid
                  UNION
                  SELECT name FROM staffs WHERE id=$uid
                  UNION
                  SELECT name FROM admins WHERE id=$uid
                  LIMIT 1
                ");
                if ($u && $u->num_rows > 0) {
                  $user = $u->fetch_assoc()['name'];
                }
              }
              echo htmlspecialchars($user);
            ?>
          </td>
          <td class="status <?= $row['status'] == 'read' ? 'read' : 'unread' ?>">
            <?= htmlspecialchars($row['status']) ?>
          </td>
          <td><?= $row['created_at'] ?></td>
          <td>
            <?php if ($row['status'] !== 'read'): ?>
              <a href="?mark_read=<?= $row['id'] ?>" class="btn-read">Mark as Read</a>
            <?php else: ?>
              ✅
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="6" style="text-align:center;">No messages found.</td></tr>
    <?php endif; ?>
  </table>

</body>
</html>
