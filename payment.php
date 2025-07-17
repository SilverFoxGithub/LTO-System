<?php
// Show errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();
include 'db/db_connect.php';

// Initialize
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $payment_method = $_POST['payment_method'] ?? '';
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

if ($amount <= 0) {
  $error = "Amount must be greater than 0.";
}
  $receiptName = "";
  $user_id = $_SESSION['user_id'] ?? null;

  if (!$user_id) {
    $error = "You must be logged in to make a payment.";
  }

  if (!$error && in_array($payment_method, ['bdo', 'gcash'])) {
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === 0) {
      $targetDir = "receipts/";
      if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

      $receiptName = time() . '_' . basename($_FILES["receipt"]["name"]);
      $targetFile = $targetDir . $receiptName;

      if (!move_uploaded_file($_FILES["receipt"]["tmp_name"], $targetFile)) {
        $error = "Failed to upload receipt.";
      }
    } else {
      $error = "Receipt is required.";
    }
  }

  if (!$error) {
    $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, payment_method, receipt, status) VALUES (?, ?, ?, ?, 'completed')");
    $stmt->bind_param("idss", $user_id, $amount, $payment_method, $receiptName);

    if ($stmt->execute()) {
      $success = "Payment submitted successfully!";
      echo "<script>
    setTimeout(function() {
      window.location.href = 'dashboard/student.html';
    }, 3000); // Redirect after 3 seconds
  </script>";
    } else {
      $error = "Database error: " . $stmt->error;
    }


    $stmt->close();
  }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Payment Page</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #e0f7fa, #e0f2f1);
      margin: 0;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    .container {
      width: 100%;
      max-width: 420px;
      background-color: #fff;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      padding: 24px;
      box-sizing: border-box;
    }

    h1 {
      text-align: center;
      margin-bottom: 24px;
      font-size: 24px;
      color: #00695c;
    }

    .tabs {
      display: flex;
      margin-bottom: 20px;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .tab {
      flex: 1;
      padding: 10px 0;
      text-align: center;
      background-color: #e0e0e0;
      cursor: pointer;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }

    .tab.active {
      background-color: #00897b;
      color: #fff;
    }

    .tab-content {
      display: none;
      padding: 20px 10px;
    }

    .tab-content.active {
      display: block;
    }

    .form-group {
      margin-bottom: 16px;
      text-align: left;
    }

    label {
      display: block;
      margin-bottom: 6px;
      font-weight: bold;
    }

    input[type="number"],
    input[type="file"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #bbb;
      border-radius: 6px;
      font-size: 1rem;
    }

    button {
      width: 100%;
      padding: 12px;
      font-size: 16px;
      background-color: #00897b;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      margin-top: 12px;
    }

    button:hover {
      background-color: #00695c;
    }

    .qr-image {
      display: block;
      margin: 0 auto 16px;
      width: 200px;
      height: 200px;
      object-fit: contain;
      cursor: pointer;
    }

    .address-box {
      text-align: center;
      font-size: 1.2rem;
      font-weight: bold;
      color: #333;
      border: 2px dashed #ccc;
      padding: 40px 10px;
      border-radius: 12px;
      background-color: #f9f9f9;
    }

    .success,
    .error {
      text-align: center;
      font-weight: bold;
      margin-bottom: 16px;
      padding: 10px;
      border-radius: 6px;
    }

    .success {
      background-color: #d4edda;
      color: #155724;
    }

    .error {
      background-color: #f8d7da;
      color: #721c24;
    }

    @media (max-width: 480px) {
      .container {
        padding: 16px;
      }

      h1 {
        font-size: 20px;
      }

      .qr-image {
        width: 80px;
        height: 80px;
      }

      .address-box {
        font-size: 1rem;
        padding: 30px 8px;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>PAYMENT</h1>

    <?php if ($success): ?>
      <div class="success"><?= $success ?></div>
    <?php elseif ($error): ?>
      <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <div class="tabs">
      <div class="tab active" onclick="showTab('bdo')">BDO</div>
      <div class="tab" onclick="showTab('gcash')">GCash</div>
      <div class="tab" onclick="showTab('cash')">Cash</div>
    </div>

    <!-- BDO Form -->
    <form method="POST" enctype="multipart/form-data" class="tab-content active" id="bdo-tab">
      <img src="assets/QR.png" alt="QR Code" class="qr-image" />
      <div class="form-group">
        <label for="bdo-amount">Amount</label>
        <div class="form-group" style="background-color: #f1f8f7; border-left: 4px solid #00897b; padding: 12px 16px; border-radius: 8px;">
          <strong>Bank Name:</strong> BDO Savings Account<br />
          <strong>Account Number:</strong> 0059-3004-8070<br />
          <strong>Account Name:</strong> Clarito De Luna
        </div>
        <input type="number" name="amount" id="bdo-amount" placeholder="Enter amount" required />
      </div>
      <div class="form-group">
        <label for="bdo-receipt">Receipt</label>
        <input type="file" name="receipt" id="bdo-receipt" accept="image/*" required />
      </div>
      <input type="hidden" name="payment_method" value="bdo" />
      <button type="submit">Submit BDO Payment</button>
    </form>

    <!-- GCash Form -->
    <form method="POST" enctype="multipart/form-data" class="tab-content" id="gcash-tab">
      <img src="assets/GCash-QR.png" alt="QR Code" class="qr-image" />
      <div class="form-group">
        <label for="gcash-amount">Amount</label>
        <div class="form-group" style="background-color: #f1f8f7; border-left: 4px solid #00897b; padding: 12px 16px; border-radius: 8px;">
          <strong>GCash Name:</strong> Clarito De Luna<br />
          <strong>Account Number:</strong> 0917-581-5741<br />
          <strong>Account Name:</strong> Clarito De Luna
        </div>
        <input type="number" name="amount" id="gcash-amount" placeholder="Enter amount" required />
      </div>
      <div class="form-group">
        <label for="gcash-receipt">Receipt</label>
        <input type="file" name="receipt" id="gcash-receipt" accept="image/*" required />
      </div>
      <input type="hidden" name="payment_method" value="gcash" />
      <button type="submit">Submit GCash Payment</button>
    </form>

    <!-- Cash Tab (No form) -->
    <div class="tab-content" id="cash-tab">
      <div class="address-box">
        Location:<br> Guiho street Cassandra II Subd. <br>Barangay. Quipot, Tiaong, Quezon
      </div>
    </div>

    <button onclick="window.location.href='dashboard/student.html'" style="margin-top: 20px; background-color: #ccc; color: #333">
      Back to Dashboard
    </button>
  </div>

  <script>
    function showTab(tabName) {
      const tabs = document.querySelectorAll(".tab");
      const contents = document.querySelectorAll(".tab-content");

      tabs.forEach((tab) => {
        tab.classList.remove("active");
        if (tab.textContent.toLowerCase() === tabName) {
          tab.classList.add("active");
        }
      });

      contents.forEach((content) => {
        content.classList.remove("active");
      });

      const activeTab = document.getElementById(`${tabName}-tab`);
      if (activeTab) activeTab.classList.add("active");
    }

    // ✅ PREVENT UNAUTHORIZED ACCESS
    function checkAuthStatus() {
      if (!localStorage.getItem("isLoggedIn")) {
        window.location.replace("index.html");
      }
    }
  </script>
</body>

</html>