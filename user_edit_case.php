<?php

// checks if the user has permission to enter this page
require "authenticate_check.php";
access('Log a case');

date_default_timezone_set('Europe/Stockholm');

require_once 'config/db.php';

$user_id = $_SESSION['user_id'];

// Get the hospital of the logged-in doctor
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

// get the case_id from the edit button
if (isset($_POST['case_id'])) {
    $case_id = $_POST['case_id'];
} else {
    die("Case ID is missing.");
}


// Fetch the existing case, and verify that it belongs to this doctor
$sql = "SELECT * FROM Infection_case WHERE case_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $case_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();
$case = $result->fetch_assoc();

$stmt->close();

if (!$case) {
    die("Case not found, or you don't have permission to edit it.");
}

// Pre-fill form with the existing case
$pathogen_id = $case['pathogen_id'];
$department_id = $case['department_id'];
$severity_id = $case['severity_id'];
$patient_id = $case['patient_id'];
$diagnosis_date = $case['case_diagnosis_date'];
$infection_date = $case['case_infection_date'];
$comments = $case['case_comments'];

// Get departments for the dropdown
$sql = "SELECT department_id, department_name FROM Department WHERE hospital_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hospital_id);
$stmt->execute();
$department_result = $stmt->get_result();
$stmt->close();

if (!$department_result) {
    die("Error getting departments: " . $conn->error);
}

// Get pathogens for the dropdown
$sql = "SELECT pathogen_id, pathogen_name FROM Pathogen";
$pathogen_result = $conn->query($sql);

if (!$pathogen_result) {
die("Error getting pathogens: " . $conn->error);
}

// Get severity levels for the dropdown
$sql = "SELECT severity_id, severity_name FROM severity_level ORDER BY severity_id";
$severity_result = $conn->query($sql);

if (!$severity_result) {
die("Error getting severity levels: " . $conn->error);
}


// update case when the form is resubmitted 
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST['action'] ?? '') == 'update') {

    $user_id = $_SESSION['user_id'];
    $pathogen_id = $_POST['pathogen'];
    $diagnosis_date = $_POST['diagnosis_date'];
    $infection_date = $_POST['infection_date'];
    $department_id = $_POST['ward'];
    $patient_id = $_POST['patient_id'];
    $severity_id = $_POST['severity'];
    $comments = $_POST['comments'];


    $sql = "UPDATE Infection_case
    SET department_id = ?, pathogen_id = ?, severity_id = ?,
        patient_id = ?, case_diagnosis_date = ?, case_infection_date = ?, case_comments = ?
    WHERE case_id = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
    die("Error preparing case registration: " . $conn->error);
    }

    $stmt->bind_param(
        "iiiisssss",
        $department_id,
        $pathogen_id,
        $severity_id,
        $patient_id,
        $diagnosis_date,
        $infection_date,
        $comments,
        $case_id,
        $user_id
    );

// Handling of either the automatic warning or the dangerous case notif
if ($stmt->execute()) {

    // Check if the pathogen is dangerous
    $sql = "SELECT pathogen_dangerous
            FROM Pathogen
            WHERE pathogen_id = ?";

    $pathogen_stmt = $conn->prepare($sql);
    $pathogen_stmt->bind_param("i", $pathogen_id);
    $pathogen_stmt->execute();

    $result = $pathogen_stmt->get_result();
    $pathogen = $result->fetch_assoc();

    $pathogen_stmt->close();

    if ($pathogen && $pathogen['pathogen_dangerous']) {

        // If the pathogen is dangerous, deal with that:
        require_once 'handle_dangerous_case.php';

        $email_sent = handleDangerousCase(
            $conn,
            $pathogen_id,
            $diagnosis_date,
            $hospital_id
        );

        if ($email_sent) {
            echo "<script>alert('Dangerous case registered and notification emails sent.');</script>";
        } else {
            echo "<script>alert('Case registered, but some notification emails could not be sent.');</script>";
        }

    } else {

        // If the pathogen is not, check the automatic warning stuff
        require_once 'check_thresholds.php';

        checkThreshold(
            $conn,
            $pathogen_id,
            $diagnosis_date,
            $department_id,
            $hospital_id
        );

        echo "<script>alert('Case updated successfully!'); window.location.href='user_page.php';</script>";
    }

} else {
    echo "<script>alert('Error registering case: " . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8') . "');</script>";
}

$stmt->close();
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
<title>InfecTracker</title>

<link rel="stylesheet" href="/css/cases.css?v=4">

<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"
>
</head>

<body>

    <?php include 'hamburger_menu.php'; ?>
<div class="img">

<img src="/Pictures/Logo.png" class="logo">
<img src="/Pictures/virus_bl_nobg.png" class="virus_bl">
<img src="/Pictures/virus_tr_nobg.png" class="virus_tr">
<img src="/Pictures/rings_br_nobg.png" class="rings_br">
</div>

<h1 class="hCase">Edit a Case</h1>

<form action="" method="POST" class="inputs">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="case_id" value="<?= htmlspecialchars($case_id) ?>">


<div class="form-column">

<div class="form-row">

<label>
Pathogen
<img src="/Pictures/pathogen.icon_bgremoved.png" class="icon">
</label>

<div class="input">

<select name="pathogen" id="pathogen" required>

<option value="" disabled selected>
Select Pathogen
</option>

<?php
while ($row = $pathogen_result->fetch_assoc()) {
    $selected = ($row['pathogen_id'] == $pathogen_id) ? 'selected' : '';
    echo "<option value='" . $row['pathogen_id'] . "' $selected>" . htmlspecialchars($row['pathogen_name']) . "</option>";
}
?>

</select>
</div>
</div>


<div class="form-row">

<label>
Diagnosis Date
<img src="/Pictures/calender.icon-bgremoved.png" class="icon">
</label>

<div>
<input type="date" name="diagnosis_date" id="diagnosis-date" required
value="<?= htmlspecialchars($diagnosis_date) ?>"
max="<?php echo date('Y-m-d'); ?>"
>

</div>
</div>

<div class="form-row">

<label>
Infection Date
<img src="/Pictures/calender.icon-bgremoved.png" class="icon">
</label>

<div>
<input type="date" name="infection_date" id="infection-date" required
value="<?= htmlspecialchars($infection_date) ?>"
max="<?php echo date('Y-m-d'); ?>"
>
</div>

</div>

<div class="form-row">

<label>
Ward
<img src="/Pictures/ward.icon-bgremoved.png" class="icon">
</label>

<div>

<select name="ward" id="ward" required>

<option value="" disabled selected>
Select Ward
</option>

<?php
while ($row = $department_result->fetch_assoc()) {
    $selected = ($row['department_id'] == $department_id) ? 'selected' : '';
    echo "<option value='" . $row['department_id'] . "' $selected>" . htmlspecialchars($row['department_name']) . "</option>";
}
?>

</select>
</div>
</div>
</div>


<div class="form-column">

<div class="form-row">

<label>
Patient ID <img src="/Pictures/patient.icon-bgremoved.png" class="icon">
</label>

<input type="number" name="patient_id" id="patient-id" placeholder="Enter Patient ID" required
value="<?= htmlspecialchars($patient_id) ?>">
</div>

<div class="form-row">

<label>
Severity
</label>

<div> 

<select name="severity" id="severity" required>

<option value="" disabled selected>
Select Severity
</option>

<?php
while ($row = $severity_result->fetch_assoc()) {
    $selected = ($row['severity_id'] == $severity_id) ? 'selected' : '';
    echo "<option value='" . $row['severity_id'] . "' $selected>" . ucfirst($row['severity_name']) . "</option>";
}
?>
</select>
</div>
</div>

<div>

<label class="label-inputs"></label>

<div class="comments">

<textarea
name="comments"
placeholder="Other Comments..."
><?= htmlspecialchars($comments) ?></textarea>

</div>
</div>


<button type="submit" class="submit-btn">
Save
</button>
</div>
</form>

<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>

const pathogenSelect = new Choices('#pathogen', {
searchEnabled: true,
searchPlaceholderValue: 'Select pathogen',
itemSelectText: '',
searchChoices: true,
fuseOptions: {threshold: 0}
});


const departmentSelect = new Choices('#ward', {
searchEnabled: true,
searchPlaceholderValue: 'Select ward',
itemSelectText: '',
searchChoices: true,
fuseOptions: {threshold: 0}
});


const severitySelect = new Choices('#severity', {
searchEnabled: false,
itemSelectText: ''
});

</script>

<?php

$conn->close();

?>

</body>