<?php
// ===== DATABASE CONNECTION =====
$host = "localhost";
$username = "root";
$password = "";
$database = "project";

$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("❌ Connection Failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8mb4");
?>