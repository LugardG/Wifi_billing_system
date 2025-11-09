<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

$username = $_SESSION['username']; // Logged-in admin username
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="admin.css">

  <!-- Font Awesome & Charts -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
  
  <!-- Load JavaScript -->
  <script src="admin.js" defer></script>
</head>
<body class="light">

<!-- Sidebar -->
<aside class="sidebar">
  <button id="toggle-btn" class="toggle-btn" aria-label="Toggle sidebar">
    <i class="fa fa-bars"></i>
  </button>
  <h2>Admin Panel</h2>

  <ul class="menu">
    <li><a href="#dashboard" class="nav-link active" data-section="dashboardSection"><i class="fa fa-home"></i><span>Dashboard</span></a></li>
    <li><a href="#customers" class="nav-link" data-section="customersSection"><i class="fa fa-users"></i><span>Customers</span></a></li>
    <li><a href="#payments" class="nav-link" data-section="paymentsSection"><i class="fa fa-credit-card"></i><span>Payments</span></a></li>
    <li><a href="#sessions" class="nav-link" data-section="sessionsSection"><i class="fa fa-network-wired"></i><span>Sessions</span></a></li>
    <li><a href="#plans" class="nav-link" data-section="plansSection"><i class="fa fa-wifi"></i><span>Plans</span></a></li>
    <li><a href="#tickets" class="nav-link" data-section="ticketsSection"><i class="fa fa-ticket"></i><span>Tickets</span></a></li>
    <li><a href="#reports" class="nav-link" data-section="reportsSection"><i class="fa fa-chart-line"></i><span>Reports</span></a></li>
  </ul>

  <div class="logout">
    <a href="logout.php" onclick="return confirmLogout()" data-tooltip="Logout" data-icon="🔒">
      <i class="fa fa-sign-out-alt"></i><span class="text">Logout</span>
    </a>
  </div>
</aside>

<!-- Main Content -->
<main class="main">
  <div class="topbar">
    <h2>Welcome, <?php echo htmlspecialchars($username); ?> 👋</h2>
    <div class="theme-toggle-wrapper" title="Toggle theme">
      <button class="theme-toggle" id="themeToggle"></button>
    </div>
  </div>

  <!-- Dashboard -->
  <section id="dashboardSection" class="section active">
    <h1>Dashboard</h1>
    <div class="cards">
      <div class="card"><h3>Total Customers</h3><p id="totalCustomers">0</p></div>
      <div class="card"><h3>Active Plans</h3><p id="activePlans">0</p></div>
      <div class="card"><h3>Payments Today</h3><p id="paymentsToday">KES 0</p></div>
      <div class="card"><h3>Active Sessions</h3><p id="activeSessions">0</p></div>
    </div>
  </section>

  <!-- Customers -->
  <section id="customersSection" class="section">
    <h1>Customers</h1>
    <table id="customersTable">
      <thead><tr><th>Name</th><th>Plan</th><th>Status</th></tr></thead>
      <tbody></tbody>
    </table>
  </section>

  <!-- Payments -->
  <section id="paymentsSection" class="section" style="display:none;">
  <h2>Payments</h2>
  <table id="paymentsTable">
    <thead><tr><th>Customer</th><th>Amount</th><th>Date</th><th>Method</th></tr></thead>
    <tbody></tbody>
  </table>
</section>


  <!-- Sessions -->
  <section id="sessionsSection" class="section" style="display:none;">
  <h2>Sessions</h2>
  <table id="sessionsTable">
    <thead><tr><th>Customer</th><th>Devices</th><th>Status</th><th>Expiry</th></tr></thead>
    <tbody></tbody>
  </table>
</section>


  <!-- Plans -->
  <section id="plansSection" class="section" style="display:none;">
  <h2>Plans</h2>
  <table id="plansTable">
    <thead><tr><th>Name</th><th>Price</th><th>Duration</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody></tbody>
  </table>
</section>


  <!-- Tickets -->
  <section id="ticketsSection" class="section">
    <h1>Support Tickets</h1>
    <table id="ticketsTable">
      <thead><tr><th>Ticket #</th><th>Customer #</th><th>Message</th><th>Category</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php
        $conn = new mysqli('localhost', 'root', 'Luc1601ky@2025', 'famget_wifi');
        if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

        $result = $conn->query("SELECT * FROM tickets ORDER BY id DESC");
        if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['ticket_number']}</td>
                    <td>{$row['customer_number']}</td>
                    <td>{$row['customer_msg']}</td>
                    <td>{$row['category']}</td>
                    <td>{$row['status']}</td>
                    <td>
                      <form method='POST' action='update_ticket_status.php'>
                        <input type='hidden' name='ticket_number' value='{$row['ticket_number']}'>
                        <select name='status'>
                          <option value='pending' " . ($row['status'] == 'pending' ? 'selected' : '') . ">Pending</option>
                          <option value='resolved' " . ($row['status'] == 'resolved' ? 'selected' : '') . ">Resolved</option>
                        </select>
                        <button type='submit'>Update</button>
                      </form>
                    </td>
                  </tr>";
          }
        }
        $conn->close();
        ?>
      </tbody>
    </table>
  </section>

  <!-- Reports -->
  <section id="reportsSection" class="section">
    <h1>Reports</h1>
    <div class="filters">
      <label>From:</label><input type="date" id="startDate">
      <label>To:</label><input type="date" id="endDate">
      <label>Type:</label>
      <select id="reportType">
        <option value="daily">Daily</option>
        <option value="weekly" selected>Weekly</option>
        <option value="monthly">Monthly</option>
      </select>
      <button id="loadReport">Load Report</button>
      <button id="downloadReport">Download PDF</button>
    </div>
    <table id="reportsTable">
      <thead><tr><th>Customer</th><th>Amount (KES)</th><th>Date</th></tr></thead>
      <tbody></tbody>
    </table>
    <canvas id="reportsChart" style="max-width:100%; height:400px; margin-top:20px;"></canvas>
  </section>
</main>

<!-- Scripts -->
<script>
    // Global error handler
    window.onerror = function(msg, url, line) {
        console.error(`Error: ${msg}\nAt: ${url}:${line}`);
        return false;
    };
    
    // Navigation function
    function switchSection(sectionId) {
        // Hide all sections
        document.querySelectorAll('.section').forEach(section => {
            section.style.display = 'none';
            section.classList.remove('active');
        });
        
        // Show selected section
        const selectedSection = document.getElementById(sectionId);
        if (selectedSection) {
            selectedSection.style.display = 'block';
            selectedSection.classList.add('active');
        }
        
        // Update navigation buttons
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('data-target') === sectionId) {
                link.classList.add('active');
            }
        });
        
        // Load section data
        switch(sectionId) {
            case 'dashboardSection':
                loadDashboardStats();
                break;
            case 'customersSection':
                loadCustomers();
                break;
            case 'paymentsSection':
                loadPayments();
                break;
            case 'sessionsSection':
                loadSessions();
                break;
            case 'plansSection':
                loadPlans();
                break;
        }
        
        // Close mobile menu if open
        const sidebar = document.querySelector('.sidebar');
        if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
        }
    }
    
    // Add loading indicator
    function showLoading(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = '<div class="loading">Loading...</div>';
        }
    }
    
    // Show initial loading state
    document.addEventListener('DOMContentLoaded', () => {
        // Show initial loading indicators
        ['totalCustomers', 'activePlans', 'paymentsToday', 'activeSessions'].forEach(id => {
            showLoading(id);
        });
        
        // Load dashboard data immediately
        loadDashboardStats();
        
</body>
</html>
