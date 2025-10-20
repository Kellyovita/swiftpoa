<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$conn = db();

// Make sure rider is logged in
if (!isset($_SESSION['rider_id'])) {
    die("<p style='color:red;text-align:center;'>Rider not logged in. Please log in first.</p>");
}

$rider_id = $_SESSION['rider_id'];

// Fetch all payments linked to this rider’s orders
$query = "
    SELECT 
        payments.id,
        payments.order_id,
        payments.amount,
        payments.payment_method,
        payments.payment_status,
        payments.created_at,
        customers.name AS customer_name,
        orders.pickup_location,
        orders.drop_location
    FROM payments
    INNER JOIN orders ON payments.order_id = orders.id
    INNER JOIN customers ON payments.customer_id = customers.id
    WHERE orders.rider_id = ?
    ORDER BY payments.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $rider_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rider Payments | SwiftPOA</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f5f5f5;
    margin: 0;
    padding: 0;
}
.container {
    width: 90%;
    margin: 40px auto;
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
}
h2 {
    color: #333;
    text-align: center;
    margin-bottom: 20px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 12px 10px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}
th {
    background: #ffb400;
    color: #000;
}
tr:hover {
    background: #f9f9f9;
}
.status-completed {
    color: green;
    font-weight: bold;
}
.status-pending {
    color: red;
    font-weight: bold;
}
.print-btn {
    display: inline-block;
    background: #ffb400;
    color: #000;
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    margin-top: 15px;
    transition: 0.3s;
}
.print-btn:hover {
    background: #e09b00;
}
@media(max-width:768px) {
    .container {
        width: 98%;
        padding: 15px;
    }
    th, td {
        font-size: 14px;
    }
}
</style>
</head>
<body>
<div class="container">
    <h2>My Payment Records</h2>

    <table>
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Pickup</th>
                <th>Drop</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['pickup_location']); ?></td>
                    <td><?php echo htmlspecialchars($row['drop_location']); ?></td>
                    <td><?php echo number_format($row['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                    <td class="status-<?php echo strtolower($row['payment_status']); ?>">
                        <?php echo htmlspecialchars($row['payment_status']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="9" style="text-align:center;color:#666;">No payment records found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div style="text-align:center;">
        <a href="#" class="print-btn" onclick="window.print()">🖨️ Print Report</a>
    </div>
</div>
</body>
</html>
