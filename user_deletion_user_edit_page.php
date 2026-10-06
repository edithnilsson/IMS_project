<?php

session_start();

require "config/db.php";

$user_id = $_SESSION['user_id'];

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get the user's hospital
    $sql = "SELECT hospital_id
            FROM System_user
            WHERE user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();

    $hospital_id = $user['hospital_id'];

    // Check if there is already a pending request
    $sql = "SELECT request_id
            FROM Account_deletion_request
            WHERE user_id = ?
            AND request_status = 'pending'
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();


    if ($result->num_rows > 0) {

        $message = "You already have a pending account deletion request.";

    } else {

        // Create a new deletion request
        $sql = "INSERT INTO Account_deletion_request
                (user_id, hospital_id)
                VALUES (?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $user_id, $hospital_id);

        if ($stmt->execute()) {
            $message = "Your account deletion request has been submitted.";
        } else {
            $message = "There was a problem submitting your request.";
        }
    }

    $stmt->close();
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
</head>

<body>

<form method="POST"
      onsubmit="return confirm('Are you sure you want to request for your account to be deleted?');">

    <button type="submit">
        Request Account Deletion
    </button>

</form>

<?php
if ($message !== "") {echo "<p>" . htmlspecialchars($message) . "</p>";}
?>

</body>
</html>