<?php
require "config/db.php";   

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Load dropdown data -------------------------------------------------
$pathogens = [];
$res = $conn->query("SELECT pathogen_id, pathogen_name FROM Pathogen ORDER BY pathogen_name");
while ($row = $res->fetch_assoc()) $pathogens[] = $row;

$departments = [];
$res = $conn->query("SELECT department_id, department_name FROM Department ORDER BY department_name");
while ($row = $res->fetch_assoc()) $departments[] = $row;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>InfecTracker</title>

    <link rel="stylesheet" href="css/cases.css">

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
                <select id="pathogen" name="pathogen" required>
                    <option value="" disabled selected>Select Pathogen</option>
                    <?php foreach ($pathogens as $p): ?>
                        <option value="<?= (int)$p['pathogen_id'] ?>">
                            <?= htmlspecialchars($p['pathogen_name']) ?>
                        </option>
                    <?php endforeach; ?>
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
                <select id="ward" name="ward" required>
                    <option value="" disabled selected>Select Ward</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= (int)$d['department_id'] ?>">
                            <?= htmlspecialchars($d['department_name']) ?>
                        </option>
                    <?php endforeach; ?>
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
</html>