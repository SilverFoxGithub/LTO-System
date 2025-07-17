<?php
// Check if download is requested, handle CSV generation first
if (isset($_GET['download_report'])) {
    require_once '../db/db_connect.php';

    $filter = $_GET['filter'] ?? 'this_year';
    $dateCondition = '';
    switch ($filter) {
        case 'last_year':
            $dateCondition = "YEAR(created_at) = YEAR(CURDATE()) - 1";
            break;
        case 'last_6_months':
            $dateCondition = "created_at >= CURDATE() - INTERVAL 6 MONTH";
            break;
        case 'last_3_months':
            $dateCondition = "created_at >= CURDATE() - INTERVAL 3 MONTH";
            break;
        default:
            $dateCondition = "YEAR(created_at) = YEAR(CURDATE())";
            break;
    }

    $currentDateTime = date('Y-m-d_H-i-s');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="monthly_report_' . $currentDateTime . '.csv"');
    header('Cache-Control: max-age=0');

    $output = fopen('php://output', 'w');

    fprintf($output, "# Monthly Report - MSSB Driving Institute\n");
    fprintf($output, "# Generated on: %s PST\n", date('F j, Y, g:i A'));
    fputcsv($output, ['Month', 'No. of Students', 'Amount']);

    $monthlyData = [];
    $months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    foreach ($months as $month) {
        $monthlyData[$month] = ['students' => 0, 'amount' => 0.00];
    }

    $res = $conn->query("SELECT DATE_FORMAT(created_at, '%M') as month, COUNT(*) as count FROM users WHERE role = 'student' AND $dateCondition GROUP BY DATE_FORMAT(created_at, '%M')");
    while ($row = $res->fetch_assoc()) {
        $monthlyData[$row['month']]['students'] = (int)$row['count'];
    }

    $res = $conn->query("SELECT DATE_FORMAT(payment_date, '%M') as month, SUM(amount) as total FROM payments WHERE status = 'completed' AND $dateCondition GROUP BY DATE_FORMAT(payment_date, '%M')");
    while ($row = $res->fetch_assoc()) {
        $monthlyData[$row['month']]['amount'] = (float)($row['total'] ?? 0.00);
    }

    foreach ($months as $month) {
        fputcsv($output, [
            $month,
            $monthlyData[$month]['students'],
            number_format($monthlyData[$month]['amount'], 2)
        ]);
    }

    fclose($output);
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Analytics - Admin Dashboard</title>
  <link rel="stylesheet" href="../assets/styles/admin-dash.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <style>
    .analytics-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      padding: 20px;
    }

    .summary-card {
      background-color: #fff;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 20px;
      text-align: center;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .summary-card h3 {
      margin: 0 0 10px;
      font-size: 18px;
      color: #333;
    }

    .summary-card p {
      font-size: 24px;
      color: #4caf50;
      margin: 0;
    }

    .chart-container {
      background-color: #fff;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      max-width: 500px;
      margin: 0 auto;
    }

    canvas {
      max-width: 100%;
    }

    .report-button, select#filterRange {
      margin: 10px;
      padding: 10px 15px;
      font-size: 16px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="sidebar">
  <div class="sidebar-top">
    <img src="../assets/logo.png" alt="MSSB Driving Institute Logo" class="logo" />
    <button onclick="window.location.href='admin.php'">Dashboard</button>
    <button onclick="window.location.href='admin_paymment.php'">Payments</button>
    <button onclick="window.location.href='analytics_admin.php'">Analytics</button>
  </div>
  <button id="logoutBtn" onclick="logout()">Logout</button>
</div>
    <div class="main-content">
      <div class="header">
        <h1>Analytics Dashboard</h1>
        <select id="filterRange" onchange="applyFilter()">
          <option value="this_year">This Year</option>
          <option value="last_year">Last Year</option>
          <option value="last_6_months">Last 6 Months</option>
          <option value="last_3_months">Last 3 Months</option>
        </select>
        <button class="report-button" onclick="downloadReport()">Download Report</button>
      </div>
      <div class="analytics-container">
        <?php
        require_once '../db/db_connect.php';
        $filter = $_GET['filter'] ?? 'this_year';
        switch ($filter) {
            case 'last_year':
                $dateCondition = "created_at BETWEEN DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 YEAR), '%Y-01-01') AND DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 YEAR), '%Y-12-31')";
                $paymentCondition = "payment_date BETWEEN DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 YEAR), '%Y-01-01') AND DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 YEAR), '%Y-12-31')";
                break;
            case 'last_6_months':
                $dateCondition = "created_at >= CURDATE() - INTERVAL 6 MONTH";
                $paymentCondition = "payment_date >= CURDATE() - INTERVAL 6 MONTH";
                break;
            case 'last_3_months':
                $dateCondition = "created_at >= CURDATE() - INTERVAL 3 MONTH";
                $paymentCondition = "payment_date >= CURDATE() - INTERVAL 3 MONTH";
                break;
            default:
                $dateCondition = "YEAR(created_at) = YEAR(CURDATE())";
                $paymentCondition = "YEAR(payment_date) = YEAR(CURDATE())";
                break;
        }

        $totalStudents = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'student' AND $dateCondition")->fetch_assoc()['total'] ?? 0;
        $totalInstructors = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'instructor' AND $dateCondition")->fetch_assoc()['total'] ?? 0;
        $totalPayments = $conn->query("SELECT SUM(amount) as total FROM payments WHERE $paymentCondition")->fetch_assoc()['total'] ?? 0;

        $avgProgress = $conn->query("SELECT AVG((SELECT COUNT(*) FROM progress WHERE user_id = users.id AND completion_percentage = 100) / 11 * 100) as avg_progress FROM users WHERE role = 'student' AND $dateCondition")->fetch_assoc()['avg_progress'] ?? 0;

        $progressDistribution = [0, 0, 0, 0, 0];
        $res = $conn->query("SELECT (SELECT COUNT(*) FROM progress WHERE user_id = users.id AND completion_percentage = 100) / 11 * 100 as progress FROM users WHERE role = 'student' AND $dateCondition");
        while ($row = $res->fetch_assoc()) {
            $p = $row['progress'] ?? 0;
            if ($p <= 20) $progressDistribution[0]++;
            elseif ($p <= 40) $progressDistribution[1]++;
            elseif ($p <= 60) $progressDistribution[2]++;
            elseif ($p <= 80) $progressDistribution[3]++;
            else $progressDistribution[4]++;
        }

        $paymentTrendLabels = [];
        $paymentTrendAmounts = [];
        $dates = [];
        for ($i = 4; $i >= 0; $i--) {
            $dates[date('Y-m-d', strtotime("-$i day"))] = 0;
        }
        $res = $conn->query("SELECT DATE(payment_date) as pay_date, SUM(amount) as total_amount FROM payments WHERE status = 'completed' AND payment_date >= CURDATE() - INTERVAL 4 DAY GROUP BY DATE(payment_date)");
        while ($row = $res->fetch_assoc()) {
            $dates[$row['pay_date']] = (float)($row['total_amount'] ?? 0);
        }
        $paymentTrendLabels = array_keys($dates);
        $paymentTrendAmounts = array_values($dates);

        $registrationLabels = [];
        $registrationCounts = [];
        $res = $conn->query("SELECT DATE(created_at) as reg_date, COUNT(*) as count FROM users WHERE role = 'student' AND $dateCondition GROUP BY DATE(created_at) ORDER BY reg_date ASC LIMIT 30");
        while ($row = $res->fetch_assoc()) {
            $registrationLabels[] = $row['reg_date'];
            $registrationCounts[] = $row['count'];
        }

        $conn->close();
        ?>

        <div class="summary-card"><h3>Total Students</h3><p><?= htmlspecialchars($totalStudents) ?></p></div>
        <div class="summary-card"><h3>Total Instructors</h3><p><?= htmlspecialchars($totalInstructors) ?></p></div>
        <div class="summary-card"><h3>Total Payments</h3><p>₱<?= number_format($totalPayments, 2) ?></p></div>
        <div class="summary-card"><h3>Average Progress</h3><p><?= round($avgProgress) ?>%</p></div>

        <div class="chart-container">
          <h3>Student Progress Distribution</h3>
          <canvas id="progressChart"></canvas>
        </div>
        <div class="chart-container">
          <h3>Payments Over Last 5 Days</h3>
          <canvas id="paymentTrendChart"></canvas>
        </div>
        <div class="chart-container">
          <h3>Student Registrations Over Time</h3>
          <canvas id="registrationChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const urlParams = new URLSearchParams(window.location.search);
      const currentFilter = urlParams.get('filter') || 'this_year';
      document.getElementById('filterRange').value = currentFilter;

      new Chart(document.getElementById('progressChart'), {
        type: 'bar',
        data: {
          labels: ['0-20%', '21-40%', '41-60%', '61-80%', '81-100%'],
          datasets: [{
            label: 'Students',
            data: <?= json_encode($progressDistribution) ?>,
            backgroundColor: '#4caf50',
            borderColor: '#388e3c',
            borderWidth: 1
          }]
        }
      });

      new Chart(document.getElementById('paymentTrendChart'), {
        type: 'bar',
        data: {
          labels: <?= json_encode($paymentTrendLabels) ?>,
          datasets: [{
            label: 'Total Payment Amount (₱)',
            data: <?= json_encode($paymentTrendAmounts) ?>,
            backgroundColor: 'rgba(76,175,80,0.2)',
            borderColor: '#4caf50',
            borderWidth: 2
          }]
        }
      });

      new Chart(document.getElementById('registrationChart'), {
        type: 'line',
        data: {
          labels: <?= json_encode($registrationLabels) ?>,
          datasets: [{
            label: 'Registrations',
            data: <?= json_encode($registrationCounts) ?>,
            backgroundColor: 'rgba(33, 150, 243, 0.2)',
            borderColor: '#2196f3',
            borderWidth: 2,
            tension: 0.4
          }]
        }
      });
    });

    function applyFilter() {
      const filter = document.getElementById('filterRange').value;
      const url = new URL(window.location.href);
      url.searchParams.set('filter', filter);
      window.location.href = url.toString();
    }

    function downloadReport() {
      const filter = document.getElementById('filterRange').value;
      window.location.href = 'analytics_admin.php?download_report=1&filter=' + encodeURIComponent(filter);
    }
  function logout() {
    if (confirm("Do you really want to logout?")) {
        fetch("../api/logout.php", { method: "POST", credentials: "include" })
            .then(response => {
                if (response.ok) {
                    localStorage.clear();
                    window.location.replace("../index.html");
                } else {
                    alert("Logout failed. Please try again.");
                }
            })
            .catch(error => console.error("Logout error:", error));
    }
}
  </script>
</body>
</html>