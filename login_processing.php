<?php

session_start();

require "config/db.php";
require_once "Role.php";          
require_once "PrivilegedUser.php";  

//captcha
$captcha = $_POST['captcha'];

if ($captcha !== $_SESSION['captcha_answer']) {
    echo "<script>
            alert('Incorrect CAPTCHA.');
            window.location.href = '/index.php';
          </script>";
    exit();
}


// Get username and password from the login form
$username = $_POST['user'];
$password = $_POST['pwd'];

// Find the user in the database
$sql = "SELECT user_id, user_password_hash, role_id, user_verification_token_hash, user_status
        FROM System_user
        WHERE user_username = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error preparing login: " . $conn->error);
}

$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();

// Check if the username exists
if ($result->num_rows === 1) {

    $user = $result->fetch_assoc();

    // Check the entered password against the stored password hash
    if (password_verify($password, $user['user_password_hash'])) {

        //Check if user has verified email
        if ($user['user_verification_token_hash'] !== NULL){
            echo "<script>
                    alert('Please verify your email.');
                    window.location.href = '/index.php';
                  </script>";
            exit(); //End script
        }

        // Then check account status
        if ($user['user_status'] === 'pending') {

            header("Location: pending.php");
            exit();

        } elseif ($user['user_status'] === 'deactivated') {

            echo "<script>
                    alert('This account has been deactivated.');
                    window.location.href = '/index.php';
                  </script>";
            exit();
        }

        // Store information about the logged-in user
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['last_activity'] = time();

        // creates a PrivilegedUser-object and saves the session for access check in authenticate_check.php
        $_SESSION['privUser'] = PrivilegedUser::getByUsername($username);

        // Login successful
        if ($user['role_id'] == 1) {
            header("Location: admin.php");
        } else {
            header("Location: homepage.php");
        }
        exit();

    } else {

        // Login information is incorrect
        echo "<script>
                alert('Login information is incorrect.');
                window.location.href = '/index.php';
              </script>";
    }

} else {

    // Login information is incorrect
    echo "<script>
            alert('Login information is incorrect.');
            window.location.href = '/index.php';
          </script>";
}

$stmt->close();
$conn->close();

?>
