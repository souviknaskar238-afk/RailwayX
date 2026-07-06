<?php

session_start();
include 'config.php';

/*
|--------------------------------------------------------------------------
| AUTHENTICATION FILE
|--------------------------------------------------------------------------
| This file:
| 1. Checks email & password
| 2. Verifies role (admin/user)
| 3. Creates session
| 4. Redirects to correct dashboard
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ================= GET FORM DATA =================

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // ================= VALIDATION =================

    if (empty($email) || empty($password) || empty($role)) {

        $_SESSION['error'] = "All fields are required!";
        header("Location: login.php");
        exit();
    }

    // ================= CHECK USER =================

    $query = "SELECT * FROM users WHERE email = ? AND role = ? LIMIT 1";

    $stmt = mysqli_prepare($conn, $query);

    mysqli_stmt_bind_param($stmt, "ss", $email, $role);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    // ================= USER FOUND =================

    if (mysqli_num_rows($result) > 0) {

        $user = mysqli_fetch_assoc($result);

        // ================= VERIFY PASSWORD =================

        if ($password == $user['password']) {

            // ================= CREATE SESSION =================

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // ================= REDIRECT =================

            if ($user['role'] == 'admin') {

                header("Location: ../admin/dashboard.php");
                exit();

            } else {

                header("Location: ../user/user_dashboard.php");
                exit();
            }

        } else {

            $_SESSION['error'] = "Incorrect password!";
            header("Location: login.php");
            exit();
        }

    } else {

        $_SESSION['error'] = "User not found!";
        header("Location: login.php");
        exit();
    }

} else {

    header("Location: login.php");
    exit();
}
?>