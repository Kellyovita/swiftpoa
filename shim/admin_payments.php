<?php
require_once __DIR__ . '/includes/db.php';
$conn = db();

// Initialize filters
$search = trim($_GET['search'] ?? '');
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$export = isset($_GET['export']) && $_GET['export'] === 'csv';

// Base query
$sql = "SELECT id, order_id, amount, transaction_ref, created_at FROM payments_new WHERE 1";

// Apply filters
$params = [];
$types = '';

if (!empty($search)) {
    $sql .= " AND (order_id LIKE ? OR transaction_ref LIKE ?)";
    $types .= 'ss';
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}
if (!empty($date_from)) {
    $sql .= " AND DATE(created_at) >= ?";
    $types .= 's';
    $params[] = $date_from;
}
if (!empty($date_to)) {
    $sql .= " AND DATE(created_at) <= ?";
    $types .= 's';
    $params[] = $date_to;
}

$sql .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// 🟡 CSV Export Handling
if ($export) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="payments_export.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Order ID', 'Amount (Ksh)', 'Transaction Ref', 'Date', 'Time']);

    while ($row = $result->fetch_assoc()) {
        $datetime = strtotime($row['created_at']);
        fputcsv($output, [
            $row['id'],
            $row['order_id'],
            number_format($row['amount'], 2),
            $row['transaction_ref'],
            date("Y-m-d", $datetime),
            date("H:i:s", $datetime)
        ]);
    }
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payments - SwiftPOA</title>
<style>
* { box-sizing: border-box; margin:0; padding:0; font-family: Arial, sans-serif; }
body {
  display: flex;
  height: 100vh;
  background: #f7f7f7;
}
.sidebar {
  width: 220px;
  background: #000;
  color: #fff;
  display: flex;
  flex-direction: column;
  padding-top: 20px;
}
.sidebar .logo { text-align: center; margin-bottom: 30px; }
.sidebar .logo img { width: 100px; margin-bottom: 10px; }
.sidebar .logo h2 { color: #ffb400; }
.sidebar .menu { list-style: none; padding-left: 0; }
.sidebar .menu li { margin: 15px 0; }
.sidebar .menu li a {
  text-decoration: none;
  color: #fff;
  padding: 10px 20px;
  display: block;
  border-radius: 5px;
  transition: 0.3s;
}
.sidebar .menu li a:hover, .sidebar .menu li a.active { background: #ffb400; color: #000; }
.sidebar .menu li a.logout { margin-top: 50px; background: #ff4d4d; }
.sidebar .menu li a.logout:hover { background: #cc0000; }

.main-content {
  flex: 1;
  padding: 20px 30px;
  overflow-y: auto;
}
h1 {
  margin-bottom: 20px;
  color: #333;
}
.actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-bottom: 10px;
}
.actions a, .actions button {
  background: #ffb400;
  border: none;
  padding: 8px 14px;
  color: #000;
  text-decoration: none;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.3s;
}
.actions a:hover, .actions button:hover {
  background: #e09c00;
}

.filter-form {
  background: #fff;
  padding: 15px;
  border-radius: 10px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  margin-bottom: 20px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px;
}
.filter-form input[type="text"], 
.filter-form input[type="date"] {
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 5px;
  flex: 1;
  min-width: 150px;
}
.filter-form button {
  padding: 8px 16px;
  background: #ffb400;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-weight: bold;
}
.filter-form button:hover { background: #e09c00; }
.filter-form a.reset {
  color: #ff4d4d;
  text-decoration: none;
  font-weight: bold;
}

table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
  box-shadow: 0 3px 6px rgba(0,0,0,0.1);
  border-radius: 8px;
  overflow: hidden;
}
th, td {
  padding: 12px 15px;
  border-bottom: 1px solid #ddd;
  text-align: left;
}
th {
  background: #ffb400;
  color: #000;
  font-weight: bold;
}
tr:hover { background: #f1f1f1; }

@media(max-width:768px){
  body { flex-direction: column; }
  .sidebar { width: 100%; flex-direction: row; overflow-x: auto; }
  .sidebar .menu { display: flex; flex-direction: row; }
  .sidebar .menu li { margin: 0 10px; }
  .main-content { padding: 10px; }
  .filter-form { flex-direction: column; align-items: stretch; }
}

@media print {
  .sidebar, .filter-form, .actions { display: none; }
  body { background: #fff; }
  h1 { text-align: center; margin-bottom: 20px; }
  table { box-shadow: none; }
}
</style>
<script>
function printReport() {
  window.print();
}
</script>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
  <div class="logo">
    <img src="logo.jpg" alt="SwiftPOA Logo">
    <h2>SwiftPOA</h2>
  </div>
  <ul class="menu">
    <li><a href="staff.php">Dashboard</a></li>
    <li><a href="staff_orders.php">Orders</a></li>
    <li><a href="assign_rider.php">Assign Orders</a></li>
    <li><a href="fees.php">Transportation Fee</a></li>
    <li><a href="payments.php" class="active">Payments</a></li>
    <li><a href="staff_message.php">Messages</a></li>
    <li><a href="staff_profile.php">Profile</a></li>
    <li><a href="logout.php" class="logout">Logout</a></li>
  </ul>
</aside>

<!-- Main -->
<main class="main-content">
  <h1>Payments</h1>

  <div class="actions">
    <a href="?<?php echo http_build_query(array_merge($_GET, ['export' => 'csv'])); ?>">⬇ Export CSV</a>
    <button type="button" onclick="printReport()">🖨 Print Report</button>
  </div>

  <form method="get" class="filter-form">
    <input type="text" name="search" placeholder="Search Order ID or Transaction Ref" value="<?php echo htmlspecialchars($search); ?>">
    <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
    <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
    <button type="submit">Filter</button>
    <a href="payments.php" class="reset">Reset</a>
  </form>

  <table>
    <tr>
      <th>ID</th>
      <th>Order ID</th>
      <th>Amount (Ksh)</th>
      <th>Transaction Reference</th>
      <th>Date</th>
      <th>Time</th>
    </tr>
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()):
        $datetime = strtotime($row['created_at']);
        $date = date("Y-m-d", $datetime);
        $time = date("H:i:s", $datetime);
      ?>
      <tr>
        <td><?php echo htmlspecialchars($row['id']); ?></td>
        <td><?php echo htmlspecialchars($row['order_id']); ?></td>
        <td>Ksh <?php echo number_format($row['amount'], 2); ?></td>
        <td><?php echo htmlspecialchars($row['transaction_ref']); ?></td>
        <td><?php echo $date; ?></td>
        <td><?php echo $time; ?></td>
      </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="6">No payment records found.</td></tr>
    <?php endif; ?>
  </table>
</main>

</body>
</html>
