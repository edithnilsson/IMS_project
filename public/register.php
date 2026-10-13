<?php
/*
Register page will let a new user register its own account. 
password_hash() creates a new password hash with a random generated salt
ID and the hashed password will be stored in the database
*/
require __DIR__ . "/../config/db.php";

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
    } elseif (!isset($_POST['terms'])) {
        echo "<script>alert('Please accept the Terms of Service');</script>"; 
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
            $mail = require __DIR__ . "/../src/mailer.php";

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
                window.location.href='/index.php';</script>";
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
        <link rel="stylesheet" href="/assets/css/register.css">
</head>
<body>
    <!-- Logo and background -->
    <img src="/assets/img/logo_nobackground.png" class= "logo">
    <img src="/assets/img/virus_bl_nobg.png" class="virus_bl">

    <a href="/index.php" class="login-link">Already Registered? Click here to login</a>

    <!-- Register form --> 
    <form action="/register.php" method="POST" class="inputs">
        <div class="align_columns">
            
            <!-- Left column -->
            <div class="column">
                <label for="username"><img src="/assets/img/user.icon-bgremoved.png" class="icon"></label>
                <input type="text" id="username" name="username" placeholder="Enter username">
                <span class="error_text" id="username_error"></span>
                
                <label for="password"><img src="/assets/img/password.icon-bgremoved.png" class="icon"></label>
                <input type="password" id="password" name="password" placeholder="Enter password">
                <span class="error_text" id="password_error"></span>

                <label for="email"><img src="/assets/img/mail.icon-bgremoved.png" class="icon"></label>
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
                
                <label for="phone_number"><img src="/assets/img/phone.icon-bgremoved.png" class="icon"></label>
                <input type="text" id="phone_number" name="phone_number" placeholder="Enter phone number">
                <span class="error_text" id="phone_number_error"></span>                
                  
                <input type="submit" value="Register" class="button">
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

                <div class="terms">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the <button type="button" id="open_terms" class="terms_link">Terms of Service</button></label>
                </div>
            </div>
        </div>
    </form>

    <dialog id="terms_dialog">
        <h1>Terms of Service</h1>
        <h2>1. Information we store</h2>
        <p>When you register we store your username, name, email address, phone number, role and hospital. Your password is stored as a hash and cannot be read by us or anyone else. This information is used to run your account and is visible to administrators at your hospital.</p>
        <h2>2. Data entered by users</h2>
        <p>Doctors, epidemiologists and other users can enter cases, announcements and other data in InfTraction. Users are fully responsible for what they enter, including making sure it is correct, lawful and handled in line with their hospital's rules.</p>
        <h2>3. No liability</h2>
        <p>We take no liability for the content that users store or publish in the system, or for decisions made based on it. InfTraction is provided "as is", without any guarantee that it is accurate, always available or free of errors.</p>
        <h2>4. Your account</h2>
        <p>You are responsible for keeping your password secret and for everything that happens under your account. Administrators can edit or remove accounts. You must verify your email address before you can log in.</p>
        <h2>5. Changes</h2>
        <p>These terms may be updated from time to time. Continuing to use InfTraction means you accept the updated terms.</p>
        <div class="terms_buttons">
            <button type="button" id="close_terms">Close</button>
            <button type="button" id="agree_terms">I agree</button>
        </div>
    </dialog>

    <script src="/assets/js/register.js"></script>

</body>
</html>