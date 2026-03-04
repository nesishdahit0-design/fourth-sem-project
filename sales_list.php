<?php
session_start();

// ===== LOGIN PROTECTION =====
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

require_once 'db.php';

// ===== SEARCH FUNCTION =====
$search = "";
if(isset($_POST['search'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search']);
    $sql = "SELECT * FROM sales 
            WHERE product_name LIKE '%$search%' 
            OR customer_name LIKE '%$search%'
            OR id LIKE '%$search%'
            ORDER BY created_at DESC";
} else {
    $sql = "SELECT * FROM sales ORDER BY created_at DESC";
}

$result = mysqli_query($conn, $sql);

// ===== CALCULATE SUMMARY =====
$summary_query = "SELECT 
                    COUNT(*) as total_sales,
                    SUM(quantity) as total_quantity,
                    SUM(total_amount) as total_revenue
                  FROM sales";
$summary_result = mysqli_query($conn, $summary_query);
$summary = mysqli_fetch_assoc($summary_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales List - Inventory System</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="purchase_sales_style.css">
</head>
<body>

<header>
    <h1>💰 SALES RECORDS</h1>
    <div class="header-btns">
        <a href="sales.php">➕ Add Sale</a>
        <a href="dashboard.html">🏠 Dashboard</a>
    </div>
</header>

<div class="container">
    <a href="dashboard.html" class="btn">⬅ Back to Dashboard</a>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="number"><?php echo $summary['total_sales'] ?? 0; ?></div>
            <div class="label">Total Sales</div>
        </div>
        <div class="summary-card">
            <div class="number"><?php echo $summary['total_quantity'] ?? 0; ?></div>
            <div class="label">Total Items Sold</div>
        </div>
        <div class="summary-card">
            <div class="number">Rs. <?php echo number_format($summary['total_revenue'] ?? 0, 2); ?></div>
            <div class="label">Total Revenue</div>
        </div>
    </div>

    <!-- Search Form -->
    <form method="POST" class="search-form">
        <input type="text" name="search" class="search-box" placeholder="🔍 Search by Product, Customer or ID..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="search-btn">Search</button>
    </form>

    <div class="table-wrapper">
        <h2>📋 Sales History</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Customer</th>
                    <th>Quantity</th>
                    <th>Selling Price</th>
                    <th>Total Amount</th>
                    <th>Sale Date</th>
                    <th>Recorded On</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if($result && mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        echo "
                        <tr>
                            <td>#" . htmlspecialchars($row['id']) . "</td>
                            <td><strong>" . htmlspecialchars($row['product_name']) . "</strong></td>
                            <td>" . htmlspecialchars($row['customer_name']) . "</td>
                            <td>" . htmlspecialchars($row['quantity']) . " units</td>
                            <td>Rs. " . number_format($row['selling_price'], 2) . "</td>
                            <td>Rs. " . number_format($row['total_amount'], 2) . "</td>
                            <td>" . date('d M Y', strtotime($row['sale_date'])) . "</td>
                            <td>" . date('d M Y H:i', strtotime($row['created_at'])) . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>📭 No sales records found. Add your first sale!</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>

<?php mysqli_close($conn); ?>
