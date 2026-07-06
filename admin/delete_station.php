<?php
session_start();

// Connect to the database
require_once '../home/config.php';

// Check if an ID was provided in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Security Best Practice: Use Prepared Statements to prevent SQL Injection
    $query = "DELETE FROM stations WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    // Execute the deletion
    if (mysqli_stmt_execute($stmt)) {
        // Success! Redirect back to the manage stations page
        header("Location: manage_stations.php?msg=deleted");
        exit();
    } else {
        // If it fails, it is usually because of a Foreign Key constraint 
        // (e.g., you can't delete a station if a Train is currently using it)
        die("Error deleting station: It might be linked to active trains. Database Error: " . mysqli_error($conn));
    }
} else {
    // If someone tries to visit this page directly without an ID, kick them back
    header("Location: manage_stations.php");
    exit();
}
?>