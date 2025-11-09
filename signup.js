// Elements
const signupForm = document.getElementById("signupForm");
const errorDiv = document.getElementById("error");

signupForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  const username = document.getElementById("username").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();
  const confirmPassword = document.getElementById("confirmPassword").value.trim();

  // Simple validation
  if (!username || !email || !password || !confirmPassword) {
    errorDiv.textContent = "All fields are required.";
    return;
  }

  if (password !== confirmPassword) {
    errorDiv.textContent = "Passwords do not match.";
    return;
  }

  try {
    // Send data to PHP signup backend
    const res = await fetch("signup.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&confirm_password=${encodeURIComponent(confirmPassword)}`
    });

    const data = await res.json();

    if (data.success) {
      alert("Account created successfully! Redirecting to login...");
      window.location.href = "login.html";
    } else {
      errorDiv.textContent = data.message || "Signup failed.";
    }
  } catch (err) {
    console.error(err);
    errorDiv.textContent = "Server error. Try again later.";
  }
});
