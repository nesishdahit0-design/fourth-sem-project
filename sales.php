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
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $selling_price = mysqli_real_escape_string($conn, $_POST['selling_price']);
    $sale_date = mysqli_real_escape_string($conn, $_POST['sale_date']);
    
    // Validate product_id is not empty
    if(empty($product_id)) {
        $error = "✗ Please select a product!";
    } else {
        // Get product details from inventory
        $product_query = "SELECT name, quantity, price FROM inventory_system WHERE id = '$product_id'";
        $product_result = mysqli_query($conn, $product_query);
        
        if($product_result && mysqli_num_rows($product_result) > 0) {
            $product = mysqli_fetch_assoc($product_result);
            $product_name = $product['name'];
            $current_quantity = $product['quantity'];
            $current_price = $product['price'];
            
            // Check if product is available in inventory
            if($current_quantity <= 0) {
                $error = "✗ Product not available! This item is out of stock.";
            }
            // Check if enough stock available
            else if($current_quantity < $quantity) {
                $error = "✗ Insufficient stock! Available: " . $current_quantity . " units only.";
            } else {
                // Calculate total amount
                $total_amount = $selling_price * $quantity;
                
                // Insert sales record
                $insert_sale = "INSERT INTO sales (product_id, product_name, customer_name, quantity, selling_price, total_amount, sale_date) 
                               VALUES ('$product_id', '$product_name', '$customer_name', '$quantity', '$selling_price', '$total_amount', '$sale_date')";
                
                if(mysqli_query($conn, $insert_sale)) {
                    // Update inventory quantity
                    $new_quantity = $current_quantity - $quantity;
                    $new_amount = $current_price * $new_quantity;
                    
                    $update_inventory = "UPDATE inventory_system 
                                        SET quantity = '$new_quantity', amount = '$new_amount' 
                                        WHERE id = '$product_id'";
                    
                    if(mysqli_query($conn, $update_inventory)) {
                        $success = "✓ Sale recorded successfully! Inventory updated. Remaining stock: " . $new_quantity . " units";
                    } else {
                        $error = "✗ Sale recorded but failed to update inventory.";
                    }
                } else {
                    $error = "✗ Failed to record sale: " . mysqli_error($conn);
                }
            }
        } else {
            $error = "✗ Product not available! This product does not exist in inventory.";
        }
    }
}

// Get only products that are available in inventory (quantity > 0)
$products_query = "SELECT id, name, quantity, price FROM inventory_system WHERE quantity > 0 ORDER BY name ASC";
$products_result = mysqli_query($conn, $products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Sale - Inventory System</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="purchase_sales_style.css">
    <script>
        function updateMaxQuantity() {
            const select = document.getElementById('product_id');
            const quantityInput = document.getElementById('quantity');
            const selectedOption = select.options[select.selectedIndex];
            
            if(selectedOption.value) {
                const maxStock = selectedOption.getAttribute('data-stock');
                quantityInput.max = maxStock;
                quantityInput.placeholder = 'Max available: ' + maxStock + ' units';
            } else {
                quantityInput.max = '';
                quantityInput.placeholder = 'Enter quantity to sell';
            }
        }
    </script>
</head>
<body>

<header>
    <h1> SALES MODULE</h1>
    <div class="header-btns">
        <a href="sales_list.php">📋 View Sales</a>
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
        <h2>➕ Add New Sale</h2>
        <form method="POST">
            <label>Select Product (Only In-Stock Items):</label>
            <select name="product_id" id="product_id" required onchange="updateMaxQuantity()">
                <option value="">Choose Product</option>
                <?php 
                if($products_result && mysqli_num_rows($products_result) > 0):
                    while($product = mysqli_fetch_assoc($products_result)):
                ?>
                    <option value="<?php echo $product['id']; ?>" data-stock="<?php echo $product['quantity']; ?>">
                        <?php echo htmlspecialchars($product['name']); ?> 
                        (Available: <?php echo $product['quantity']; ?> units, Price: Rs. <?php echo number_format($product['price'], 2); ?>)
                    </option>
                <?php 
                    endwhile;
                else:
                ?>
                    <option value="">⚠ No products available in stock</option>
                <?php endif; ?>
            </select>

            <label>Customer Name:</label>
            <input type="text" name="customer_name" placeholder="Enter customer name" required>

            <label>Quantity:</label>
            <input type="number" name="quantity" id="quantity" placeholder="Enter quantity to sell" min="1" required>

            <label>Selling Price (per unit):</label>
            <input type="number" step="0.01" name="selling_price" placeholder="Enter selling price" min="0" required>

            <label>Sale Date:</label>
            <input type="date" name="sale_date" value="<?php echo date('Y-m-d'); ?>" required>

            <input type="submit" name="submit" value="✓ Record Sale">
        </form>
    </div>
</div>

</body>
</html>