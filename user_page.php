<?php

require "authenticate_check.php";

require_once 'config/db.php';

$user_id = $_SESSION['user_id'];

$message = "";

// Get the hospital and other information of the logged-in doctor
$sql = "SELECT s.hospital_id, s.user_username, s.user_name, s.user_surname, s.user_email, s.user_phone_number, s.role_id, h.hospital_name, r.role_name
        FROM System_user s
        JOIN Hospital h ON s.hospital_id = h.hospital_id
        JOIN Roles r ON s.role_id = r.role_id
        WHERE user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

$hospital_id = $user['hospital_id'];


// Get all cases/ announcements of the user


$cases = []; 
$announcements = [];
if ($user['role_name'] === 'Doctor') {
    $sql = "SELECT inf.*, patho.pathogen_name, s.severity_name, d.department_name
    FROM Infection_case inf
    JOIN Pathogen patho ON inf.pathogen_id = patho.pathogen_id
    JOIN Severity_level s ON inf.severity_id = s.severity_id
    JOIN Department d ON inf.department_id = d.department_id
    WHERE inf.user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $cases[] = $row;
    }

    $stmt->close();

}

elseif ($user['role_name']=== 'Epidemiologist') {
    $sql="SELECT mw.*, p.pathogen_name, d.department_name
    FROM Manual_warning mw
    JOIN Pathogen p ON mw.pathogen_id = p.pathogen_id
    JOIN Department d ON mw.department_id = d.department_id
    WHERE mw.user_id= ?
    ORDER BY mwarning_date DESC ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    $stmt->close();

    }

// Request to delete user

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
    <meta name="viewport" content="width=, initial-scale=1.0">

    <link rel="stylesheet" href="css/user_page.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0">< </script>

    <title>Document</title>


</head>
<body>
    
    <div class = "container">
    <?php include 'navigation_bar.php'; ?>
        <h1><?= htmlspecialchars($user['user_name'] . " " . $user['user_surname']) ?></h1>
        <div class = "profile_info">

            <div class = "form-row" >
                <h3>User name:</h3>
                <?= htmlspecialchars($user['user_username']) ?>

                <h3>Phone number:</h3>
                <?= htmlspecialchars($user['user_phone_number']) ?>

                <h3>Email:</h3>

                <?= htmlspecialchars($user['user_email']) ?>

            </div>

            <div class = "form-row" >

                <h3>Role:</h3>
                    <?= htmlspecialchars($user['role_name']) ?>

                <h3>Hospital:</h3>
                    <?= htmlspecialchars($user['hospital_name']) ?>
            </div>
        </div>
    


        <?php if ($user['role_name'] === 'Doctor'): ?>
            <h2>Cases</h2>
        
        <?php elseif ($user['role_name'] === 'Epidemiologist'): ?>
            <h2>Announcements</h2>
        <?php endif; ?>


        <table class="user-table">
            <thead>
            <tr>
                <?php if ($user['role_name'] === 'Doctor'): ?>
                    <th>Date of diagnosis</th>
                    <th>Date of infection</th>
                    <th>Pathogen</th>
                    <th>Ward</th>
                    <th>Severity</th>
                    <th>Comments</th>
                    <th>Edit</th>

                <?php elseif ($user['role_name'] === 'Epidemiologist'): ?>
                    <th>Date</th>
                    <th>Message</th>
                    <th>Pathogen</th>
                    <th>Ward</th>
                    <th>Edit</th>

                <?php endif; ?>
            </tr>
            </thead>

            <tbody>
                <?php if ($user['role_name'] === 'Doctor'): ?>
                    <?php foreach ($cases as $case): ?>
                        <tr>
                            <td><?= htmlspecialchars($case['case_diagnosis_date']) ?></td>
                            <td><?= htmlspecialchars($case['case_infection_date']) ?></td>
                            <td><?= htmlspecialchars($case['pathogen_name']) ?></td>
                            <td><?= htmlspecialchars($case['department_name']) ?></td>
                            <td><?= htmlspecialchars($case['severity_name']) ?></td>
                            <td><?= htmlspecialchars($case['case_comments']) ?></td>
                            <td>
                                <form action="user_edit_case.php" method="POST">
                                    <input type="hidden"
                                        name="case_id"
                                        value="<?= htmlspecialchars($case['case_id']) ?>">
                                    <button type="submit">Edit</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php elseif ($user['role_name'] === 'Epidemiologist'): ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <tr>
                            <td><?= htmlspecialchars($announcement['mwarning_date']) ?></td>
                            <td><?= htmlspecialchars($announcement['mwarning_message']) ?></td>
                            <td><?= htmlspecialchars($announcement['pathogen_name']) ?></td>
                            <td><?= htmlspecialchars($announcement['department_name']) ?></td>
                            <td>
                                <form action="edit_announcement.php" method="POST">
                                    <input type="hidden"
                                        name="mwarning_id"
                                        value="<?= htmlspecialchars($announcement['mwarning_id']) ?>">
                                    <button type="submit">Edit</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>

        </table>
    </div>
    
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