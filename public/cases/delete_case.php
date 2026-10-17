
<?php
require __DIR__ . "/../../src/authenticate_check.php";
require __DIR__ . "/../../config/db.php";

access('Admin');   //only admin can delete the cases 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['case_id'])) {

    $case_id = $_POST['case_id'];

    // Get the hospital of the logged-in Admin
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT hospital_id FROM System_user WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user) {
        $hospital_id = $user['hospital_id'];

        $sql = "DELETE FROM Infection_case
                WHERE case_id = ? AND hospital_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $case_id, $hospital_id);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();

header("Location: /account/profile.php");
exit();
?>
