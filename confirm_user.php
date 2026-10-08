<?php

require "config/db.php";

if (isset($_GET['id'])) {

    $user_id = $_GET['id'];

    $sql = "UPDATE System_user
            SET user_status = 'active'
            WHERE user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);

    $stmt->execute();

    $stmt->close();
}

$conn->close();


header("Location: admin.php");
exit();

?>