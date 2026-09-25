<?php
/*
Register page will let a new user register its own account. 
password_hash() creates a new password hash with a random generated salt
ID and the hashed password will be stored in the database
*/

require "config/db.php";

//If form was submitted (user registered), insert username and password into the database
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $email = $_POST["email"];
    $phone_number = $_POST["phone_number"];
    $role = $_POST["role"];

    if (empty($username)){
        echo "<script>alert('Username can not be empty');</script>";
    } elseif (empty($password)){
        echo "<script>alert('Password can not be empty');</script>";
    } elseif (empty($first_name)) {
        echo "<script>alert('First Name can not be empty');</script>";
    } elseif (empty($last_name)){
        echo "<script>alert('Last Name can not be empty');</script>";  
    } elseif (empty($email)) {
        echo "<script>alert('Email can not be empty');</script>";
    } elseif (empty($phone_number)) {
        echo "<script>alert('Phone number can not be empty');</script>";
    } elseif (empty($role)) {
        echo "<script>alert('Please pick a role');</script>";
    } elseif (!preg_match("/^[A-Za-z0-9]+$/", $username)){ // ^ marks start of string, $ marks end of string, + go through the string one index at a time
        echo "<script>alert('Username can only contain letters and numbers');</script>";
    } elseif (
        strlen($password) <8 || //At least 8 characters long
        !preg_match("/[A-Z]/", $password) || //At least 1 uppercase letter
        !preg_match("/[a-z]/", $password) || //At least 1 lowercase letter
        !preg_match("/[0-9]/", $password) || //At least 1 number
        !preg_match('/[^A-Za-z0-9]/', $password) //At least 1 special character
    ) {
        echo "<script>alert('Password must be at least 8 characters and include an uppercase letter, a lowercase letter, a number, and a special character');</script>";
    } else {
        //PASSWORD_DEFAULT uses bcrypt algo + generates random salt
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        //Create fast hashed verification token. 
        $verification_token = bin2hex(random_bytes(16));
        $token_hash = hash("sha256", $verification_token);

        $sql = "INSERT into System_user (user_username, user_password_hash, user_name, user_surname, user_email, user_phone_number, user_role, user_verification_token_hash) values (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $username, $hashed_password, $first_name, $last_name, $email, $phone_number, $role, $token_hash);
        $result = $stmt->execute();

        if ($result) {
            $mail = require "mailer.php";
            $mail->addAddress($email); //Send to
            $mail->Subject = "InfTraction Account Verification";

            // Create verification link
            $host = $_SERVER['HTTP_HOST'];  //Returns the Host header from the current request 
            $verify_link = "http://$host/verify.php?token=" . $verification_token;

            // Raw unhashed token goes into the link
            //verify.php will hash and compare the token ithat is stored n the database
            $mail->Body = "Click the link to verify your account: <a href=\"$verify_link\">$verify_link</a>";
            
            // Send email
            $mail->send();
            echo "<script>alert('Registered! Check your email to verify your account before logging in.');</script>";

        } else {
            echo "<script>alert('Registration failed');</script>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfTraction Register</title>
        <link rel="stylesheet" href="css/register.css">
</head>
<body>
    <!-- Logo and background -->
    <img src="/Pictures/logo_nobackground.png" class= "logo">
    <img src="/Pictures/virus_bl_nobg.png" class="virus_bl">

    <a href="index.php" class="login-link">Already Registered? Click here to login</a>

    <!-- Register form --> 
    <form action="register.php" method="POST" class="inputs">
        <div class="align_columns">

            <!-- Left column -->
            <div class="column">
                <label for="username"><img src="/Pictures/user.icon-bgremoved.png" class="icon"></label>
                <input type="text" id="username" name="username" placeholder="Enter username">
                <span class="error_text" id="username_error"></span>

                <label for="password"><img src="/Pictures/password.icon-bgremoved.png" class="icon"></label>
                <input type="password" id="password" name="password" placeholder="Enter password">
                <span class="error_text" id="password_error"></span>
            </div>

            <!-- Middle column -->
            <div class="column">
                <label for="first_name"></label>
                <input type="text" id="first_name" name="first_name" placeholder="Enter first name">
                <span class="error_text" id="first_name_error"></span>

                <label for="last_name"></label>
                <input type="text" id="last_name" name="last_name" placeholder="Enter last name">
                <span class="error_text" id="last_name_error"></span>

                <label for="role"></label>
                <select id="role" name="role">
                    <option value="">Choose a Role</option>
                    <option value="doctor">Doctor</option>
                    <option value="epidemiologist">Epidemiologist</option>
                </select>
                <span class="error_text" id="role_error"></span>

                <input type="submit" value="Register" class="button">
            </div>

            <!-- Right column -->
            <div class="column">
                <label for="email"><img src="/Pictures/mail.icon-bgremoved.png" class="icon"></label>
                <input type="email" id="email" name="email" placeholder="Enter email">
                <span class="error_text" id="email_error"></span>

                <label for="phone_number"><img src="/Pictures/phone.icon-bgremoved.png" class="icon"></label>
                <input type="text" id="phone_number" name="phone_number" placeholder="Enter phone number">
                <span class="error_text" id="phone_number_error"></span>

            </div>
        </div>
    </form>

    <script src="js/register.js"></script>

</body>
</html>


