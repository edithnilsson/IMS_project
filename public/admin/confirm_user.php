<?php
require __DIR__ . "/../../src/authenticate_check.php";
access('Admin');

require __DIR__ . "/../../config/db.php";

if (isset($_POST['id']) && isset($_POST['action'])) {

    $user_id = $_POST['id'];
    $action = $_POST['action'];

    if ($action == "confirm") {
        $sql = "UPDATE System_user
                SET user_status = 'active'
                WHERE user_id = ? AND user_status = 'pending'";
    } else if ($action == "dismiss") {
        $sql = "DELETE FROM System_user
                WHERE user_id = ? AND user_status = 'pending'";
    }

    if (isset($sql)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();

header("Location: /admin/admin.php");
exit();
?>