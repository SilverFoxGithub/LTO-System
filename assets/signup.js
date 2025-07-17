// Function to send OTP
function sendOTP() {
    const email = document.querySelector("#email").value;

    if (!email) {
        alert("Please enter your email.");
        return;
    }

    fetch("http://localhost/LTO-System/api/send_otp.php", { // Updated API path
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email }),
    })
    .then(response => response.json())
    .then(result => {
    if (result.status === "success") {
        alert(result.message); // Should now say "OTP sent successfully."
    } else {
        alert(result.message || result.error || "Something went wrong.");
    }
})

    .catch(error => {
        console.error("Error:", error);
        alert("Something went wrong. Please try again later huhu.");
    });
}

// Function to check password strength
function checkPasswordStrength() {
    const password = document.querySelector("#password").value;
    const strengthText = document.querySelector("#password-strength");

    if (!password) {
        strengthText.textContent = "";
        return;
    }

    if (password.length < 8) {
        strengthText.textContent = "Weak (Min. 8 characters required)";
        strengthText.style.color = "red";
    } else {
        strengthText.textContent = "Strong";
        strengthText.style.color = "green";
    }
}

// Function to handle signup
function handleSignup() {
    const full_name = document.querySelector("#full-name").value;
    const email = document.querySelector("#email").value;
    const password = document.querySelector("#password").value;
    const confirm_password = document.querySelector("#confirm-password").value;
    const role = document.querySelector("#role").value;
    const otp = document.querySelector("#otp").value; // Added OTP field

    if (!full_name || !email || !password || !confirm_password || !role || !otp) {
        alert("Please fill out all fields.");
        return;
    }

    if (password !== confirm_password) {
        alert("Passwords do not match.");
        return;
    }

    fetch("http://localhost/LTO-System/api/verify_otp.php", { // Updated API path    
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ full_name, email, password, confirm_password, role, otp }), // Send OTP along with user details
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert("Signup successful! Redirecting to login...");
            window.location.href = "index.html";
        } else {
            alert(result.error);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Something went wrong. Please try again later.");
    });
}

// Attach event listeners after DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    const otpButton = document.querySelector("#send-otp");
    const passwordInput = document.querySelector("#password");
    const signupButton = document.querySelector("#signup-btn");

    if (otpButton) otpButton.addEventListener("click", sendOTP);
    if (passwordInput) passwordInput.addEventListener("keyup", checkPasswordStrength);
    if (signupButton) signupButton.addEventListener("click", handleSignup);
});