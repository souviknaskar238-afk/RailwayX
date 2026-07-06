<?php

session_start();

include '../home/config.php';

/*
|--------------------------------------------------------------------------
| GET FORM DATA
|--------------------------------------------------------------------------
*/

$name              = trim($_POST['name']);
$email             = trim($_POST['email']);
$phone             = trim($_POST['phone']);
$password          = trim($_POST['password']);
$confirm_password  = trim($_POST['confirm_password']);

/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

// Empty fields

if(
    empty($name) ||
    empty($email) ||
    empty($phone) ||
    empty($password) ||
    empty($confirm_password)
){

    $_SESSION['error'] = "All fields are required.";

    header("Location: ../home/../home/register.php");
    exit();
}

// Password match

if($password != $confirm_password){

    $_SESSION['error'] = "Passwords do not match.";

    header("Location: ../home/register.php");
    exit();
}

// Check existing email

$check_email = "SELECT * FROM users WHERE email = '$email'";

$result = mysqli_query($conn, $check_email);

if(mysqli_num_rows($result) > 0){

    $_SESSION['error'] = "Email already exists.";

    header("Location: ../user/../home/register.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| HASH PASSWORD
|--------------------------------------------------------------------------
*/

$hashed_password = $password;

/*
|--------------------------------------------------------------------------
| INSERT USER
|--------------------------------------------------------------------------
*/

$insert_query = "

INSERT INTO users
(name, email, phone, password)

VALUES
('$name', '$email', '$phone', '$hashed_password')

";

if(mysqli_query($conn, $insert_query)){

    $_SESSION['success'] = "Registration successful! Please login.";

    header("Location: ../home/login.php");
    exit();
}
else{

    $_SESSION['error'] = "Something went wrong.";

    header("Location: ../home/register.php");
    exit();
}

?>