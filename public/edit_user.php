<?php

require __DIR__ . "/../src/authenticate_check.php";
access('Admin');
require __DIR__ . "/../config/db.php";

// user transfer from the edit button
$user_id = $_POST['user_id'];

// No user selected (went to this page directly), send back to admin page
if (empty($user_id)) {
    echo "<script>alert('No user selected.'); window.location.href='admin.php';</script>";
    exit;
}

// If "submit" button is pressed go in here, otherwise skip this if()
// Updates user information
if (isset($_POST['save'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $role_id = $_POST['role_id'];
    $hospital_id = $_POST['hospital_id'];
 
    $email_format_valid = filter_var($email, FILTER_VALIDATE_EMAIL);
    $phone_number_format_valid = preg_match("/^[0-9+ -]+$/", $phone_number);

    if (empty($first_name)) {
        echo "<script>alert('First name can not be empty'); window.history.back();</script>";
    } elseif (empty($last_name)) {
        echo "<script>alert('Last name can not be empty'); window.history.back();</script>";
    } elseif (empty($email)) {
        echo "<script>alert('Email can not be empty'); window.history.back();</script>";
    } elseif (!$email_format_valid) {
        echo "<script>alert('Please enter a valid email address'); window.location.href='admin.php';</script>";
    } elseif (empty($phone_number)) {
        echo "<script>alert('Phone number can not be empty'); window.history.back();</script>";
    } elseif (!$phone_number_format_valid) {
        echo "<script>alert('Phone number can only contain numbers'); window.location.href='admin.php';</script>";
    } elseif (empty($role_id)) {
        echo "<script>alert('Please pick a role'); window.history.back();</script>";
    } elseif (empty($hospital_id)) {
        echo "<script>alert('Please pick a hospital'); window.history.back();</script>";
    } else {
        $sql = "UPDATE System_user
                SET user_name = ?, user_surname = ?, user_email = ?, user_phone_number = ?, role_id = ?, hospital_id = ?
                WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssiss", $first_name, $last_name, $email, $phone_number, $role_id, $hospital_id, $user_id);
        try {
            $stmt->execute();
            echo "<script>alert('User updated.'); window.location.href='admin.php';</script>";
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                echo "<script>alert('Email is already in use by another user.'); window.location.href='admin.php';</script>";
            } else {
                echo "<script>alert('Update failed. Please try again.'); window.location.href='admin.php';</script>";
            }
        }
    }
    exit();
}

// Fetch information about the user using user_id
$stmt = $conn->prepare("SELECT user_id, user_username, user_name, user_surname, user_email, user_phone_number, role_id, hospital_id
                         FROM System_user WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$target_user = $stmt->get_result()->fetch_assoc();

//Check if user exists
if (!$target_user) {
    echo "<script>alert('User not found.'); window.location.href='admin.php';</script>";
    exit;
}

// Get all roles for the dropdown, with the user's current role pre-selected
$sql = "SELECT role_id, role_name FROM Roles ORDER BY role_name";
$role_result = $conn->query($sql);
 
// Get all hospitals for the dropdown, with the user's current hospital pre-selected
$sql = "SELECT hospital_id, hospital_name FROM Hospital ORDER BY hospital_name";
$hospital_result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit User</title>
        <link rel="stylesheet" href="/assets/css/edit_user.css">
    </head>
    <body>
        <!-- Logo and background -->
        <?php include __DIR__ . "/../src/navigation_bar.php"; ?>
        <h1 class="title">Edit User</h1>
        <img src="/assets/img/Logo.png" class="logo">
        <img src="/assets/img/virus_tr_nobg.png" class="virus_tr">
        <img src="/assets/img/virus_bl_nobg.png" class="virus_bl">
        <img src="/assets/img/rings_br_nobg.png" class="rings_br">
        <form action="edit_user.php" method="POST" class="inputs">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($target_user['user_id']) ?>">
        
            <label for="username">Username</label>
            <input type="text" id="username" value="<?= htmlspecialchars($target_user['user_username']) ?>" disabled>
        
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($target_user['user_name']) ?>" required>
            <span class="error_text" id="first_name_error"></span>
            
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($target_user['user_surname']) ?>" required>
            <span class="error_text" id="last_name_error"></span>
            
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($target_user['user_email']) ?>" required>
            <span class="error_text" id="email_error"></span>

            <label for="phone_number">Phone Number</label>
            <input type="text" id="phone_number" name="phone_number" value="<?= htmlspecialchars($target_user['user_phone_number']) ?>" required>
            <span class="error_text" id="phone_number_error"></span> 
            
            <label for="role">Role</label>
            <select id="role" name="role_id">
                <?php while ($row = $role_result->fetch_assoc()) { ?>
                    <option value="<?= htmlspecialchars($row['role_id']) ?>"
                        <?= $row['role_id'] == $target_user['role_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['role_name']) ?>
                    </option>
                <?php } ?>
            </select>
        
            <label for="hospital_id">Hospital</label>
            <select id="hospital_id" name="hospital_id">
                <?php while ($row = $hospital_result->fetch_assoc()) { ?>
                    <option value="<?= htmlspecialchars($row['hospital_id']) ?>"
                        <?= $row['hospital_id'] == $target_user['hospital_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['hospital_name']) ?>
                    </option>
                <?php } ?>
            </select>

            <div class="form_buttons">
                <a href="admin.php" class="button cancel_button">Cancel</a>
                <button type="submit" name="save" value="1" class="button">Save Changes</button>
            </div>
        </form>
        <script src="/assets/js/edit_user.js"></script>
    </body>
</html>