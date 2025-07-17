<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../assets/styles/admin-dash.css" />
  <style>
    .lesson-progress-bar-wrapper {
      width: 80px;
      height: 20px;
      background-color: #e0e0e0;
      border-radius: 10px;
      overflow: hidden;
      display: block;
      margin: 2px auto 0;
    }

    .lesson-progress-bar {
      height: 100%;
      background-color: #4caf50;
      width: 0%;
      transition: width 0.3s ease;
      border-radius: 10px 0 0 10px;
    }

    .lesson-progress-label {
      font-size: 10px;
      color: #333;
      text-align: center;
      display: block;
      margin-top: 2px;
    }

    /* General Modal Styles */
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
      from {
        transform: translateY(-30px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .modal h2 {
      margin-top: 0;
      font-size: 22px;
      margin-bottom: 20px;
      color: #333;
    }

    .modal input,
    .modal select {
      width: 100%;
      padding: 10px 12px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      box-sizing: border-box;
    }

    .modal button[type="submit"] {
      background-color: #4caf50;
      color: white;
      border: none;
      padding: 10px 14px;
      width: 100%;
      font-size: 16px;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.2s ease;
    }

    .modal button[type="submit"]:hover {
      background-color: #45a045;
    }

    /* Close Button */
    .modal .close {
      position: absolute;
      top: 12px;
      right: 18px;
      color: #555;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }

    .modal .close:hover {
      color: #000;
    }
    .btn-create,
    .btn-edit {
      padding: 8px 14px;
      font-size: 14px;
      background-color: #2196f3;
      color: #fff;
      border: none;
      border-radius: 8px;
      margin-bottom: 15px;
      cursor: pointer;
      transition: background-color 0.2s ease;
    }

    .btn-create:hover,
    .btn-edit:hover {
      background-color: #1976d2;
    }
    #addButton {
      max-width: 20%;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background-color: rgba(0, 0, 0, 0.6);
    }

    .modal-content {
      background: white;
      padding: 24px;
      margin: 8% auto;
      border-radius: 10px;
      max-width: 400px;
      position: relative;
    }

    .modal-content h2 {
      margin-top: 0;
    }

    .modal .close {
      position: absolute;
      right: 15px;
      top: 10px;
      font-size: 24px;
      cursor: pointer;
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
  </style>
</head>

<body>
  <div class="container">
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
      <div class="header">
        <h1>Enrolled Students</h1>
      </div>
      <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search students..." onkeyup="searchTable()">
      </div>
      <button class="btn btn-create" id="addButton" onclick="openModal('createStudentModal')">Add New Student</button>
      <div class="student-list">
        <table>
          <thead>
            <tr>
              <th>Student</th>
              <th>Lesson Progress</th>
              <th>Progress</th>
              <th>View Results</th>
              <th>Certificate</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="studentTableBody">
            <?php
require_once '../db/db_connect.php';

$sql = "SELECT id, full_name, pdf_file, status, test_1_score, test_2_score, final_test_score FROM users WHERE role = 'student'";
$result = $conn->query($sql);

$totalLessons = 11; // Change if needed

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $id = (int)$row['id'];
    $name = htmlspecialchars($row['full_name']);
    $pdf = $row['pdf_file'];
    $status = $row['status'] ?? '';
    $test1Score = $row['test_1_score'] !== null ? $row['test_1_score'] : 'No score';
    $test2Score = $row['test_2_score'] !== null ? $row['test_2_score'] : 'No score';
    $finalScore = $row['final_test_score'] !== null ? $row['final_test_score'] : 'No score';

    $stmt = $conn->prepare("SELECT COUNT(*) as completed FROM progress WHERE user_id = ? AND completion_percentage = 100");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $progressResult = $stmt->get_result();
    $progressRow = $progressResult->fetch_assoc();
    $completed = $progressRow['completed'] ?? 0;

    $progressText = "Lesson $completed of $totalLessons";
    $progressData = "$completed/$totalLessons";
    $percentage = ($completed / $totalLessons) * 100;
    $progressBar = "
      <div class='lesson-progress-bar-wrapper'>
        <div class='lesson-progress-bar' style='width: {$percentage}%;'></div>
      </div>
      <span class='lesson-progress-label'>" . round($percentage) . "%</span>
    ";

    echo "<tr>";
    echo "<td>$name</td>";
    echo "<td data-progress=\"$progressData\">$progressBar</td>";
    echo "<td class='progress-status'>" . ucfirst($status) . "</td>";
    echo "<td>
            <button class='btn btn-result view-result' data-student-id='$id' data-test1='$test1Score' data-test2='$test2Score' data-final='$finalScore'>Result</button>
          </td>";
    echo "<td>";
    if ($pdf) {
      $filePath = "../uploads/$pdf";
      echo "<button class='btn view-file-btn' data-file='$filePath'>View File</button>";
    } else {
      echo "No file";
    }
    echo "</td>";
    echo "<td>
            <button class='btn btn-upload upload-cert' data-student-id='$id'>Upload</button>
            <button class='btn btn-remove delete-user-btn' data-student-id='$id'>Remove</button>
            <button class='btn btn-edit edit-user-btn' data-student-id='$id' data-name=\"$name\">Edit</button>
          </td>";
    echo "</tr>";
  }
} else {
  echo "<tr><td colspan='6'>No students found</td></tr>";
}
$conn->close();
?>

          </tbody>
        </table>
      </div>
      <div class="pagination-wrapper">
        <div id="pagination">
          <button class="btn">Previous</button>
          <button class="btn">1</button>
          <button class="btn">Next</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Upload Modal -->
  <div id="uploadModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('uploadModal')">×</span>
      <h2>Upload Certificate</h2>
      <form id="uploadForm" enctype="multipart/form-data">
        <input type="file" name="certificate" accept="application/pdf" required />
        <input type="hidden" id="uploadStudentId" name="student_id" />
        <button type="submit">Upload</button>
      </form>
    </div>
  </div>

  <!-- Add Modal -->
  <div id="createStudentModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('createStudentModal')">×</span>
      <h2>Add New Student</h2>
      <form id="createStudentForm">
        <input type="text" name="full_name" placeholder="Full Name" required />
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Create</button>
      </form>
    </div>
  </div>

  <!-- Edit Modal -->
  <div id="editStudentModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('editStudentModal')">×</span>
      <h2>Edit Student</h2>
      <form id="editStudentForm">
        <input type="text" name="full_name" id="editFullName" required />
        <input type="hidden" name="student_id" id="editStudentId" />
        <select name="role" id="editRole" required>
          <option value="">Select Role</option>
          <option value="student">Student</option>
          <option value="instructor">Instructor</option>
          <option value="admin">Admin</option>
        </select>
        <button type="submit">Save Changes</button>
      </form>
    </div>
  </div>

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

  <div id="filePreviewModal" class="custom-file-modal" role="dialog" aria-modal="true" aria-label="PDF Viewer Modal">
    <span class="custom-file-modal-close" id="filePreviewClose">×</span>
    <div class="custom-file-modal-content" id="filePreviewContent">
      <iframe id="pdfViewer" allow="fullscreen"></iframe>
    </div>
  </div>

  <!-- JavaScript -->
  <script src="../assets/admin.js"></script>
  <script>
    function openModal(id) {
      document.getElementById(id).style.display = 'block';
    }

    function closeModal(id) {
      document.getElementById(id).style.display = 'none';
    }

    // Handle Create Student
    document.getElementById('createStudentForm').addEventListener('submit', function (e) {
      e.preventDefault();
      const formData = new FormData(this);

      fetch('create_student.php', {
        method: 'POST',
        body: formData
      })
        .then(res => res.text())
        .then(data => {
          alert(data);
          location.reload();
        });
    });

    // Handle Edit Button Click
    document.querySelectorAll('.edit-user-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('editStudentId').value = btn.dataset.studentId;
        document.getElementById('editFullName').value = btn.dataset.name;
        document.getElementById('editRole').value = 'student'; // Default to 'student'
        openModal('editStudentModal');
      });
    });

    // Handle Edit Submit
    document.getElementById('editStudentForm').addEventListener('submit', function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch('update_student.php', {
        method: 'POST',
        body: formData
      })
        .then(res => res.text())
        .then(data => {
          alert(data);
          location.reload();
        });
    });
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const rowsPerPage = 10;
      const tableBody = document.getElementById('studentTableBody');
      const pagination = document.getElementById('pagination');
      const rows = Array.from(tableBody.querySelectorAll('tr'));
      const totalPages = Math.ceil(rows.length / rowsPerPage);
      let currentPage = 1;

      function showPage(page) {
        currentPage = page;
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const filteredRows = rows.filter(row => row.style.display !== 'none');

        rows.forEach((row, index) => {
          row.style.display = (filteredRows.indexOf(row) >= start && filteredRows.indexOf(row) < end) ? '' : 'none';
        });

        renderPagination();
      }

      function renderPagination() {
        pagination.innerHTML = '';

        const prevBtn = document.createElement('button');
        prevBtn.textContent = 'Previous';
        prevBtn.className = 'btn';
        prevBtn.disabled = currentPage === 1;
        prevBtn.addEventListener('click', () => {
          if (currentPage > 1) showPage(currentPage - 1);
        });
        pagination.appendChild(prevBtn);

        const filteredRows = rows.filter(row => row.style.display !== 'none');
        const totalFilteredPages = Math.ceil(filteredRows.length / rowsPerPage);

        for (let i = 1; i <= totalFilteredPages; i++) {
          const btn = document.createElement('button');
          btn.textContent = i;
          btn.className = 'btn';
          if (i === currentPage) {
            btn.style.backgroundColor = '#004d40';
            btn.style.color = '#fff';
          }
          btn.addEventListener('click', () => showPage(i));
          pagination.appendChild(btn);
        }

        const nextBtn = document.createElement('button');
        nextBtn.textContent = 'Next';
        nextBtn.className = 'btn';
        nextBtn.disabled = currentPage === totalFilteredPages;
        nextBtn.addEventListener('click', () => {
          if (currentPage < totalFilteredPages) showPage(currentPage + 1);
        });
        pagination.appendChild(nextBtn);
      }

      if (rows.length > 0) {
        showPage(1);
      }

      // Search functionality
      const searchInput = document.getElementById('searchInput');
      function searchTable() {
        const input = searchInput.value.toLowerCase().replace(/[^\w\s]/g, '');

        rows.forEach(row => {
          let match = false;
          const cells = row.getElementsByTagName('td');
          for (let j = 0; j < cells.length - 2; j++) { // Exclude View Results, Certificate, and Actions columns
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

      searchInput.addEventListener('keyup', searchTable);
    });

    // File modal
    document.addEventListener('DOMContentLoaded', () => {
      const filePreviewModal = document.getElementById('filePreviewModal');
      const filePreviewClose = document.getElementById('filePreviewClose');
      const pdfViewer = document.getElementById('pdfViewer');

      document.querySelectorAll('.view-file-btn').forEach(button => {
        button.addEventListener('click', () => {
          const filePath = button.getAttribute('data-file');
          const fileExtension = filePath.split('.').pop().toLowerCase();

          if (fileExtension === 'pdf') {
            pdfViewer.src = filePath + '#toolbar=0&navpanes=0&scrollbar=0';
            filePreviewModal.classList.add('show');
          } else {
            alert('Unsupported file type');
          }
        });
      });

      filePreviewClose.addEventListener('click', () => {
        filePreviewModal.classList.remove('show');
        pdfViewer.src = '';
      });

      filePreviewModal.addEventListener('click', (e) => {
        if (e.target === filePreviewModal) {
          filePreviewModal.classList.remove('show');
          pdfViewer.src = '';
        }
      });

      const modalContent = document.getElementById('filePreviewContent');
      modalContent.addEventListener('click', (e) => {
        e.stopPropagation();
      });
    });
  </script>

  <!-- Upload and Delete -->
  <script>
    function closeModal(id) {
      document.getElementById(id).style.display = 'none';
    }

    function openModal(id) {
      document.getElementById(id).style.display = 'flex';
    }

    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.upload-cert').forEach(btn => {
        btn.addEventListener('click', () => {
          const studentId = btn.getAttribute('data-student-id');
          document.getElementById('uploadStudentId').value = studentId;
          openModal('uploadModal');
        });
      });

      document.getElementById('uploadForm').addEventListener('submit', e => {
        e.preventDefault();
        const formData = new FormData(e.target);

        fetch('file-handler.php?action=upload', {
          method: 'POST',
          body: formData
        })
          .then(res => res.json())
          .then(data => {
            alert(data.success ? 'Upload successful!' : `Upload failed: ${data.error}`);
            if (data.success) location.reload();
          });
      });

      document.querySelectorAll('.delete-user-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const studentId = btn.getAttribute('data-student-id');
          if (confirm('Are you sure you want to remove this student?')) {
            fetch(`file-handler.php?action=delete`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: `student_id=${studentId}`
            })
              .then(res => res.json())
              .then(data => {
                alert(data.success ? 'Student removed successfully.' : `Delete failed: ${data.error}`);
                if (data.success) {
                  btn.closest('tr').remove();
                }
              });
          }
        });
      });

      document.getElementById('submitAddressBtn').addEventListener('click', () => {
        const address = document.getElementById('address').value.trim();
        if (!address) {
          alert('Please enter an address.');
          return;
        }
        alert(`Address submitted: ${address}`);
      });
    });
  </script>

  <script>
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
</body>

</html>