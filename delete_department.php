<?php 

require __DIR__ . "/src/authenticate_check.php";
access('Admin');
require "config/db.php";

$user_id = $_SESSION['user_id'];

// Get the hospital of the logged-in admin
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


// get the department_id from the edit button
if (isset($_POST['department_id'])) {
    $department_id = $_POST['department_id'];
} else {
    die("Department ID is missing.");
}

$sql = "DELETE FROM Department
        WHERE department_id = ? AND hospital_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $department_id, $hospital_id);
$result = $stmt->execute();
$stmt->close();

// check if the ward was deleted or not
if ($result) {
    echo "<script>alert('Ward deleted!'); window.location.href='admin.php';</script>";
} else {
    echo "<script>alert('Error deleting ward.');</script>";
}

?> 