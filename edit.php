<?php
$conn = mysqli_connect("localhost", "root", "", "project");

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

$product = null;

// Fetch product
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM inventory_system WHERE id=$id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Product not found!";
        exit();
    }
} else {
    echo "No product ID provided!";
    exit();
}

// Update
if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    
    $amount = $price * $quantity;

    $sql = "UPDATE inventory_system SET 
            name='$name',
            price='$price',
            quantity='$quantity',
            amount='$amount' 
            WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <a href="index.php" class="btn btn-add">⬅ Back</a>

    <h1>✏️ Edit Product</h1>
</header>

<div class="container">
    
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">

        <label>Product Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" 
               placeholder="Enter your product name" required>

        <label>Price</label>
        <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" 
               placeholder="Enter your price" required>

        <label>Quantity</label>
        <input type="number" name="quantity" value="<?php echo $product['quantity']; ?>" 
               placeholder="Enter your quantity" required>

        <label>Total Amount</label>
        <input type="text" value="<?php echo $product['price'] * $product['quantity']; ?>" disabled>

        <input type="submit" name="update" value="Update Product">
    </form>
</div>

</body>
</html>

<?php $conn->close(); ?>