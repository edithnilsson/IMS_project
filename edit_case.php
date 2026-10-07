<?php

require "config/db.php";

if (!isset($_GET['id'])) {
    die("Case ID is missing.");
}

$case_id = $_GET['id'];

// Get the case
$sql = "SELECT * FROM infection_case WHERE case_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $case_id);
$stmt->execute();

$result = $stmt->get_result();
$case = $result->fetch_assoc();

if (!$case) {
    die("Case not found.");
}

$stmt->close();


// Save changes
if (isset($_POST['save'])) {

    $pathogen_id = $_POST['pathogen_id'];
    $severity_id = $_POST['severity_id'];
    $patient_id = $_POST['patient_id'];
    $diagnosis_date = $_POST['case_diagnosis_date'];
    $infection_date = $_POST['case_infection_date'];

    $sql = "UPDATE infection_case
            SET pathogen_id = ?,
                severity_id = ?,
                patient_id = ?,
                case_diagnosis_date = ?,
                case_infection_date = ?
            WHERE case_id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "iiisss",
        $pathogen_id,
        $severity_id,
        $patient_id,
        $diagnosis_date,
        $infection_date,
        $case_id
    );

    if ($stmt->execute()) {
        echo "Case updated successfully.";
    } else {
        echo "Error updating case.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Case</title>

    <style>

        body {
            font-family: Arial;
            margin: 40px;
        }

        form {
            width: 400px;
        }

        label {
            display: block;
            margin-top: 15px;
        }

        input {
            width: 100%;
            padding: 8px;
        }

        button {
            margin-top: 20px;
            padding: 10px 20px;
        }

    </style>
</head>

<body>

<h1>Edit Case</h1>

<form method="POST">

    <label>Pathogen ID</label>
    <input type="number" name="pathogen_id"
           value="<?php echo $case['pathogen_id']; ?>" required>

    <label>Severity ID</label>
    <input type="number" name="severity_id"
           value="<?php echo $case['severity_id']; ?>" required>

    <label>Patient ID</label>
    <input type="number" name="patient_id"
           value="<?php echo $case['patient_id']; ?>" required>

    <label>Diagnosis Date</label>
    <input type="date" name="case_diagnosis_date"
           value="<?php echo $case['case_diagnosis_date']; ?>" required>

    <label>Infection Date</label>
    <input type="date" name="case_infection_date"
           value="<?php echo $case['case_infection_date']; ?>" required>

    <button type="submit" name="save">Save Changes</button>

</form>

</body>

</html>