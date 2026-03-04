<?php
session_start();

// ===== LOGIN PROTECTION =====
// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}
$conn =mysqli_connect("localhost", "root", "", "project");



if(isset($_POST['submit'])) {

    $name = $_POST['name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $amount = $_POST['amount'];

    // Calculate Total Amount
    $amount = $price * $quantity;

    // Insert with Amount
    $sql = "INSERT INTO inventory_system (name, price, quantity, amount) 
            VALUES ('$name', '$price', '$quantity', '$amount')";

    if($conn->query($sql) === TRUE) {

        header("Location: index.php");
        exit(); 
    } else {
        echo "Error: ".$conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product - Mini Inventory System</title>
     <link rel="stylesheet" href="style.css">

</head>
<body>
<header>
      <a href="index.php" class="btn btn-add">⬅ Back</a>
  
   <h1>Add New Product</h1>
</header>

<div class="container">
       
    <form method="POST">
        <label>Product Name:</label>
        <input type="text" name="name" placeholder="Enter your product" required>
<br>
  <label>Product Price:</label>
        <input type="number" step="0.01" name="price" placeholder="Enter the Price" required>
<br>
  <label>Product Quantity:</label>
        <input type="number" name="quantity" placeholder="Enter the Quantity" required>
        <label>description:</label>
        <input type="number" name="description" placeholder="description" >
<br>
  
<br>
        <input type="submit" name="submit" value="Add Product">

    </form>
</div>

</body>
</html>
