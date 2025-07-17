// Modal functionality
const modal = document.getElementById('chatModal');
const openModalBtn = document.getElementById('openModalBtn');
const closeModal = document.getElementsByClassName('close')[0];

openModalBtn.onclick = function () {
  modal.style.display = "block";
};

closeModal.onclick = function () {
  modal.style.display = "none";
};

window.onclick = function (event) {
  if (event.target === modal) {
    modal.style.display = "none";
  }
};

// Chat functionality
const chatForm = document.getElementById('chatForm');
const userInput = document.getElementById('userInput');
const chatbox = document.getElementById('chatbox');

chatForm.addEventListener('submit', function (event) {
  event.preventDefault();
  const message = userInput.value.trim();
  if (message !== "") {
    addMessage(message, 'user');
    userInput.value = "";
    showLoading();
    getBotResponse(message);
  }
});

function addMessage(message, sender) {
  const messageDiv = document.createElement('div');
  messageDiv.className = sender + '-message';
  messageDiv.innerText = message;
  chatbox.appendChild(messageDiv);
  chatbox.scrollTop = chatbox.scrollHeight;
}

function showLoading() {
  const loadingDiv = document.createElement('div');
  loadingDiv.className = 'bot-message loading';
  loadingDiv.id = 'loading';
  loadingDiv.innerText = 'Typing...';
  chatbox.appendChild(loadingDiv);
  chatbox.scrollTop = chatbox.scrollHeight;
}

function removeLoading() {
  const loadingDiv = document.getElementById('loading');
  if (loadingDiv) {
    loadingDiv.remove();
  }
}

function getBotResponse(input) {
  fetch('bot.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'message=' + encodeURIComponent(input)
  })
    .then(response => response.text())
    .then(data => {
      removeLoading();
      addMessage(data, 'bot');
    })
    .catch(error => {
      console.error('Error:', error);
      removeLoading();
      addMessage("⚠️ Sorry, there was a problem. Please try again.", 'bot');
    });
}

// Handle quick buttons (simulate user message + send immediately)
document.querySelectorAll('.quick-btn').forEach(button => {
  button.addEventListener('click', () => {
    const msg = button.getAttribute('data-msg');
    addMessage(msg, 'user');
    showLoading();
    getBotResponse(msg);
  });
});
