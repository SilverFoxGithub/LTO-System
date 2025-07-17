<?php
session_start();
require_once 'db/db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: index.html");
  exit();
}

$user_id = $_SESSION['user_id'];

// Get certificate file from database
$stmt = $conn->prepare("SELECT pdf_file FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$certificatePath = $row && $row['pdf_file'] ? 'uploads/' . $row['pdf_file'] : '';
$hasCertificate = !empty($certificatePath);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Certificate Status</title>
  <link rel="stylesheet" href="../assets/styles/dashboard.css">
  <style>
    .congrats-container {
      width: 90%;
      max-width: 900px;
      margin: 40px auto;
      background-color: #ffffff;
      border-radius: 12px;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
      padding: 30px;
      text-align: center;
    }

    .congrats-container h1 {
      font-size: 2.5rem;
      color: #00796b;
      margin-bottom: 12px;
    }

    .congrats-container p {
      font-size: 1.2rem;
      margin: 12px 0;
      color: #333;
    }

    .file-info {
      margin-top: 20px;
      font-style: italic;
      color: #555;
    }

    .action-buttons {
      margin-top: 30px;
    }

    .action-buttons .btn {
      background-color: #00897b;
      color: white;
      border: none;
      padding: 12px 24px;
      margin: 8px;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .action-buttons .btn:hover {
      background-color: #00695c;
    }

    .action-buttons .btn:disabled {
      background-color: #cccccc;
      cursor: not-allowed;
    }

    #previewImage {
      max-width: 100%;
      border: 1px solid #ccc;
      margin-top: 20px;
      display: none;
      border-radius: 6px;
    }

    #pdfViewer {
      width: 100%;
      height: 500px;
      border: none;
      margin-top: 20px;
      display: none;
      border-radius: 6px;
    }
  </style>
</head>
<body>

<div class="congrats-container">
  <?php if (!$hasCertificate): ?>
    <h1>Certificate Pending</h1>
    <p>You need to complete and pass the Theoretical Driving Course first.</p>
    <div class="file-info">
      Uploaded file: <strong>Admin is still uploading certificate</strong>
    </div>
    <div class="action-buttons">
      <button class="btn" onclick="goToDashboard()">Back to Dashboard</button>
      <button class="btn" id="downloadBtn" disabled>Download Certificate</button>
    </div>
  <?php else: ?>
    <h1>Certificate Available</h1>
    <p>Your certificate is ready.</p>
    <div class="file-info">
      Uploaded file: <strong id="fileName"><?php echo htmlspecialchars(basename($certificatePath)); ?></strong>
    </div>

    <img id="previewImage" alt="Certificate Preview" />
    <iframe id="pdfViewer"></iframe>

    <div class="action-buttons">
      <button class="btn" onclick="goToDashboard()">Back to Dashboard</button>
      <button class="btn" id="downloadBtn" onclick="downloadCertificate()">Download Certificate</button>
    </div>
  <?php endif; ?>
</div>

<script>
  // Injected by PHP
  const uploadedFile = "<?php echo htmlspecialchars($certificatePath); ?>";
  const hasCertificate = <?php echo $hasCertificate ? 'true' : 'false'; ?>;

  // Preview logic
  if (hasCertificate) {
    if (uploadedFile.match(/\.(jpg|jpeg|png|webp|gif)$/i)) {
      const img = document.getElementById("previewImage");
      img.src = uploadedFile;
      img.style.display = "block";
    } else if (uploadedFile.match(/\.pdf$/i)) {
      const pdf = document.getElementById("pdfViewer");
      pdf.src = uploadedFile;
      pdf.style.display = "block";
    }
  }

  function goToDashboard() {
    window.location.href = "dashboard/student.html";
  }

  function downloadCertificate() {
    if (!hasCertificate) {
      alert("⚠️ Error: No certificate uploaded yet.");
      return;
    }
    window.open(uploadedFile, "_blank");
  }
</script>

</body>
</html>