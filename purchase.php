<?php
session_start();

// ===== LOGIN PROTECTION =====
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

require_once 'db.php';

$success = "";
$error = "";

// ===== HANDLE FORM SUBMISSION =====
if(isset($_POST['submit'])) {
    $product_type = mysqli_real_escape_string($conn, $_POST['product_type']);
    $supplier_name = mysqli_real_escape_string($conn, $_POST['supplier_name']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $purchase_price = mysqli_real_escape_string($conn, $_POST['purchase_price']);
    $purchase_date = mysqli_real_escape_string($conn, $_POST['purchase_date']);
    
    if($product_type == 'existing') {
        // ===== EXISTING PRODUCT =====
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
        
        // Get product details
        $product_query = "SELECT name, quantity, price FROM inventory_system WHERE id = '$product_id'";
        $product_result = mysqli_query($conn, $product_query);
        
        if($product_result && mysqli_num_rows($product_result) > 0) {
            $product = mysqli_fetch_assoc($product_result);
            $product_name = $product['name'];
            $current_quantity = $product['quantity'];
            $current_price = $product['price'];
            
            // Calculate total amount
            $total_amount = $purchase_price * $quantity;
            
            // Insert purchase record
            $insert_purchase = "INSERT INTO purchases (product_id, product_name, supplier_name, quantity, purchase_price, total_amount, purchase_date) 
                               VALUES ('$product_id', '$product_name', '$supplier_name', '$quantity', '$purchase_price', '$total_amount', '$purchase_date')";
            
            if(mysqli_query($conn, $insert_purchase)) {
                // Update inventory quantity
                $new_quantity = $current_quantity + $quantity;
                $new_amount = $current_price * $new_quantity;
                
                $update_inventory = "UPDATE inventory_system 
                                    SET quantity = '$new_quantity', amount = '$new_amount' 
                                    WHERE id = '$product_id'";
                
                if(mysqli_query($conn, $update_inventory)) {
                    $success = "✓ Purchase recorded successfully! Stock updated.";
                } else {
                    $error = "✗ Purchase recorded but failed to update inventory.";
                }
            } else {
                $error = "✗ Failed to record purchase: " . mysqli_error($conn);
            }
        } else {
            $error = "✗ Product not found!";
        }
        
    } else {
        // ===== NEW PRODUCT =====
        $product_name = mysqli_real_escape_string($conn, $_POST['new_product_name']);
        $selling_price = mysqli_real_escape_string($conn, $_POST['selling_price']);
        
        // Calculate amounts
        $total_amount = $purchase_price * $quantity;
        $inventory_amount = $selling_price * $quantity;
        
        // Insert into inventory_system first
        $insert_inventory = "INSERT INTO inventory_system (name, price, quantity, amount) 
                            VALUES ('$product_name', '$selling_price', '$quantity', '$inventory_amount')";
        
        if(mysqli_query($conn, $insert_inventory)) {
            $new_product_id = mysqli_insert_id($conn);
            
            // Insert purchase record
            $insert_purchase = "INSERT INTO purchases (product_id, product_name, supplier_name, quantity, purchase_price, total_amount, purchase_date) 
                               VALUES ('$new_product_id', '$product_name', '$supplier_name', '$quantity', '$purchase_price', '$total_amount', '$purchase_date')";
            
            if(mysqli_query($conn, $insert_purchase)) {
                $success = "✓ New product added to inventory and purchase recorded!";
            } else {
                $error = "✗ Product added to inventory but failed to record purchase.";
            }
        } else {
            $error = "✗ Failed to add product to inventory: " . mysqli_error($conn);
        }
    }
}

// Get all products for dropdown
$products_query = "SELECT id, name, quantity, price FROM inventory_system ORDER BY name ASC";
$products_result = mysqli_query($conn, $products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Purchase - Inventory System</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="purchase_sales_style.css">
    <script>
        function toggleProductInput() {
            const type = document.querySelector('input[name="product_type"]:checked').value;
            const existingSection = document.getElementById('existing-product-section');
            const newSection = document.getElementById('new-product-section');
            
            if(type === 'existing') {
                existingSection.style.display = 'block';
                newSection.style.display = 'none';
                document.getElementById('product_id').required = true;
                document.getElementById('new_product_name').required = false;
                document.getElementById('selling_price').required = false;
            } else {
                existingSection.style.display = 'none';
                newSection.style.display = 'block';
                document.getElementById('product_id').required = false;
                document.getElementById('new_product_name').required = true;
                document.getElementById('selling_price').required = true;
            }
        }
        
        window.onload = function() {
            toggleProductInput();
        }
    </script>
</head>
<body>

<header>
    <h1>📦 PURCHASE MODULE</h1>
    <div class="header-btns">
        <a href="purchase_list.php">📋 View Purchases</a>
        <a href="dashboard.html">🏠 Dashboard</a>
    </div>
</header>

<div class="container">
    <a href="dashboard.html" class="btn">⬅ Back to Dashboard</a>

    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="form-wrapper">
        <h2> Add New Purchase</h2>
        <form method="POST">
            
            <label>Purchase Type:</label>
            <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="radio" name="product_type" value="existing" checked onchange="toggleProductInput()">
                    <span>Existing Product (Restock)</span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="radio" name="product_type" value="new" onchange="toggleProductInput()">
                    <span>New Product (Add to Inventory)</span>
                </label>
            </div>

            <!-- EXISTING PRODUCT SECTION -->
            <div id="existing-product-section">
                <label>Select Product:</label>
                <select name="product_id" id="product_id">
                    <option value="">Choose Product</option>
                    <?php 
                    if($products_result && mysqli_num_rows($products_result) > 0):
                        while($product = mysqli_fetch_assoc($products_result)):
                    ?>
                        <option value="<?php echo $product['id']; ?>">
                            <?php echo htmlspecialchars($product['name']); ?> 
                            (Stock: <?php echo $product['quantity']; ?>, Price: Rs. <?php echo number_format($product['price'], 2); ?>)
                        </option>
                    <?php 
                        endwhile;
                    endif;
                    ?>
                </select>
            </div>

            <!-- NEW PRODUCT SECTION -->
            <div id="new-product-section" style="display: none;">
                <label>New Product Name:</label>
                <input type="text" name="new_product_name" id="new_product_name" placeholder="Enter product name">

                <label>Selling Price (per unit):</label>
                <input type="number" step="0.01" name="selling_price" id="selling_price" placeholder="Price to sell at" min="0">
            </div>

            <label>Supplier Name:</label>
            <input type="text" name="supplier_name" placeholder="Enter supplier name" required>

            <label>Quantity:</label>
            <input type="number" name="quantity" placeholder="Enter quantity" min="1" required>

            <label>Purchase Price (per unit):</label>
            <input type="number" step="0.01" name="purchase_price" placeholder="Enter purchase price" min="0" required>

            <label>Purchase Date:</label>
            <input type="date" name="purchase_date" value="<?php echo date('Y-m-d'); ?>" required>

            <input type="submit" name="submit" value="✓ Record Purchase">
        </form>
    </div>
</div>

</body>
</html>