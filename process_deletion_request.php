<?php

require "authenticate_check.php";

access('manage_user');

require "config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid request.");
}

$request_id = $_POST['request_id'];
$action = $_POST['action'];

$admin_id = $_SESSION['user_id'];

// Get the hospital of the logged-in admin
$sql = "SELECT hospital_id
        FROM System_user
        WHERE user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $admin_id);
$stmt->execute();

$result = $stmt->get_result();
$admin = $result->fetch_assoc();

$stmt->close();

$hospital_id = $admin['hospital_id'];

// Get the deletion request
$sql = "SELECT user_id
        FROM Account_deletion_request
        WHERE request_id = ?
        AND hospital_id = ?
        AND request_status = 'pending'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $request_id, $hospital_id);
$stmt->execute();

$result = $stmt->get_result();
$request = $result->fetch_assoc();

$stmt->close();

if (!$request) {
    exit("Deletion request not found.");
}

$requested_user_id = $request['user_id'];

// Approve deletion
if ($action === "approve") {

    $conn->begin_transaction();

    try {

        // Remove the user's account info
        $sql = "UPDATE System_user
                SET user_email = NULL,
                    user_phone_number = NULL,
                    user_password_hash = NULL,
                    user_status = 'deactivated'
                WHERE user_id = ?
                AND hospital_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $requested_user_id, $hospital_id);
        $stmt->execute();
        $stmt->close();


        // Mark the deletion request as approved
        $sql = "UPDATE Account_deletion_request
                SET request_status = 'approved',
                    reviewed_by = ?,
                    reviewed_at = CURRENT_TIMESTAMP
                WHERE request_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $admin_id, $request_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        header("Location: admin.php");
        exit();

    } catch (Exception $e) {

        $conn->rollback();
        exit("There was a problem approving the deletion request.");
    }
}

// Reject deletion
if ($action === "reject") {

    $sql = "UPDATE Account_deletion_request
            SET request_status = 'rejected',
                reviewed_by = ?,
                reviewed_at = CURRENT_TIMESTAMP
            WHERE request_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $admin_id, $request_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin.php");
    exit();
}

exit("Invalid action.");
?>