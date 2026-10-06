<?php

// checks if the user has permission to enter this page
require "authenticate_check.php";
access('log_case');

date_default_timezone_set('Europe/Stockholm');

require_once 'config/db.php';

// Get departments for the dropdown
$sql = "SELECT department_id, department_name FROM Department";
$department_result = $conn->query($sql);

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

// Register the case when the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
$user_id = $_SESSION['user_id'];
$pathogen_id = $_POST['pathogen'];
$diagnosis_date = $_POST['diagnosis_date'];
$infection_date = $_POST['infection_date'];
$department_id = $_POST['ward'];
$patient_id = $_POST['patient_id'];
$severity_id = $_POST['severity'];
$comments = $_POST['comments'];
// Get the hospital of the logged-in user
$sql = "SELECT hospital_id FROM System_user WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$hospital_id = $user['hospital_id'];


// Insert the case into Infection_case
$sql = "INSERT INTO Infection_case
        (case_id, user_id, department_id, pathogen_id, severity_id,
         patient_id, case_diagnosis_date, case_infection_date, case_comments, hospital_id)
        VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
die("Error preparing case registration: " . $conn->error);
}

$stmt->bind_param(
"siiiissss",
$user_id,
$department_id,
$pathogen_id,
$severity_id,
$patient_id,
$diagnosis_date,
$infection_date,
$comments,
$hospital_id
);

if ($stmt->execute()) {
    require_once 'check_thresholds.php';

    checkThreshold(
    $conn,
    $pathogen_id,
    $diagnosis_date,
    $department_id,
    $hospital_id
    );

    echo "<script>alert('Case registered successfully!');</script>";
} else {
    echo "<script>alert('Error registering case: " . $stmt->error . "');</script>";
}

$stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
<title>InfecTracker</title>

<link rel="stylesheet" href="css/cases.css?v=4">

<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"
>
</head>

<body>

    <?php include 'hamburger_menu.php'; ?>
<div class="img">

<img src="Pictures/Logo.png" class="logo">
<img src="Pictures/virus_bl_nobg.png" class="virus_bl">
<img src="Pictures/virus_tr_nobg.png" class="virus_tr">
<img src="/Pictures/rings_br_nobg.png" class="rings_br">
</div>

<h1 class="hCase">Register a Case</h1>

<form action="" method="POST" class="inputs">

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
echo "<option value='" . $row['pathogen_id'] . "'>" . $row['pathogen_name'] . "</option>";
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
value="<?php echo date('Y-m-d'); ?>"
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
value="<?php echo date('Y-m-d'); ?>"
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
echo "<option value='" . $row['department_id'] . "'>" . $row['department_name'] . "</option>";
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

<input type="number" name="patient_id" id="patient-id" placeholder="Enter Patient ID" required>
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
echo "<option value='" . $row['severity_id'] . "'>" . ucfirst($row['severity_name']) . "</option>";
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
></textarea>

</div>
</div>


<button type="submit" class="submit-btn">
Submit
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