<?php

session_start();

require "config/db.php";

//captcha
$captcha = $_POST['captcha'];

if ($captcha !== $_SESSION['captcha_answer']) {
    echo "<script>
            alert('Incorrect CAPTCHA.');
            window.location.href = 'index.php';
          </script>";
    exit();
}


// Get username and password from the login form
$username = $_POST['user'];
$password = $_POST['pwd'];

// Find the user in the database
$sql = "SELECT user_id, user_password_hash, user_role
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

        // Store information about the logged-in user
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_role'] = $user['user_role'];

        // Login successful
        header("Location: homepage.php");
        exit();

    } else {

        // Login information is incorrect
        echo "<script>
                alert('Login information is incorrect.');
                window.location.href = 'index.php';
              </script>";
    }

} else {

    // Login information is incorrect
    echo "<script>
            alert('Login information is incorrect.');
            window.location.href = 'index.php';
          </script>";
}

$stmt->close();
$conn->close();

?>
