// Elements
const loginBtn = document.getElementById("loginBtn");
const usernameInput = document.getElementById("username");
const passwordInput = document.getElementById("password");
const rememberMe = document.getElementById("rememberMe");
const errorDiv = document.getElementById("error");

// On page load, check if credentials are saved
window.addEventListener("DOMContentLoaded", () => {
  const savedUsername = localStorage.getItem("username");
  const savedPassword = localStorage.getItem("password");

  if (savedUsername && savedPassword) {
    usernameInput.value = savedUsername;
    passwordInput.value = savedPassword;
    rememberMe.checked = true;
  }
});

// Login button click
loginBtn.addEventListener("click", async (e) => {
  e.preventDefault();

  const username = usernameInput.value.trim();
  const password = passwordInput.value.trim();

  if (!username || !password) {
    errorDiv.textContent = "Please enter both username and password.";
    return;
  }

  try {
    // Call your PHP login API
    const res = await fetch("login.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
    });

    const data = await res.json();

    if (data.success) {
      // Remember Me
      if (rememberMe.checked) {
        localStorage.setItem("username", username);
        localStorage.setItem("password", password);
      } else {
        localStorage.removeItem("username");
        localStorage.removeItem("password");
      }

      // ✅ Use JSON redirect provided by PHP
      if (data.redirect) {
        window.location.href = data.redirect;
      } else {
        window.location.href = "admin.php"; // fallback
      }
    } else {
      errorDiv.textContent = data.message || "Invalid credentials.";
    }
  } catch (err) {
    console.error(err);
    errorDiv.textContent = "Server error. Try again later.";
  }
});

// Forgot Password
document.getElementById("forgotPassword").addEventListener("click", (e) => {
  e.preventDefault();
  alert("Redirect to Forgot Password page (to be implemented).");
});

// Sign Up
document.getElementById("signUp").addEventListener("click", (e) => {
  e.preventDefault();
  window.location.href = "signup.html"; // redirect to your signup page
});
