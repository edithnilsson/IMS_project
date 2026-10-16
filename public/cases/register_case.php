<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// checks if the user has permission to enter this page
require __DIR__ . "/../../src/authenticate_check.php";
access('Register Case');

date_default_timezone_set('Europe/Stockholm');

require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../src/patient_id_encryption.php";
include __DIR__ . "/../../src/navigation_bar.php";

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


// Get departments for the dropdown
$sql = "SELECT department_id, department_name 
        FROM Department 
        WHERE hospital_id = ?
        AND department_status = 'active'";
        
$stmt= $conn->prepare($sql);
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

// Register the case when the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'];
    $pathogen_id = (int) $_POST['pathogen'];
    $diagnosis_date = $_POST['diagnosis_date'];
    $infection_date = $_POST['infection_date'];
    $department_id = (int) $_POST['ward'];
    $severity_id = (int) $_POST['severity'];
    $comments = $_POST['comments'] ?? '';

    // Get and validate the hospital patient ID
    $hospital_patient_id = trim($_POST['patient_id'] ?? '');

    if ($hospital_patient_id === '' || !ctype_digit($hospital_patient_id)) {
        die("Invalid patient ID.");
    }

    $conn->begin_transaction();

    try {
        // Generate an HMAC for this patient across hospitals
        $patient_id_hmac = patientIdHmac($hospital_patient_id);

        // Check whether this patient already has an internal ID
        $sql = "SELECT patient_id
                FROM Patient_ID_Mapping
                WHERE patient_id_hmac = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $patient_id_hmac);
        $stmt->execute();

        $result = $stmt->get_result();
        $mapping = $result->fetch_assoc();
        $stmt->close();

        if ($mapping) {
            // If they do, reuse the existing internal patient ID
            $patient_id = $mapping['patient_id'];
        } else {
            // If not, create a new internal patient record and encrypt the original patient ID
            //internal record:
            $sql = "SELECT UUID() AS patient_id";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            $patient_id = $row['patient_id'];

            $sql = "INSERT INTO Patient (patient_id) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $patient_id);
            $stmt->execute();
            $stmt->close();

            // encryption of the original hospital patient ID
            $encrypted = encryptPatientId($hospital_patient_id);

            // Store the patient mapping (HMAC and encrypted identifier)
            $sql = "INSERT INTO Patient_ID_Mapping (patient_id, patient_id_hmac, encrypted_patient_id, nonce, auth_tag)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssss",
                $patient_id,
                $patient_id_hmac,
                $encrypted['ciphertext'],
                $encrypted['nonce'],
                $encrypted['auth_tag']
            );
            $stmt->execute();
            $stmt->close();
        }

        // Register the infection case
        $sql = "INSERT INTO Infection_case
                (case_id, user_id, department_id, pathogen_id, severity_id,
                 patient_id, case_diagnosis_date, case_infection_date,
                 case_comments, hospital_id)
                VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "siiisssss",
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
        $stmt->execute();
        $stmt->close();

        // Check if the pathogen is dangerous
        $sql = "SELECT pathogen_dangerous
                FROM Pathogen
                WHERE pathogen_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $pathogen_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $pathogen = $result->fetch_assoc();
        $stmt->close();

        if (!$pathogen) {
            throw new RuntimeException("Pathogen not found.");
        }

        // Run the appropriate checks before committing
        if ($pathogen['pathogen_dangerous']) {
            require_once __DIR__ . "/../../src/handle_dangerous_case.php";

            $notification_result = handleDangerousCase(
                $conn,
                $pathogen_id,
                $diagnosis_date,
                $hospital_id
            );

            if ($notification_result === false) {
                throw new RuntimeException("Dangerous-case notification failed.");
            }
        } else {
            require_once __DIR__ . "/../../src/check_thresholds.php";

            checkThreshold(
                $conn,
                $pathogen_id,
                $diagnosis_date,
                $department_id,
                $hospital_id
            );
        }

        // Save everything only if all steps succeed
        $conn->commit();

        //Success messages
        if ($pathogen['pathogen_dangerous']) {
            echo "<script>alert('Case registered successfully. Notification emails were sent.');</script>";
        } else {
            echo "<script>alert('Case registered successfully.');</script>";
        }

    } catch (Throwable $e) {
        // Undo all transactional database changes if something fails
        $conn->rollback();

        if (isset($stmt) && $stmt instanceof mysqli_stmt) {
            $stmt->close();
        }

        error_log($e->getMessage());
        echo "<script>alert('Could not register the case. Please try again.');</script>";
        exit;
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
<title>InfecTracker</title>

<link rel="stylesheet" href="/assets/css/cases.css?v=753">

<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"
>
</head>

<body>
<img src="/assets/img/virus_tr_nobg.png" class="virus_tr">
<img src="/assets/img/rings_br_nobg.png" class="rings_br">

<h1 class="hCase">Register Case</h1>

<form action="" method="POST" class="inputs">

<div class="form-column">

<div class="form-row">

<label>
Pathogen
<img src="/assets/img/pathogen.icon_bgremoved.png" class="icon">
</label>

<div class="input">

<select name="pathogen" id="pathogen" required>

<option value="" hidden>
    Select pathogen
</option>
    <?php
    while ($row = mysqli_fetch_assoc($pathogen_result)) {
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
<img src="/assets/img/calender.icon-bgremoved.png" class="icon">
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
<img src="/assets/img/calender.icon-bgremoved.png" class="icon">
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
<img src="/assets/img/ward.icon-bgremoved.png" class="icon">
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
Patient ID <img src="/assets/img/patient.icon-bgremoved.png" class="icon">
</label>

<input type="text" inputmode="numeric" name="patient_id" id="patient-id" placeholder="Enter Patient ID" required>
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