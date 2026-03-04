<?php

session_start();

// ===== LOGIN PROTECTION =====
// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "project");

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// ===== SEARCH FUNCTION =====
$search = "";
if (isset($_POST['search'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search']);
    $sql = "SELECT * FROM inventory_system 
            WHERE name LIKE '%$search%' 
            OR id LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM inventory_system";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* ===== RESET ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: linear-gradient(rgba(15, 32, 65, 0.95), rgba(20, 48, 85, 0.95)),
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(0,200,255,0.1)"/><circle cx="80" cy="80" r="2" fill="rgba(0,200,255,0.1)"/></svg>');
            color: #fff;
        }

        /* ===== HEADER ===== */
        header {
            background: linear-gradient(135deg, #0f2041 0%, #1a3a52 50%, #0d47a1 100%);
            padding: 30px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.8),
                        inset 0 1px 0 rgba(255, 255, 255, 0.1);
            border-bottom: 2px solid rgba(0, 200, 255, 0.3);
            animation: slideDown 0.6s ease-out;
        }

        @keyframes slideDown { from {opacity:0; transform:translateY(-30px);} to{opacity:1; transform:translateY(0);} }

        header h1 {
            font-family: 'Orbitron', sans-serif;
            color: #00eeff;
            font-size: 32px;
            letter-spacing: 3px;
            text-shadow: 0 0 20px #00eeff, 0 0 40px rgba(0, 200, 255, 0.5);
            font-weight: 900;
        }

        .header-btns { display: flex; gap: 15px; flex-wrap: wrap; }
        .header-btns a {
            padding: 13px 26px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            background: linear-gradient(135deg, #00ffcc 0%, #00b894 100%);
            color: #0f2041;
            box-shadow: 0 6px 20px rgba(0, 255, 204, 0.6);
            transition: all 0.35s cubic-bezier(0.23, 1, 0.320, 1);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0, 255, 204, 0.4);
        }
        .header-btns a::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(135deg, #00b894, #00ffcc); transition: left 0.35s; z-index: -1; }
        .header-btns a:hover { transform: translateY(-5px) scale(1.08); box-shadow: 0 12px 35px rgba(0, 255, 204, 0.9); color: #0f2041; }
        .header-btns a:hover::before { left:0; }

        .container { max-width: 1300px; margin: 50px auto; padding: 20px; }

        .btn {
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            display: inline-block;
            background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.5);
            transition: all 0.35s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 107, 107, 0.5);
            margin-bottom: 30px;
        }
        .btn::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(135deg, #ee5a6f, #ff6b6b); transition: left 0.35s; z-index: -1; }
        .btn:hover { transform: translateY(-3px) scale(1.05); box-shadow: 0 10px 30px rgba(255, 107, 107, 0.8); }
        .btn:hover::before { left:0; }

        .table-wrapper {
            background: linear-gradient(135deg, rgba(15, 32, 65, 0.8), rgba(20, 48, 85, 0.8));
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6),
                        inset 0 1px 0 rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(0, 200, 255, 0.3);
            backdrop-filter: blur(10px);
            overflow-x: auto;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        @keyframes fadeInUp { from {opacity:0; transform:translateY(30px);} to {opacity:1; transform:translateY(0);} }

        table { width: 100%; border-collapse: collapse; }
        thead { background: linear-gradient(135deg, rgba(0, 238, 255, 0.25), rgba(0, 114, 255, 0.25)); border-bottom: 2px solid rgba(0, 200, 255, 0.5); }
        th { padding: 18px; color: #00eeff; font-size: 13px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; text-align: left; }
        td { padding: 16px 18px; color: rgba(255, 255, 255, 0.9); font-size: 14px; border-bottom: 1px solid rgba(0, 200, 255, 0.2); font-weight: 500; }
        tbody tr { transition: all 0.35s ease; }
        tbody tr:hover { background: rgba(0, 238, 255, 0.12); transform: translateX(5px); }

        .stock-high { background: rgba(0, 255, 100, 0.2); color: #00ff64; padding: 6px 12px; border-radius: 6px; font-weight: 700; border: 1px solid #00ff64; }
        .stock-low { background: rgba(255, 87, 34, 0.2); color: #ff5722; padding: 6px 12px; border-radius: 6px; font-weight: 700; border: 1px solid #ff5722; animation: blink 1.5s infinite; }
        @keyframes blink { 0%,100%{opacity:1;}50%{opacity:0.6;} }

        td a { padding: 8px 16px; margin: 0 5px; border-radius: 6px; font-weight: 700; text-decoration: none; color: #fff; display: inline-block; transition: all 0.3s ease; font-size: 12px; position: relative; overflow: hidden; border:1px solid; }
        td a::before { content: ''; position:absolute; top:0; left:-100%; width:100%; height:100%; transition:left 0.3s; z-index:-1; }
        .btn-edit { background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%); box-shadow: 0 6px 18px rgba(0,198,255,0.6); border-color: rgba(0,198,255,0.6); }
        .btn-edit::before { background: linear-gradient(135deg, #0072ff, #00c6ff); }
        .btn-edit:hover { transform: translateY(-3px) scale(1.07); box-shadow: 0 10px 28px rgba(0,198,255,0.9); }
        .btn-edit:hover::before { left:0; }
        .btn-delete { background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); box-shadow: 0 6px 18px rgba(255,75,43,0.6); border-color: rgba(255,75,43,0.6); }
        .btn-delete::before { background: linear-gradient(135deg,#ff4b2b,#ff416c); }
        .btn-delete:hover { transform: translateY(-3px) scale(1.07); box-shadow: 0 10px 28px rgba(255,75,43,0.9); }
        .btn-delete:hover::before { left:0; }
        td[colspan]{ text-align:center; padding:50px !important; font-size:18px; color:rgba(255,255,255,0.6); font-style:italic; }

        /* ===== SEARCH BAR ===== */
        .search-form { display:flex; justify-content:flex-end; gap:12px; margin-bottom:25px; flex-wrap:wrap; }
        .search-box { width:320px; max-width:100%; padding:14px 22px; border-radius:40px; border:2px solid rgba(0,200,255,0.4); background: linear-gradient(135deg, rgba(15,32,65,0.9), rgba(20,48,85,0.9)); color:#fff; font-size:14px; font-weight:500; outline:none; transition: all 0.4s ease; box-shadow:0 6px 20px rgba(0,200,255,0.4); }
        .search-box:focus { border-color:#00eeff; box-shadow:0 10px 30px rgba(0,238,255,0.9); transform: scale(1.05);}
        .search-btn { padding:14px 24px; border-radius:40px; border:none; font-weight:700; font-size:13px; cursor:pointer; background:linear-gradient(135deg, #00ffcc, #00b894); color:#0f2041; box-shadow:0 6px 20px rgba(0,255,204,0.6); transition: all 0.35s ease;}
        .search-btn:hover { transform: translateY(-4px) scale(1.08); box-shadow: 0 12px 35px rgba(0,255,204,0.9); }

        @media(max-width:768px){ .search-form{ justify-content:center; flex-direction:column; align-items:center; } .search-box,.search-btn{width:100%; text-align:center;} }
    </style>
</head>
<body>

<header>
    <h1>📦 TECHNOVA INVENTORY SYSTEM</h1>
    <div class="header-btns">
        <a href="add.php">➕ Add Product</a>
        <a href="logout.php">🚪 Logout</a>
    </div>
</header>

<div class="container">
    <a href="dashboard.html" class="btn">⬅ Back to Dashboard</a>

    <!-- ===== SEARCH FORM ===== -->
    <form method="POST" class="search-form">
        <input type="text" name="search" class="search-box" placeholder="🔍 Search Products..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="search-btn">Search</button>
    </form>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total Value</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $status = ($row['quantity'] > 5) ? '<span class="stock-high">✓ In Stock</span>' : '<span class="stock-low">⚠ Low Stock</span>';

                        echo "
                        <tr>
                            <td>#" . htmlspecialchars($row['id']) . "</td>
                            <td><strong>" . htmlspecialchars($row['name']) . "</strong></td>
                            <td>Rs. " . number_format($row['price'], 2) . "</td>
                            <td>" . htmlspecialchars($row['quantity']) . " units</td>
                            <td>Rs. " . number_format($row['amount'], 2) . "</td>
                            <td>" . $status . "</td>
                            <td>
                                <a href='edit.php?id=" . urlencode($row['id']) . "' class='btn-edit'>✏️ Edit</a>
                                <a href='delete.php?id=" . urlencode($row['id']) . "' class='btn-delete' onclick='return confirm(\"Are you sure you want to delete this product?\")'>🗑️ Delete</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>📭 No products found. Add your first product now!</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>

<?php $conn->close(); ?>
