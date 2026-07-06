<?php
// db_config.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "railway"; // Make sure this is your exact database name!

// Create connection - MAKE SURE this variable is exactly $conn
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
?>