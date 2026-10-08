<?php
/*
Register page will let a new user register its own account. 
password_hash() creates a new password hash with a random generated salt
ID and the hashed password will be stored in the database
*/
require "config/db.php";

// Get all roles from database to use in dropdown menu except admin role
$sql = "SELECT role_id, role_name FROM Roles WHERE role_name != 'Admin'";
$role_result = $conn->query($sql);

// Get all hospital names from database to use in dropdown menu
$sql = "SELECT hospital_id, hospital_name FROM Hospital ORDER BY hospital_name";
$hospital_result = $conn->query($sql);

//If form was submitted (user registered),
// Inserts inputs into the database
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $email = $_POST["email"];
    $phone_number = $_POST["phone_number"];
    $role = $_POST["role"];
    $hospital_id = $_POST["hospital_id"];

    $username_format_valid = preg_match("/^[A-Za-z0-9]+$/", $username); // ^ marks start of string, $ marks end of string, + go through the string one index at a time
    $password_valid = strlen($password) >= 8 // Over 8 or more chars long
        && preg_match("/[A-Z]/", $password) // Contains at least 1 uppercase letter
        && preg_match("/[a-z]/", $password) // Contains at least 1 lowercase letter
        && preg_match("/[0-9]/", $password) // Contains at least 1 number
        && preg_match('/[@!#$%^&*(),.?":{}|<>_\-]/', $password); // Contains at least 1 special char
    $email_format_valid = filter_var($email, FILTER_VALIDATE_EMAIL); //Check if email format is valid
    $phone_number_format_valid = preg_match('/^[0-9+ -]+$/', $phone_number);

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
    } elseif (!$email_format_valid){
        echo "<script>alert('Please enter a valid email address');</script>";
    } elseif (empty($phone_number)) {
        echo "<script>alert('Phone number can not be empty');</script>";
    } elseif (empty($role)) {
        echo "<script>alert('Please pick a role');</script>";
    } elseif (empty($hospital_id)) {
        echo "<script>alert('Please pick a hospital');</script>";
    } elseif (!$username_format_valid){ 
        echo "<script>alert('Username can only contain letters and numbers');</script>";
    } elseif (!$password_valid) {
        echo "<script>alert('Password must be at least 8 characters and include an uppercase letter, a lowercase letter, a number, and a special character');</script>";
    } elseif (!$phone_number_format_valid) {
        echo "<script>alert('Phone number can only contain numbers');</script>";
    } else {
        //PASSWORD_DEFAULT uses bcrypt algo + generates random salt
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        //Create fast hashed verification token. 
        $verification_token = bin2hex(random_bytes(16));
        $token_hash = hash("sha256", $verification_token);

        // Start transaction, 
        // First registered user will be admin
        // If 2 users register at the same time the second user will rollback
        $conn->begin_transaction();
        //FOR UPDATE locks rows until transaction ends
        $count_result = $conn->query("SELECT COUNT(*) AS user_count FROM System_user FOR UPDATE");
        $count_row = $count_result->fetch_assoc();

        $status = "pending";

        // Count users in database and if 0 set role as admin and status as active
        if ($count_row["user_count"] == 0) {
            $admin_role_result = $conn->query("SELECT role_id FROM Roles WHERE role_name = 'Admin'");
            $admin_role_row = $admin_role_result->fetch_assoc();
            $role = $admin_role_row['role_id'];
            $status = "active";
        }

        $sql = "INSERT into System_user (user_id, user_username, user_password_hash, user_name, user_surname, user_email, user_phone_number, role_id, user_verification_token_hash, hospital_id, user_status) values (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssisss", $username, $hashed_password, $first_name, $last_name, $email, $phone_number, $role, $token_hash, $hospital_id, $status);

        try {
            $stmt->execute();
            $result = true;
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            $result = false;
            //Catch duplicate entries of unique keys
            if ($e->getCode() == 1062) {
                echo "<script>alert('Username, email, or phone number already in use.');</script>";
            } else {
                echo "<script>alert('Registration failed. Please try again.');</script>";
            }
        }

        // Send validation link to registered email
        if ($result) {
            $conn->commit();
            $mail = require "src/mailer.php";

            try {
                $mail->addAddress($email); //Send to
                $mail->Subject = "InfTraction Account Verification";

                // Create verification link
                $host = $_SERVER['HTTP_HOST'];  //Returns the Host header from the current request 
                $verify_link = "http://$host/verify.php?token=" . $verification_token;

                // Raw unhashed token goes into the link
                //verify.php will hash and compare the token ithat is stored n the database
                $mail->Body = "
                    <img src=\"cid:logo\" width=\"700\">
                    <p>Click the link to verify your account:<br>
                    <a href=\"$verify_link\">$verify_link</a></p>";

                // Send email
                $mail->send();
                echo "<script>alert('Registered! Check your email to verify your account before logging in.');
                window.location.href='index.php';</script>";
            } catch (Exception $e) {
                echo "Mailer error: {$mail->ErrorInfo}";
            }
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

                <label for="email"><img src="/Pictures/mail.icon-bgremoved.png" class="icon"></label>
                <input type="email" id="email" name="email" placeholder="Enter email">
                <span class="error_text" id="email_error"></span>
            </div>

            <!-- Middle column -->
            <div class="column">
                <label for="first_name"></label>
                <input type="text" id="first_name" name="first_name" placeholder="Enter first name">
                <span class="error_text" id="first_name_error"></span>
                
                <label for="last_name"></label>
                <input type="text" id="last_name" name="last_name" placeholder="Enter last name">
                <span class="error_text" id="last_name_error"></span>
                
                <label for="phone_number"><img src="/Pictures/phone.icon-bgremoved.png" class="icon"></label>
                <input type="text" id="phone_number" name="phone_number" placeholder="Enter phone number">
                <span class="error_text" id="phone_number_error"></span>                
                
            </div>
            
            <!-- Right column -->
            <div class="column">
                
                <label for="hospital_id"></label>
                <select id="hospital_id" name="hospital_id">
                    <option value="" disabled selected hidden>Choose a Hospital</option>
                    <?php while ($row = $hospital_result->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($row['hospital_id']) . "'>" . htmlspecialchars($row['hospital_name']) . "</option>";
                    } ?>
                </select>
                <span class="error_text" id="hospital_error"></span>

                <label for="role"></label>
                <select id="role" name="role">
                    <option value="" disabled selected hidden>Choose a Role</option>
                    <?php while ($row = $role_result->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($row['role_id']) . "'>" . htmlspecialchars($row['role_name']) . "</option>";
                    } ?>
                </select>
                <span class="error_text" id="role_error"></span>
                
                <input type="submit" value="Register" class="button">
            </div>
        </div>
    </form>

    <script src="js/register.js"></script>

</body>
</html>