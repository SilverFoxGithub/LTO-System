<?php
session_start();
include '../db/db_connect.php';

// Ensure only admin can access
// if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
//   header("Location: ../index.html");
//   exit();
// }

// Fetch payments along with user info and role
$sql = "SELECT p.*, u.full_name, u.role 
        FROM payments p 
        JOIN users u ON p.user_id = u.id 
        ORDER BY p.payment_date DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Payments Page</title>
  <link rel="stylesheet" href="../assets/styles/admin-dash.css"/>
<style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f0f7f0;
      display: flex;
      height: 100vh;
    }

    .sidebar {
      width: 220px;
      background-color: #00897b;
      color: white;
      padding: 20px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .sidebar-top {
      display: flex;
      flex-direction: column;
    }

    .sidebar h2 {
      font-size: 20px;
      margin-bottom: 30px;
    }

    .sidebar button {
      background-color: #004d40;
      border: none;
      color: white;
      padding: 10px;
      margin-bottom: 10px;
      cursor: pointer;
      border-radius: 4px;
      font-size: 14px;
    }

    .sidebar button:hover {
      background-color: #00332d;
    }

    .main-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      padding: 30px;
      background-color: #ffffff;
      margin: 20px 20px 20px 20px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    h1 {
      font-size: 24px;
      margin: 0 0 20px 0;
      color: #00897b;
    }

    .search-bar {
      margin-bottom: 20px;
    }

    .search-bar input {
      width: 100%;
      max-width: 300px;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background-color: #f9f9f9;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    th, td {
      border: 1px solid #ddd;
      padding: 12px;
      text-align: left;
      font-size: 14px;
    }

    th {
      background-color: #e0f2f1;
      color: #333;
      font-weight: bold;
    }

    .pagination-wrapper {
      margin-top: auto;
      padding-top: 20px;
      text-align: center;
      background-color: #ffffff;
    }

    .btn {
      font-size: 14px;
      font-weight: bold;
      border: none;
      cursor: pointer;
      border-radius: 6px;
      padding: 8px 12px;
      background: #00897b;
      color: white;
      margin: 0 4px;
    }

    .btn:hover {
      background-color: #007265;
    }

    .btn.active {
      background-color: #004d40 !important;
    }

    .receipt-btn {
      background: none;
      border: none;
      color: #00796b;
      text-decoration: underline;
      cursor: pointer;
      font-size: 14px;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.75);
      justify-content: center;
      align-items: center;
    }

    .modal img {
      max-width: 80%;
      max-height: 80%;
      border-radius: 8px;
      box-shadow: 0 0 10px black;
    }

    .modal .close {
      position: absolute;
      top: 30px;
      right: 40px;
      color: white;
      font-size: 30px;
      font-weight: bold;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <div class="sidebar-top">
      <img src="../assets/logo.png" alt="MSSB Driving Institute Logo" class="logo" />
      <!-- <h2>Admin Dashboard</h2> -->
      <button onclick="window.location.href='admin.php'">Dashboard</button>
      <button onclick="window.location.href='admin_paymment.php'">Payments</button>
      <button onclick="window.location.href='analytics_admin.php'">Analytics</button>
    </div>
    <button id="logoutBtn" onclick="logout()">Logout</button>
  </div>

  <div class="main-content">
    <h1>Payments</h1>
    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search payments..." onkeyup="searchTable()">
    </div>
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Role</th>
          <th>Receipt</th>
          <th>Mode of Payment</th>
          <th>Amount</th>
          <th>Date and Time</th>
        </tr>
      </thead>
      <tbody id="studentTableBody">
        <?php if (mysqli_num_rows($result) > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td><?= htmlspecialchars($row['full_name']) ?></td>
              <td><?= htmlspecialchars($row['role']) ?></td>
              <td>
                <?php if (!empty($row['receipt'])): ?>
                  <button class="receipt-btn" data-img="../receipts/<?= htmlspecialchars($row['receipt']) ?>">View</button>
                <?php else: ?>
                  No Receipt
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($row['payment_method']) ?></td>
              <td>₱<?= number_format($row['amount'], 2) ?></td>
              <td><?= htmlspecialchars($row['payment_date']) ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6">No payment records found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <div class="pagination-wrapper">
      <div id="pagination"></div>
    </div>
  </div>

  <div id="receiptModal" class="modal">
    <span class="close" id="closeModal">×</span>
    <img id="modalImg" src="" alt="Receipt Image"/>
  </div>

  <script>
    function logout() {
      localStorage.removeItem('userToken');
      alert('You have been logged out.');
      window.location.href = '../index.html';
    }

    document.addEventListener('DOMContentLoaded', () => {
      const modal = document.getElementById('receiptModal');
      const modalImg = document.getElementById('modalImg');
      const closeBtn = document.getElementById('closeModal');
      const tableBody = document.getElementById('studentTableBody');
      const rows = Array.from(tableBody.getElementsByTagName('tr'));
      const searchInput = document.getElementById('searchInput');

      document.querySelectorAll('.receipt-btn').forEach(button => {
        button.addEventListener('click', () => {
          const imgSrc = `../uploads/${button.dataset.img}`;
          modalImg.src = imgSrc;
          modal.style.display = 'flex';
        });
      });

      closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
        modalImg.src = '';
      });

      window.addEventListener('click', e => {
        if (e.target === modal) {
          modal.style.display = 'none';
          modalImg.src = '';
        }
      });

      // Pagination logic
      const rowsPerPage = 10;
      const pagination = document.getElementById('pagination');

      function showPage(page) {
        const filteredRows = rows.filter(row => row.style.display !== 'none');
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        rows.forEach((row, index) => {
          row.style.display = (filteredRows.indexOf(row) >= start && filteredRows.indexOf(row) < end) ? '' : 'none';
        });

        pagination.innerHTML = '';

        const prevBtn = document.createElement('button');
        prevBtn.textContent = 'Previous';
        prevBtn.className = 'btn';
        prevBtn.disabled = page === 1;
        prevBtn.addEventListener('click', () => showPage(page - 1));
        pagination.appendChild(prevBtn);

        for (let i = 1; i <= totalPages; i++) {
          const btn = document.createElement('button');
          btn.textContent = i;
          btn.className = 'btn';
          if (i === page) btn.classList.add('active');
          btn.addEventListener('click', () => showPage(i));
          pagination.appendChild(btn);
        }

        const nextBtn = document.createElement('button');
        nextBtn.textContent = 'Next';
        nextBtn.className = 'btn';
        nextBtn.disabled = page === totalPages;
        nextBtn.addEventListener('click', () => showPage(page + 1));
        pagination.appendChild(nextBtn);
      }

      if (rows.length > 0) {
        showPage(1);
      }

      // Search functionality
      function searchTable() {
        const input = searchInput.value.toLowerCase().replace(/[^\w\s]/g, '');

        rows.forEach(row => {
          let match = false;
          const cells = row.getElementsByTagName('td');
          for (let j = 0; j < cells.length - 1; j++) { // Exclude receipt button column
            if (cells[j]) {
              const text = cells[j].textContent.toLowerCase().replace(/[^\w\s]/g, '');
              if (text.includes(input)) {
                match = true;
                break;
              }
            }
          }
          row.style.display = match ? '' : 'none';
        });

        showPage(1); // Reset to first page after search
      }

      // Ensure search triggers on input
      searchInput.addEventListener('keyup', searchTable);
    });
  </script>
  <script src="../assets/admin.js"></script>
</body>
</html>