document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Admin Dashboard Initialized');

    // Cache DOM elements
    const sidebar = document.querySelector('.sidebar');
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('.section');
    const toggleBtn = document.getElementById('toggle-btn');

    // Initialize navigation
    function initNavigation() {
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const sectionId = this.dataset.section;
                
                if (!sectionId) {
                    console.error('No section ID specified for link:', this);
                    return;
                }

                console.log('Navigation clicked:', sectionId);

                // Update navigation state
                navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');

                // Update sections visibility
                sections.forEach(section => {
                    section.style.display = section.id === sectionId ? 'block' : 'none';
                });

                // Load section data
                loadSectionData(sectionId);

                // Close mobile sidebar
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('active');
                    document.body.classList.remove('no-scroll');
                }
            });
        });
    }

    // Toggle sidebar
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            document.body.classList.toggle('no-scroll');
        });
    }

    // Load section data
    async function loadSectionData(sectionId) {
        console.log('Loading section:', sectionId);
        
        try {
            switch(sectionId) {
                case 'dashboardSection':
                    await loadDashboardStats();
                    break;
                case 'customersSection':
                    await loadCustomers();
                    break;
                case 'paymentsSection':
                    await loadPayments();
                    break;
                case 'sessionsSection':
                    await loadSessions();
                    break;
                case 'plansSection':
                    await loadPlans();
                    break;
                case 'ticketsSection':
                    await loadTickets();
                    break;
                case 'reportsSection':
                    // Will be loaded when date range is selected
                    break;
            }
        } catch (error) {
            console.error('Error loading section:', error);
            showMessage('Failed to load content');
        }
    }

    // Dashboard Stats
    async function loadDashboardStats() {
        try {
            const response = await fetch('dashboard_stats.php');
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            
            document.getElementById('totalCustomers').textContent = data.totalCustomers || 0;
            document.getElementById('activePlans').textContent = data.activePlans || 0;
            document.getElementById('paymentsToday').textContent = `KES ${data.paymentsToday || 0}`;
            document.getElementById('activeSessions').textContent = data.activeSessions || 0;
            
            console.log('Dashboard stats loaded:', data);
        } catch (error) {
            console.error('Error loading dashboard stats:', error);
            showMessage('Error loading dashboard data');
        }
    }
      // Customers
    async function loadCustomers() {
  try {
    const response = await fetch("get_customers.php");
    if (!response.ok) throw new Error("HTTP error " + response.status);

    const data = await response.json();

    // Handle both plain array or { list: [...] }
    const customers = Array.isArray(data) ? data : data.list || [];

    const tbody = document.querySelector("#customersTable tbody");
    if (!tbody) return;

    if (customers.length === 0) {
      tbody.innerHTML = `<tr><td colspan="3">No customers found</td></tr>`;
      return;
    }

    tbody.innerHTML = customers.map(c => `
      <tr>
        <td>${c.name}</td>
        <td>${c.plan}</td>
        <td style="color:${c.status === 'online' ? 'green' : 'red'};font-weight:bold;">
          ${c.status}
        </td>
      </tr>
    `).join("");
  } catch (err) {
    console.error("Error loading customers:", err);
    const tbody = document.querySelector("#customersTable tbody");
    if (tbody) tbody.innerHTML = `<tr><td colspan="3">Failed to load customers</td></tr>`;
  }
}


    // Payments
   async function loadPayments() {
  const response = await fetch("get_payments.php");
  const data = await response.json();
  const tbody = document.querySelector("#paymentsTable tbody");
  tbody.innerHTML = data.length
    ? data.map(p => `<tr><td>${p.customer}</td><td>${p.amount}</td><td>${p.date}</td><td>${p.method}</td></tr>`).join("")
    : "<tr><td colspan='4'>No payments found</td></tr>";
}


    // Sessions
   async function loadSessions() {
  const response = await fetch("get_sessions.php");
  const data = await response.json();
  const tbody = document.querySelector("#sessionsTable tbody");
  tbody.innerHTML = data.length
    ? data.map(s => `<tr><td>${s.customer}</td><td>${s.devices}</td><td>${s.status}</td><td>${s.expiry}</td></tr>`).join("")
    : "<tr><td colspan='4'>No sessions found</td></tr>";
}


    // Plans
    async function loadPlans() {
  const response = await fetch("get_plans.php");
  const data = await response.json();
  const tbody = document.querySelector("#plansTable tbody");
  tbody.innerHTML = data.length
    ? data.map(p => `<tr><td>${p.name}</td><td>${p.price}</td><td>${p.duration}</td><td>${p.status}</td><td>${p.actions}</td></tr>`).join("")
    : "<tr><td colspan='5'>No plans found</td></tr>";
}


    // Tickets
    async function loadTickets() {
  const response = await fetch("get_tickets.php");
  const data = await response.json();
  const tbody = document.querySelector("#ticketsTable tbody");
  tbody.innerHTML = data.length
    ? data.map(t => `<tr><td>${t.ticket_number}</td><td>${t.customer_number}</td><td>${t.customer_msg}</td><td>${t.category}</td><td>${t.status}</td><td>${t.created_at}</td></tr>`).join("")
    : "<tr><td colspan='6'>No tickets found</td></tr>";
}


    // Utility functions
    function showMessage(message) {
        alert(message);
    }

    function escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') return '';
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Initialize
    initNavigation();
    loadDashboardStats(); // Load dashboard data on page load
});

// Confirm toggle button exists and works
const toggleBtn = document.getElementById("toggle-btn");
if (toggleBtn) {
  console.log("🟢 Toggle button ready:", toggleBtn);
} else {
  console.warn("⚠️ Toggle button not found!");
}

// ====================
// DOM Elements
// ====================
const sidebar = document.querySelector(".sidebar");
const main = document.querySelector(".main");
const body = document.body;

// Safety checks
if (!sidebar) console.warn("⚠️ Sidebar element not found (.sidebar).");
if (!main) console.warn("⚠️ Main element not found (.main).");

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  // Load initial dashboard data
  loadDashboardStats();
  
  // Initialize navigation
  initializeNavigation();
});

// ====================
// Sidebar Toggle (Desktop + Mobile)
// ====================
if (toggleBtn && sidebar) {
  toggleBtn.addEventListener("click", () => {
    sidebar.classList.toggle("collapsed");

    // For smaller screens (mobile slide-in)
    if (window.innerWidth <= 768) {
      sidebar.classList.toggle("active");
      document.body.classList.toggle("no-scroll");
    }

    // Save sidebar state
    localStorage.setItem("sidebarCollapsed", sidebar.classList.contains("collapsed") ? "1" : "0");
  });

  // Restore saved state
  if (localStorage.getItem("sidebarCollapsed") === "1") {
    sidebar.classList.add("collapsed");
  }
}

// ====================
// API Endpoints
// ====================
const endpoints = {
  customers: "get_customers.php",
  payments: "get_payments.php",
  sessions: "get_sessions.php",
  plans: "get_plans.php",
  tickets: "get_tickets.php",
  reports: "get_reports.php",
  updatePlan: "update_plan.php",
  deletePlan: "delete_plan.php",
  togglePlan: "toggle_plan.php"
};

// ====================
// Data Loading Functions
// ====================
async function loadDashboardStats() {
  try {
    const stats = await fetchData("dashboard_stats.php");
    if (stats) {
      document.getElementById("totalCustomers").textContent = stats.totalCustomers || 0;
      document.getElementById("activePlans").textContent = stats.activePlans || 0;
      document.getElementById("paymentsToday").textContent = `KES ${stats.paymentsToday || 0}`;
      document.getElementById("activeSessions").textContent = stats.activeSessions || 0;
    }
    console.log("Dashboard stats loaded:", stats);
  } catch (error) {
    console.error("Error loading dashboard stats:", error);
    showMessage("Error loading dashboard statistics", "error");
  }
}

async function loadCustomers() {
  const data = await fetchData(endpoints.customers);
  const tbody = document.querySelector("#customersTable tbody");
  tbody.innerHTML = data.list.map(c => `
    <tr>
      <td>${c.name}</td>
      <td>${c.plan}</td>
      <td>${c.status}</td>
    </tr>
  `).join("");
}

async function loadPayments() {
  const data = await fetchData(endpoints.payments);
  const tbody = document.querySelector("#paymentsTable tbody");
  tbody.innerHTML = data.list.map(p => `
    <tr>
      <td>${p.customer}</td>
      <td>${p.amount}</td>
      <td>${p.date}</td>
      <td>${p.method}</td>
    </tr>
  `).join("");
}

async function loadSessions() {
  const data = await fetchData(endpoints.sessions);
  const tbody = document.querySelector("#sessionsTable tbody");
  tbody.innerHTML = data.list.map(s => `
    <tr>
      <td>${s.customer}</td>
      <td>${s.device}</td>
      <td>${s.status}</td>
      <td>${s.expiry}</td>
    </tr>
  `).join("");
}

async function loadPlans() {
  const data = await fetchData(endpoints.plans);
  const tbody = document.querySelector("#plansTable tbody");
  tbody.innerHTML = data.list.map(p => `
    <tr>
      <td>${p.name}</td>
      <td>${p.price}</td>
      <td>${p.duration}</td>
      <td>${p.status}</td>
      <td>
        <button onclick="togglePlan(${p.id})">${p.status === 'active' ? 'Disable' : 'Enable'}</button>
        <button onclick="editPlan(${p.id})">Edit</button>
        <button onclick="deletePlan(${p.id})">Delete</button>
      </td>
    </tr>
  `).join("");
}

// ====================
// Helper Functions
// ====================
function showMessage(text, type = "info") {
  const colors = { success: "green", error: "red", info: "blue" };
  console.log(`%c${text}`, `color: ${colors[type] || "gray"};`);
  alert(text);
}

async function fetchData(url, params = "") {
  try {
    console.log(`Fetching data from: ${url}`);
    const res = await fetch(url + params, { 
      cache: "no-store",
      headers: {
        'Accept': 'application/json'
      }
    });
    
    if (!res.ok) {
      throw new Error(`HTTP ${res.status}: ${res.statusText}`);
    }
    
    const data = await res.json();
    console.log(`Data received from ${url}:`, data);
    return data;
  } catch (err) {
    console.error(`Error fetching data from ${url}:`, err);
    showMessage(`Error fetching data: ${err.message}`, "error");
    return null;
  }
}

// ====================
// Navigation
// ====================
function initializeNavigation() {
  const sidebarButtons = document.querySelectorAll(".nav-link");
  
  sidebarButtons.forEach(button => {
    button.addEventListener("click", async (e) => {
      e.preventDefault();
      
      // Get target section
      const target = button.dataset.target;
      if (!target) {
        console.warn("No target specified for button:", button);
        return;
      }
      
      console.log("Navigation clicked:", target);
      
      // Update active section
      document.querySelectorAll(".section").forEach(s => s.classList.remove("active"));
      const section = document.getElementById(target);
      if (section) {
        section.classList.add("active");
        section.style.display = 'block';
      } else {
        console.warn("Section not found:", target);
        return;
      }
      
      // Update active button
      document.querySelectorAll(".nav-link").forEach(b => b.classList.remove("active"));
      button.classList.add("active");
      
      // Load section data
      try {
        console.log("Loading section:", target);
        switch (target) {
          case "dashboardSection":
            await loadDashboardStats();
            break;
          case "customersSection":
            await loadCustomers();
            break;
          case "paymentsSection":
            await loadPayments();
            break;
          case "sessionsSection":
            await loadSessions();
            break;
          case "plansSection":
            await loadPlans();
            break;
          case "ticketsSection":
            await loadTickets();
            break;
          case "reportsSection":
            if (typeof loadReports === 'function') await loadReports();
            break;
        }
      } catch (error) {
        console.error("Error loading section:", error);
        showMessage("Error loading content", "error");
      }

      // Close sidebar on mobile
      if (window.innerWidth <= 768) {
        sidebar.classList.remove("active");
        document.body.classList.remove("no-scroll");
      }
    });
  });
}

// ====================
// Loaders for Sections
// ====================
async function loadCustomers() {
  const data = await fetchData(endpoints.customers);
  const tbody = document.querySelector("#customersTable tbody");
  if (!tbody) return;
  const list = Array.isArray(data.list) ? data.list : (Array.isArray(data) ? data : []);
  tbody.innerHTML = list.length
    ? list.map(c => `
      <tr>
        <td>${c.name}</td>
        <td>${c.plan}</td>
        <td>${c.status}</td>
      </tr>`).join("")
    : `<tr><td colspan="3">No customers found</td></tr>`;
}

async function loadPayments() {
  const data = await fetchData(endpoints.payments);
  const tbody = document.querySelector("#paymentsTable tbody");
  if (!tbody) return;
  const list = Array.isArray(data.list) ? data.list : (Array.isArray(data) ? data : []);
  tbody.innerHTML = list.length
    ? list.map(p => `
      <tr>
        <td>${p.customer ?? '-'}</td>
        <td>KES ${p.amount ?? 0}</td>
        <td>${p.date ?? '-'}</td>
        <td>${p.method ?? '-'}</td>
      </tr>`).join("")
    : `<tr><td colspan="4">No payments available</td></tr>`;
}

async function loadSessions() {
  const data = await fetchData(endpoints.sessions);
  const tbody = document.querySelector("#sessionsTable tbody");
  if (!tbody) return;
  const list = Array.isArray(data.list) ? data.list : (Array.isArray(data) ? data : []);
  tbody.innerHTML = list.length
    ? list.map(s => `
      <tr>
        <td>${s.customer}</td>
        <td>${s.device}</td>
        <td>${s.status}</td>
        <td>${s.expiry}</td>
      </tr>`).join("")
    : `<tr><td colspan="4">No sessions found</td></tr>`;
}

async function loadPlans() {
  const data = await fetchData(endpoints.plans);
  const tbody = document.querySelector("#plansTable tbody");
  if (!tbody) return;
  const list = Array.isArray(data.list) ? data.list : (Array.isArray(data) ? data : []);
  tbody.innerHTML = list.length
    ? list.map(p => `
      <tr>
        <td>${p.name}</td>
        <td>KES ${p.price}</td>
        <td>${p.duration}</td>
        <td>${p.status}</td>
        <td>
          <button class="edit-plan" onclick="editPlan(${p.id})">Edit</button>
          <button class="delete-plan" onclick="deletePlan(${p.id})">Delete</button>
        </td>
      </tr>`).join("")
    : `<tr><td colspan="5">No plans found</td></tr>`;
}

async function loadTickets() {
  const data = await fetchData(endpoints.tickets);
  const tbody = document.querySelector("#ticketsTable tbody");
  if (!tbody) return;
  const list = Array.isArray(data.list) ? data.list : (Array.isArray(data) ? data : []);
  tbody.innerHTML = list.length
    ? list.map(t => `
      <tr>
        <td>${t.ticket_number}</td>
        <td>${t.customer_number}</td>
        <td>${t.customer_msg}</td>
        <td>${t.category}</td>
        <td>${t.status}</td>
        <td>
          <form method="POST" action="update_ticket_status.php">
            <input type="hidden" name="ticket_number" value="${t.ticket_number}">
            <select name="status">
              <option value="pending" ${t.status === "pending" ? "selected" : ""}>Pending</option>
              <option value="resolved" ${t.status === "resolved" ? "selected" : ""}>Resolved</option>
            </select>
            <button type="submit">Update</button>
          </form>
        </td>
      </tr>`).join("")
    : `<tr><td colspan="6">No tickets available</td></tr>`;
}



// ====================
// Theme Toggle
// ====================
const themeBtn = document.getElementById("themeToggle");
if (themeBtn) {
  if (!themeBtn.querySelector(".circle")) {
    const circle = document.createElement("div");
    circle.classList.add("circle");
    themeBtn.appendChild(circle);
  }

  themeBtn.addEventListener("click", () => {
    const isDark = body.classList.toggle("dark");
    body.classList.toggle("light");
    themeBtn.querySelector(".circle").style.transform = isDark ? "translateX(26px)" : "translateX(0)";
    localStorage.setItem("famget_theme", isDark ? "dark" : "light");
  });

  if (localStorage.getItem("famget_theme") === "dark") {
    body.classList.add("dark");
    themeBtn.querySelector(".circle").style.transform = "translateX(26px)";
  } else body.classList.add("light");
}

// ====================
// Plan Actions
// ====================
async function editPlan(id) { showMessage(`Edit Plan ID: ${id}`); }

async function deletePlan(id) {
  if (!confirm("Are you sure you want to delete this plan?")) return;
  try {
    const res = await fetch(`${endpoints.deletePlan}?id=${id}`, { method: "POST" });
    const data = await res.json();
    if (data.success) {
      showMessage("Plan deleted successfully", "success");
      loadPlans();
    } else showMessage("Failed to delete plan", "error");
  } catch (err) {
    showMessage(`Error: ${err.message}`, "error");
  }
}

// ====================
// Reports + PDF Export
// ====================
const reportType = document.getElementById("reportType");
const loadReportBtn = document.getElementById("loadReport");
const downloadReportBtn = document.getElementById("downloadReport");
let reportsChart = null;

async function loadReports() {
  const type = reportType?.value || "weekly";
  const start = document.getElementById("startDate")?.value || "";
  const end = document.getElementById("endDate")?.value || "";
  const params = `?type=${encodeURIComponent(type)}&start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`;
  const data = await fetchData(endpoints.reports, params);

  const tbody = document.querySelector("#reportsTable tbody");
  if (!tbody) return;
  const rows = Array.isArray(data.rows) ? data.rows : (Array.isArray(data.list) ? data.list : []);
  tbody.innerHTML = rows.map(r => `<tr><td>${r.customer}</td><td>${r.amount}</td><td>${r.date}</td></tr>`).join("");

  renderChart(Array.isArray(data.totals) ? data.totals : []);
}

function renderChart(data) {
  const canvas = document.getElementById("reportsChart");
  if (!canvas) return;
  const ctx = canvas.getContext("2d");

  if (reportsChart) reportsChart.destroy();
  reportsChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: data.map(d => d.date),
      datasets: [{
        label: "Income (KES)",
        data: data.map(d => parseFloat(d.amount || 0)),
        borderColor: "rgba(0,168,204,1)",
        backgroundColor: "rgba(0,168,204,0.2)",
        tension: 0.3,
        fill: true
      }]
    },
    options: { responsive: true, plugins: { legend: { display: true } } }
  });
}

if (loadReportBtn) loadReportBtn.addEventListener("click", loadReports);

if (downloadReportBtn) {
  downloadReportBtn.addEventListener("click", async () => {
    const type = reportType?.value || "weekly";
    const start = document.getElementById("startDate")?.value || "";
    const end = document.getElementById("endDate")?.value || "";
    const params = `?type=${encodeURIComponent(type)}&start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`;
    const data = await fetchData(endpoints.reports, params);

    const { jsPDF } = window.jspdf || {};
    if (!jsPDF) return showMessage("PDF library not loaded", "error");

    const doc = new jsPDF();
    doc.setFontSize(16);
    doc.text(`${type.toUpperCase()} Report`, 14, 20);
    doc.setFontSize(12);
    doc.text(`Period: ${start || '-'} to ${end || '-'}`, 14, 28);

    const rows = Array.isArray(data.rows) ? data.rows : (Array.isArray(data.list) ? data.list : []);
    const tableData = rows.map(r => [r.customer, r.amount, r.date]);
    doc.autoTable({ head: [["Customer", "Amount", "Date"]], body: tableData, startY: 36 });
    doc.save(`${type}_report_${Date.now()}.pdf`);
  });
}

// ====================
// Initialize on Load
// ====================
document.addEventListener("DOMContentLoaded", async () => {
  const dash = document.getElementById("dashboardSection");
  if (dash) dash.classList.add("active");

  const dashLink = Array.from(sidebarLinks).find(a => a.dataset.target === "dashboardSection");
  if (dashLink) dashLink.classList.add("active");

  await loadDashboard();
});
// ====================
// Loaders for Sections
// ====================
async function loadCustomers() {
  try {
    const response = await fetch("get_customers.php");
    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    
    const data = await response.json();
    const tbody = document.querySelector("#customersTable tbody");
    if (!tbody) return;

    // Handle case where data is a plain array instead of { list: [...] }
    const customers = Array.isArray(data.list) ? data.list : (Array.isArray(data) ? data : []);

    if (!customers.length) {
      tbody.innerHTML = `<tr><td colspan="3">No customers found</td></tr>`;
      return;
    }

    tbody.innerHTML = customers.map(c => `
      <tr>
        <td>${escapeHtml(c.name)}</td>
        <td>${escapeHtml(c.plan)}</td>
        <td class="${c.status === 'online' ? 'text-green' : 'text-red'}">${escapeHtml(c.status)}</td>
      </tr>
    `).join("");

    console.log("✅ Customers loaded successfully:", customers.length);
  } catch (err) {
    console.error("Error loading customers:", err);
    const tbody = document.querySelector("#customersTable tbody");
    if (tbody) tbody.innerHTML = `<tr><td colspan="3">Failed to load customers</td></tr>`;
  }
}
// Quick simulator call (for testing only)
async function simulatePayment(phone, plan) {
  try {
    const res = await fetch('mpesa_callback_simulate.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ phone, plan })
    });
    const data = await res.json();
    console.log('Simulation result:', data);
    alert('Simulated access code: ' + data.access_code + '\nExpiry: ' + data.expiry);
  } catch (err) {
    alert('Simulation failed: ' + err.message);
  }
}

// Example usage from console:
// simulatePayment('254703378569', 'Daily 1 Device');
