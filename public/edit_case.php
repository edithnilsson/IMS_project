<?php

require __DIR__ . "/../config/db.php";

if (!isset($_GET['id'])) die("Case ID is missing.");

$case_id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM infection_case WHERE case_id = ?");
$stmt->bind_param("s", $case_id);
$stmt->execute();
$case = $stmt->get_result()->fetch_assoc();

if (!$case) die("Case not found.");

$pathogens = $conn->query("SELECT * FROM pathogen");
$severities = $conn->query("SELECT * FROM severity_level");

if (isset($_POST['save'])) {

    $stmt = $conn->prepare(
        "UPDATE infection_case
         SET pathogen_id=?, severity_id=?, patient_id=?,
             case_diagnosis_date=?, case_infection_date=?
         WHERE case_id=?"
    );

    $stmt->bind_param(
        "iiisss",
        $_POST['pathogen_id'],
        $_POST['severity_id'],
        $_POST['patient_id'],
        $_POST['case_diagnosis_date'],
        $_POST['case_infection_date'],
        $case_id
    );

    if ($stmt->execute()) {
        header("Location: admin_edit.php");
        exit();
    }
}
?>

<h1>Edit Case</h1>

<link rel="stylesheet" href="/assets/css/edit_case.css">

<form method="POST">

    <label>Pathogen</label>
    <select name="pathogen_id">
        <?php while ($p = $pathogens->fetch_assoc()) { ?>
            <option value="<?= $p['pathogen_id'] ?>"
                <?= $p['pathogen_id'] == $case['pathogen_id'] ? 'selected' : '' ?>>
                <?= $p['pathogen_name'] ?>
            </option>
        <?php } ?>
    </select>

    <label>Severity</label>
    <select name="severity_id">
        <?php while ($s = $severities->fetch_assoc()) { ?>
            <option value="<?= $s['severity_id'] ?>"
                <?= $s['severity_id'] == $case['severity_id'] ? 'selected' : '' ?>>
                <?= $s['severity_name'] ?>
            </option>
        <?php } ?>
    </select>

    <label>Patient ID</label>
    <input type="number" name="patient_id"
           value="<?= $case['patient_id'] ?>">

    <label>Diagnosis Date</label>
    <input type="date" name="case_diagnosis_date"
           value="<?= $case['case_diagnosis_date'] ?>">

    <label>Infection Date</label>
    <input type="date" name="case_infection_date"
           value="<?= $case['case_infection_date'] ?>">

    <button name="save">Save Changes</button>

</form>