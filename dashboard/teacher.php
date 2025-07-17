<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Teacher Dashboard</title>
  <link rel="stylesheet" href="../assets/styles/admin-dash.css" />
  <link rel="shortcut icon" href="assets/logo.png" type="image/x-icon" />
  <style>
    button, .btn {
      font-size: 14px;
      font-weight: bold;
      border: none;
      cursor: pointer;
      border-radius: 0.5em;
      padding: 0.5em 1em;
      transition: transform 0.1s ease;
    }

    .btn {
      background: #00897b;
      color: white;
      border: 2px solid #00897b;
      text-align: center;
      margin-bottom: 10px;
    }

    .btn:hover {
      transform: translateY(-0.33em);
    }

    .btn:active {
      transform: translateY(0);
    }

    .pass-btn { background-color: #4CAF50; }
    .fail-btn { background-color: #f44336; }
    .remove-btn { background-color: #e7e7e7; color: black; }

    .btn.active {
      background-color: #004d40 !important;
      color: white;
    }

    .download-link {
      color: #1e88e5;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.2s ease, text-decoration 0.2s ease;
    }

    .download-link:hover {
      color: #1565c0;
      text-decoration: underline;
    }

    .toast {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: #4caf50;
      color: #fff;
      padding: 10px 20px;
      border-radius: 8px;
      opacity: 0;
      transform: translateY(20px);
      transition: all 0.3s ease;
      z-index: 9999;
    }

    .toast.error {
      background: #e53935;
    }

    .toast.show {
      opacity: 1;
      transform: translateY(0);
    }

    .lesson-progress-wrapper {
      width: 100px;
      height: 30px;
      background-color: #e0e0e0;
      border-radius: 10px;
      position: relative;
      overflow: hidden;
      margin: 0 auto;
    }

    .lesson-progress-bar {
      height: 100%;
      background-color: #4caf50;
      border-radius: 10px 0 0 10px;
      transition: width 0.3s ease;
    }

    .lesson-progress-label {
      position: absolute;
      width: 100%;
      height: 100%;
      left: 0;
      top: 0;
      font-size: 10px;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      pointer-events: none;
    }

    /* General Modal Styles from Admin */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.6);
    }

    .modal-content {
      background-color: #fff;
      margin: 8% auto;
      padding: 30px 25px;
      border: 1px solid #ccc;
      border-radius: 10px;
      width: 90%;
      max-width: 400px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
      from { transform: translateY(-30px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    .modal h2 {
      margin-top: 0;
      font-size: 22px;
      margin-bottom: 20px;
      color: #333;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="sidebar">
      <div class="sidebar-top">
        <img src="../assets/logo.png" alt="MSSB Driving Institute Logo" class="logo" />
        <h2>Teacher Dashboard</h2>
      </div>
      <button id="logoutBtn" onclick="logout()">Logout</button>
    </div>

    <div class="main-content">
      <div class="header">
        <h1>Enrolled Students</h1>
      </div>

      <div class="student-list">
        <table>
          <thead>
            <tr>
              <th>Student</th>
              <th>Lesson Progress</th>
              <th>Progress</th>
              <th>Download</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="studentTableBody">
<?php
require_once '../db/db_connect.php';

$sql = "SELECT id, full_name, pdf_file, test_1_score, test_2_score, final_test_score FROM users WHERE role = 'student'";
$result = $conn->query($sql);

$totalLessons = 11; // Adjust if your course has more or fewer lessons

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $name = htmlspecialchars($row['full_name']);
    $hasPDF = !empty($row['pdf_file']);
    $link = $hasPDF ? "../uploads/{$row['pdf_file']}" : "";
    $test1Score = $row['test_1_score'] !== null ? $row['test_1_score'] : 'No score';
    $test2Score = $row['test_2_score'] !== null ? $row['test_2_score'] : 'No score';
    $finalScore = $row['final_test_score'] !== null ? $row['final_test_score'] : 'No score';

    // Query for completed lessons
    $stmt = $conn->prepare("SELECT COUNT(*) as completed FROM progress WHERE user_id = ? AND completion_percentage = 100");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $progressResult = $stmt->get_result();
    $progressRow = $progressResult->fetch_assoc();
    $completed = $progressRow['completed'] ?? 0;
    $percentage = round(($completed / $totalLessons) * 100);

    // Progress HTML bar
    $lessonProgress = "
      <div class='lesson-progress-wrapper'>
        <div class='lesson-progress-bar' style='width: {$percentage}%;'></div>
        <span class='lesson-progress-label'>{$percentage}%</span>
      </div>
    ";

    echo "<tr data-student-id='{$id}'>";
    echo "<td>{$name}</td>";
    echo "<td>{$lessonProgress}</td>";
    echo "<td><button class='btn btn-result view-result' data-student-id='$id' data-test1='$test1Score' data-test2='$test2Score' data-final='$finalScore'>Result</button></td>";
    echo "<td>
            <button class='btn pass-btn'>Pass</button>
            <button class='btn fail-btn'>Fail</button>
          </td>";
    echo "<td>" . ($hasPDF ? "<a class='download-link' href='{$link}' download>Download</a>" : "No PDF") . "</td>";
    echo "<td><button class='btn remove-btn'>Remove</button></td>";
    echo "</tr>";
  }
} else {
  echo "<tr><td colspan='5'>No students found</td></tr>";
}
$conn->close();
?>
          </tbody>
        </table>
      </div>

      <div class="pagination-wrapper" style="text-align:center; margin-top: 20px;">
        <div id="pagination"></div>
      </div>
    </div>
  </div>

  <div id="toast-container"></div>

  <!-- View Test Result Modal -->
  <div id="scoreModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('scoreModal')">×</span>
      <h2>Test Results</h2>
      <div id="scoreContent">
        <p>Loading...</p>
      </div>
    </div>
  </div>

  <script>
    function showToast(message, type = 'success') {
      const toast = document.createElement('div');
      toast.className = `toast ${type}`;
      toast.textContent = message;
      document.getElementById('toast-container').appendChild(toast);

      setTimeout(() => toast.classList.add('show'), 10);
      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }

    function sendPost(action, studentId, extra = {}) {
      const formData = new FormData();
      formData.append('student_id', studentId);
      if (extra.status) formData.append('status', extra.status);

      fetch(`file-handler.php?action=${action}`, {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showToast(`${action.charAt(0).toUpperCase() + action.slice(1)} successful`);
          setTimeout(() => location.reload(), 1500);
        } else {
          showToast('Error: ' + data.error, 'error');
        }
      })
      .catch(() => showToast('Network error. Try again.', 'error'));
    }

    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.pass-btn').forEach(btn => {
        btn.addEventListener('click', function () {
          if (!confirm("Mark this student as Passed?")) return;
          const row = this.closest('tr');
          const studentId = row.getAttribute('data-student-id');
          row.children[1].innerHTML = '<div class="lesson-progress-wrapper"><div class="lesson-progress-bar" style="width: 100%;"></div><span class="lesson-progress-label">100%</span></div>';
          sendPost('update_status', studentId, { status: 'pass' });
        });
      });

      document.querySelectorAll('.fail-btn').forEach(btn => {
        btn.addEventListener('click', function () {
          if (!confirm("Mark this student as Failed?")) return;
          const studentId = this.closest('tr').getAttribute('data-student-id');
          sendPost('update_status', studentId, { status: 'fail' });
        });
      });

      document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', function () {
          if (!confirm("Remove this student? This cannot be undone.")) return;
          const studentId = this.closest('tr').getAttribute('data-student-id');
          sendPost('delete', studentId);
        });
      });

      // Pagination logic
      const rowsPerPage = 10;
      const tableBody = document.getElementById('studentTableBody');
      const pagination = document.getElementById('pagination');
      const rows = Array.from(tableBody.querySelectorAll('tr'));
      const totalPages = Math.ceil(rows.length / rowsPerPage);

      function showPage(page) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        rows.forEach((row, i) => row.style.display = i >= start && i < end ? '' : 'none');
        pagination.innerHTML = '';

        const prev = document.createElement('button');
        prev.textContent = 'Previous';
        prev.className = 'btn';
        prev.disabled = page === 1;
        prev.onclick = () => showPage(page - 1);
        pagination.appendChild(prev);

        for (let i = 1; i <= totalPages; i++) {
          const btn = document.createElement('button');
          btn.textContent = i;
          btn.className = 'btn';
          if (i === page) btn.classList.add('active');
          btn.onclick = () => showPage(i);
          pagination.appendChild(btn);
        }

        const next = document.createElement('button');
        next.textContent = 'Next';
        next.className = 'btn';
        next.disabled = page === totalPages;
        next.onclick = () => showPage(page + 1);
        pagination.appendChild(next);
      }

      if (rows.length) showPage(1);
    });

    // function logout() {
    //   if (confirm("Do you want to Logout?")) window.location.href = "logout.php";
    // }

    // View Results JavaScript
    function openModal(id) {
      const modal = document.getElementById(id);
      if (modal) modal.style.display = 'flex';
    }

    function closeModal(id) {
      const modal = document.getElementById(id);
      if (modal) modal.style.display = 'none';
    }

    document.addEventListener("DOMContentLoaded", () => {
      document.querySelectorAll(".view-result").forEach(button => {
        button.addEventListener("click", () => {
          const studentId = button.getAttribute("data-student-id");
          const test1Score = button.getAttribute("data-test1");
          const test2Score = button.getAttribute("data-test2");
          const finalScore = button.getAttribute("data-final");
          const container = document.getElementById("scoreContent");

          container.innerHTML = `
            <ul>
              <li><strong>Day 1 - Part 1:</strong> ${test1Score}</li>
              <li><strong>Day 1 - Part 2:</strong> ${test2Score}</li>
              <li><strong>Final Test:</strong> ${finalScore}</li>
            </ul>
          `;

          openModal("scoreModal");
        });
      });
    });
  </script>

  <script src="../assets/instructor.js"></script>
</body>
</html>