
<?php
require __DIR__ . "/../../config/db.php";
require __DIR__ . "/../../src/authenticate_check.php";

// Only Admin can delete announcements
access('Admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {

    $mwarning_id = $_POST['id'];

    $sql = "DELETE FROM Manual_warning
            WHERE mwarning_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $mwarning_id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

header("Location: /admin/admin.php");
exit();
?>
