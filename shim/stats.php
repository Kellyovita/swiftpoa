<?php
include('db.php');
$conn = db();

// Handle AJAX requests for real-time updates
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    $data = [
        'vendors'   => $conn->query("SELECT COUNT(*) AS c FROM vendors")->fetch_assoc()['c'],
        'riders'    => $conn->query("SELECT COUNT(*) AS c FROM riders")->fetch_assoc()['c'],
        'staffs'    => $conn->query("SELECT COUNT(*) AS c FROM staffs")->fetch_assoc()['c'],
        'pending'   => $conn->query("SELECT COUNT(*) AS c FROM orders WHERE status='pending'")->fetch_assoc()['c'],
        'fulfilled' => $conn->query("SELECT COUNT(*) AS c FROM orders WHERE status='fulfilled'")->fetch_assoc()['c'],
        'cancelled' => $conn->query("SELECT COUNT(*) AS c FROM orders WHERE status='cancelled'")->fetch_assoc()['c'],
        'total'     => $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'],
    ];

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>System Statistics - SwiftPOA Admin</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f7f7f7;
      color: #333;
      margin: 0;
      padding: 20px;
    }

    h1 {
      text-align: center;
      color: #222;
      margin-bottom: 30px;
    }

    .stats-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin: 20px auto;
      max-width: 1000px;
    }

    .stat-box {
      background: #f4c20d;
      padding: 20px;
      border-radius: 10px;
      text-align: center;
      font-size: 20px;
      font-weight: bold;
      color: black;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
      transition: transform 0.2s ease-in-out, background 0.3s;
    }

    .stat-box:hover {
      transform: scale(1.05);
    }

    .updated {
      background: #34a853 !important;
      color: #fff;
    }
  </style>
</head>
<body>
  <h1>System Statistics</h1>

  <div class="stats-container" id="statsContainer">
    <div class="stat-box">Loading data...</div>
  </div>

  <script>
    let previousData = {};

    function loadStats() {
      fetch('stats.php?action=fetch')
        .then(response => response.json())
        .then(data => {
          const container = document.getElementById('statsContainer');
          container.innerHTML = `
            <div class="stat-box" id="vendors">Vendors: ${data.vendors}</div>
            <div class="stat-box" id="riders">Riders: ${data.riders}</div>
            <div class="stat-box" id="staffs">Staffs: ${data.staffs}</div>
            <div class="stat-box" id="pending">Orders (Pending): ${data.pending}</div>
            <div class="stat-box" id="fulfilled">Orders (Fulfilled): ${data.fulfilled}</div>
            <div class="stat-box" id="cancelled">Orders (Cancelled): ${data.cancelled}</div>
            <div class="stat-box" id="total">Total Orders: ${data.total}</div>
          `;

          // Highlight changes
          for (let key in data) {
            if (previousData[key] !== undefined && previousData[key] != data[key]) {
              const box = document.getElementById(key);
              box.classList.add('updated');
              setTimeout(() => box.classList.remove('updated'), 1000);
            }
          }

          previousData = data;
        })
        .catch(error => {
          console.error('Error loading stats:', error);
        });
    }

    // Load stats immediately and then refresh every 5 seconds
    loadStats();
    setInterval(loadStats, 5000);
  </script>
</body>
</html>
