require("dotenv").config();
const express = require("express");
const cors = require("cors");
const nodemailer = require("nodemailer");

const app = express();
app.use(express.json());
app.use(cors()); // Allows cross-origin requests

const PORT = 5000;

// Setup Nodemailer for email sending
const transporter = nodemailer.createTransport({
  service: "gmail",
  auth: {
    user: process.env.EMAIL_USER, // Use environment variables for security
    pass: process.env.EMAIL_PASS, // Use an App Password for Gmail
  },
});

// OTP Storage (Can be replaced with Redis/Database)
const otpStorage = {};

// Generate a random 6-digit OTP
const generateOTP = () => Math.floor(100000 + Math.random() * 900000).toString();

// Send OTP Route
app.post("/send-otp", async (req, res) => {
  const { email } = req.body;

  if (!email) {
    return res.status(400).json({ error: "Email is required" });
  }

  const otp = generateOTP();
  otpStorage[email] = otp; // Store OTP temporarily

  const mailOptions = {
    from: process.env.EMAIL_USER,
    to: email,
    subject: "Your OTP Code",
    text: `Your OTP code is: ${otp}`,
  };

  try {
    await transporter.sendMail(mailOptions);
    res.status(200).json({ message: "OTP sent successfully!" });
  } catch (error) {
    console.error("Error sending OTP:", error);
    res.status(500).json({ error: "Failed to send OTP. Please try again later." });
  }
});

// Verify OTP Route
app.post("/verify-otp", (req, res) => {
  const { email, otp } = req.body;

  if (!email || !otp) {
    return res.status(400).json({ error: "Email and OTP are required" });
  }

  if (otpStorage[email] && otpStorage[email] === otp) {
    delete otpStorage[email]; // Remove OTP after successful verification
    return res.status(200).json({ message: "OTP verified successfully!" });
  } else {
    return res.status(400).json({ error: "Invalid or expired OTP" });
  }
});

// Start the Server
app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
});