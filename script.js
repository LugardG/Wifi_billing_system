/* ----------------- Plan subscription and payment ----------------- */
async function subscribe(planName) {
    // Show phone number prompt
    const phoneNumber = prompt("Enter your phone number (format: 07XXXXXXXX):", "");
    if (!phoneNumber) return;

    // Validate phone number format
    if (!/^0[17][0-9]{8}$/.test(phoneNumber)) {
        alert("Please enter a valid Safaricom number (format: 07XXXXXXXX or 01XXXXXXXX)");
        return;
    }

    // Get the plan price from the DOM
    const planCard = document.querySelector(`.plan-card:has(h3:contains('${planName}'))`);
    if (!planCard) {
        alert("Error: Could not find plan details");
        return;
    }

    try {
        // Show loading state
        const button = planCard.querySelector('button');
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = "Processing...";

        // Format phone number for M-Pesa (254XXXXXXXXX)
        const formattedPhone = "254" + phoneNumber.substring(1);

        // Call stk_push.php
        const response = await fetch('stk_push.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                phone: formattedPhone,
                plan: planName
            })
        });

        const result = await response.json();

        if (result.ResponseCode === "0") {
            button.textContent = "Check your phone!";
            // Show instructions modal
            showPaymentModal(`
                <h3>Payment Instructions</h3>
                <p>1. Check your phone for the M-Pesa payment prompt</p>
                <p>2. Enter your M-Pesa PIN to complete payment</p>
                <p>3. You'll receive an SMS confirmation</p>
                <p>4. Your WiFi access will activate automatically</p>
            `);
        } else {
            throw new Error(result.ResponseDescription || "Payment initiation failed");
        }

        // Reset button after 30 seconds
        setTimeout(() => {
            button.disabled = false;
            button.textContent = originalText;
        }, 30000);

    } catch (error) {
        alert("Error: " + error.message);
        const button = planCard.querySelector('button');
        button.disabled = false;
        button.textContent = "Try Again";
    }
}

/* ----------------- Login modal functions ----------------- */
const loginModal = document.getElementById("loginModal");

function openModal() {
  if (loginModal) loginModal.style.display = "flex";
}

function closeModal() {
  if (loginModal) loginModal.style.display = "none";
}

window.onclick = function(event) {
  // close login modal if clicking outside
  if (event.target === loginModal) {
    closeModal();
  }
}

/* login form submit (placeholder) */
function loginUser() {
  const username = document.getElementById("username").value.trim();
  const code = document.getElementById("accessCode").value.trim();

  if (!username || !code) {
    alert("Please enter both username and access code.");
    return false;
  }

  // call verify_access.php with username and code
  fetch('verify_access.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `username=${encodeURIComponent(username)}&code=${encodeURIComponent(code)}`
  }).then(res => res.json()).then(data => {
    if (data.success) {
      alert('Access granted. Welcome!');
      closeModal();
      // Optionally redirect or set UI state to connected
    } else {
      if (data.message === 'Subscription expired') {
        if (confirm('Your subscription has expired. Would you like to purchase a new plan now?')) {
          window.location.hash = '#plans';
        }
      } else {
        alert('Access denied: ' + data.message);
      }
    }
  }).catch(err => {
    alert('Server error while verifying access code.');
  });

  return false;
}

/* ----------------- Support popup (floating) ----------------- */
const supportPopup = document.getElementById("supportPopup");
const supportBtn = document.getElementById("supportBtn");

/**
 * toggleSupportPopup(show:boolean)
 * If `true` - opens popup, if `false` - closes it.
 */
function toggleSupportPopup(show) {
  if (!supportPopup) return;
  supportPopup.style.display = show ? "block" : "none";
  // manage focus for accessibility
  if (show) {
    supportPopup.setAttribute('aria-hidden', 'false');
    // focus first actionable link
    const firstLink = supportPopup.querySelector('.popup-actions a');
    if (firstLink) firstLink.focus();
  } else {
    supportPopup.setAttribute('aria-hidden', 'true');
    if (supportBtn) supportBtn.focus();
  }
}

// allow clicking outside popup to close (on mobile)
document.addEventListener('click', function(e) {
  if (!supportPopup) return;
  const isClickInside = supportPopup.contains(e.target) || (supportBtn && supportBtn.contains(e.target));
  if (!isClickInside && supportPopup.style.display === 'block') {
    toggleSupportPopup(false);
  }
});
// close when pressing Escape
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    if (supportPopup && supportPopup.style.display === 'block') toggleSupportPopup(false);
    if (loginModal && loginModal.style.display === 'flex') closeModal();
  }
});
// Handle Subscribe button clicks for each plan
async function subscribe(planName) {
  const phone = prompt(`Enter your M-PESA phone number (format 2547XXXXXXXX) for ${planName}:`);
  if (!phone) {
    alert("Payment cancelled.");
    return;
  }

  const confirmPay = confirm(`Proceed with payment for ${planName}?`);
  if (!confirmPay) return;

  try {
    const response = await fetch("stk_push.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ phone, plan: planName })
    });

    const data = await response.json();

    if (data.ResponseCode === "0") {
      alert("✅ STK Push sent! Please check your phone and enter your M-PESA PIN to complete the payment.");
    } else {
      alert("❌ Failed to initiate STK Push: " + (data.errorMessage || data.ResponseDescription || "Unknown error"));
    }
  } catch (error) {
    alert("⚠️ Error sending STK request: " + error.message);
  }
}
let currentPlan = '';

function subscribe(planName) {
  currentPlan = planName;
  document.getElementById('planSelected').innerText = `You selected: ${planName}`;
  document.getElementById('selectedPlan').value = planName;
  openPaymentModal();
}

function openPaymentModal() {
  document.getElementById('paymentModal').style.display = 'flex';
}

function closePaymentModal() {
  document.getElementById('paymentModal').style.display = 'none';
}

async function sendSTKPush(event) {
  event.preventDefault();
  const phone = document.getElementById('paymentPhone').value.trim();
  const plan = document.getElementById('selectedPlan').value;

  if (!phone || !/^2547\d{8}$/.test(phone)) {
    alert("⚠️ Please enter a valid phone number (format: 2547XXXXXXXX)");
    return false;
  }

  try {
    const response = await fetch("stk_push.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ phone, plan })
    });

     const text = await response.text();
    let data;
    try {
      data = JSON.parse(text);
    } catch (e) {
      // response wasn't JSON (HTML error page or other). Show raw text for debugging.
      alert("Server returned unexpected response. See console for details.");
      console.error('Non-JSON response from stk_push.php:', text);
      closePaymentModal();
      return false;
    }

    if (data.ResponseCode === "0") {
      alert("✅ STK Push sent! Check your phone and complete payment.");

      // Poll check_payment_status.php for access code
      const pollInterval = 5000; // 5s
      const maxAttempts = 12; // total 60s
      let attempts = 0;

      const poll = setInterval(async () => {
        attempts++;
        try {
          const chk = await fetch(`check_payment_status.php?phone=${encodeURIComponent(phone)}`);
          const chkData = await chk.json();
          if (chkData.success) {
            clearInterval(poll);
            // show access code / username to user
            showPaymentModal(`
              <h3>Payment Successful</h3>
              <p>Your username: <strong>${chkData.username}</strong></p>
              <p>Your access code: <strong>${chkData.access_code}</strong></p>
              <p>Expires: ${chkData.expiry}</p>
            `);
          } else {
            if (attempts >= maxAttempts) {
              clearInterval(poll);
              showPaymentModal(`<h3>Payment processing</h3><p>We couldn't detect your payment yet. If you've completed payment, wait a moment and try logging in. If not, try again.</p>`);
            }
          }
        } catch (err) {
          console.error('Poll error', err);
        }
      }, pollInterval);

    } else {
      alert("❌ Failed to send STK Push: " + (data.errorMessage || data.ResponseDescription || "Unknown error"));
    }

    closePaymentModal();
  } catch (error) {
    alert("⚠️ Network or server error: " + error.message);
  }

  return false;
}
