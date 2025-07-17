<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Driving Chatbot AI (Gemini Powered)</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<button id="openModalBtn">Chat about Driving 🚗</button><br><br><br>
<a class= goback href="../dashboard/student.html"> Go Back </a>

<!-- Modal -->
<div id="chatModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <div id="chatbox">
      <div class="bot-message">👋 Hello! I'm your Driving Assistant. How can I help you today?</div>
    </div>
    <form id="chatForm">
      <input type="text" id="userInput" placeholder="Ask me anything about driving..." autocomplete="off" required>
      <button type="submit">Send</button>
    </form>

    <div id="quickButtons">
      <p><strong>Quick Help:</strong></p>
      <button class="quick-btn" data-msg="How do I use this chatbot?">How to Use</button>
      <button class="quick-btn" data-msg="What is this chatbot?">What is This?</button>
      <button class="quick-btn" data-msg="The system is not working.">System Not Working</button>
      <button class="quick-btn" data-msg="How do I go back to the dashboard?">Go Back Help</button>
    </div>

  </div>
</div>

<script src="script.js"></script>
</body>
</html>
