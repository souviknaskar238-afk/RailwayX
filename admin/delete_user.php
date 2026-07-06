<?php
session_start();
require_once '../home/config.php';

// Check if an ID was provided in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Security Best Practice: Use Prepared Statements to prevent SQL Injection
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Redirect back with a success message in the URL
        header("Location: manage_users.php?msg=deleted");
        exit();
    } else {
        die("Error deleting user: " . mysqli_error($conn));
    }
} else {
    // If no ID is provided, just send them back
    header("Location: manage_users.php");
    exit();
}
?>