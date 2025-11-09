<?php
header("Content-Type: application/json");
include "db.php"; // expects $conn (mysqli)

// If db not available, return empty
if (!isset($conn)) {
    echo json_encode(["rows" => [], "totals" => []]);
    exit;
}

$type = isset($_GET['type']) ? $_GET['type'] : 'weekly';
$start = isset($_GET['start']) && $_GET['start'] !== '' ? $_GET['start'] : null;
$end = isset($_GET['end']) && $_GET['end'] !== '' ? $_GET['end'] : null;

// Build date filter
$where = "1=1";
$params = [];
if ($start && $end) {
    $where .= " AND DATE(p.date) BETWEEN ? AND ?";
    $params[] = $start;
    $params[] = $end;
} elseif ($start) {
    $where .= " AND DATE(p.date) >= ?";
    $params[] = $start;
} elseif ($end) {
    $where .= " AND DATE(p.date) <= ?";
    $params[] = $end;
} else {
    // default range depending on type
    if ($type === 'weekly') {
        $where .= " AND DATE(p.date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($type === 'monthly') {
        $where .= " AND DATE(p.date) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    } else {
        // daily -> last 1 day
        $where .= " AND DATE(p.date) >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
    }
}

// Detect if payments table has customer_id column or customer column
$has_customer_id = false;
$has_customer_col = false;
$colsRes = $conn->query("SHOW COLUMNS FROM payments");
if ($colsRes) {
    while ($col = $colsRes->fetch_assoc()) {
        $field = strtolower($col['Field']);
        if ($field === 'customer_id') $has_customer_id = true;
        if ($field === 'customer' || $field === 'customer_name' || $field === 'customer_number') $has_customer_col = true;
    }
}

// Build main rows query
if ($has_customer_id) {
    // join with customers to get name
    $sqlRows = "SELECT p.*, COALESCE(c.name, '') AS customer FROM payments p LEFT JOIN customers c ON p.customer_id = c.id WHERE $where ORDER BY p.date DESC";
} elseif ($has_customer_col) {
    // use payments.customer
    $sqlRows = "SELECT p.*, COALESCE(p.customer, '') AS customer FROM payments p WHERE $where ORDER BY p.date DESC";
} else {
    // fallback: just return payments and use phone column if exists or blank
    $sqlRows = "SELECT p.*, '' AS customer FROM payments p WHERE $where ORDER BY p.date DESC";
}

// Prepare statement
$stmt = $conn->prepare($sqlRows);
if ($stmt) {
    if (count($params) === 2) {
        $stmt->bind_param("ss", $params[0], $params[1]);
    } elseif (count($params) === 1) {
        $stmt->bind_param("s", $params[0]);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        // Normalize fields used by frontend: customer, amount, date
        $rows[] = [
            "customer" => isset($r['customer']) ? $r['customer'] : (isset($r['customer_name']) ? $r['customer_name'] : ''),
            "amount" => isset($r['amount']) ? $r['amount'] : (isset($r['total']) ? $r['total'] : 0),
            "date" => isset($r['date']) ? date('Y-m-d', strtotime($r['date'])) : (isset($r['created_at']) ? date('Y-m-d', strtotime($r['created_at'])) : '')
        ];
    }
    $stmt->close();
} else {
    $rows = [];
}

// Build aggregated totals per day for chart
// We'll reuse same where clause but group by DATE(p.date)
$sqlTotals = "SELECT DATE(p.date) as dt, SUM(p.amount) as total_amount FROM payments p WHERE $where GROUP BY DATE(p.date) ORDER BY DATE(p.date) ASC";
$stmt2 = $conn->prepare($sqlTotals);
$totals = [];
if ($stmt2) {
    if (count($params) === 2) {
        $stmt2->bind_param("ss", $params[0], $params[1]);
    } elseif (count($params) === 1) {
        $stmt2->bind_param("s", $params[0]);
    }
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while ($t = $res2->fetch_assoc()) {
        $totals[] = [
            "date" => $t['dt'],
            "amount" => (float)$t['total_amount']
        ];
    }
    $stmt2->close();
}

// If totals empty but rows exist, derive totals by grouping rows
if (empty($totals) && !empty($rows)) {
    $map = [];
    foreach ($rows as $r) {
        $d = $r['date'] ?: date('Y-m-d');
        if (!isset($map[$d])) $map[$d] = 0;
        $map[$d] += floatval($r['amount']);
    }
    ksort($map);
    foreach ($map as $d => $a) {
        $totals[] = ["date" => $d, "amount" => $a];
    }
}

echo json_encode([
    "rows" => $rows,
    "totals" => $totals
]);

$conn->close();
?>
