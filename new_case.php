<?php
$servername = "127.0.0.1"; /* Currently this works because it forces the connection with TCP and later picks the variable port*/
$username = "root"; 
$password = "root";
$dbname = "infection_tracking";
$port = 3333; /* This variable is not needed if you change servername for "localhost:3333" */ 

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
</head>
<body>
<div class="img">
    <img src="Pictures/Logo.png" class="logo">
    <img src="Pictures/virus_bl_nobg.png" class="virus_bl">
    <img src="Pictures/virus_tr_nobg.png" class="virus_tr">
    <img src="/Pictures/rings_br_nobg.png" class="rings_br">
</div>

<h1 class="hCase">Register a Case</h1>

<form action method="POST" class="inputs">
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
                    Date/Time   
                    <img src="/Pictures/calender.icon-bgremoved.png" class="icon">
            </label>

            <div>
                <input type="datetime-local">
            </div>
        </div>

        <div class="form-row">
            <label>
                    Ward     
                    <img src="/Pictures/ward.icon-bgremoved.png" class="icon">
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
                PatientID <img src="/Pictures/patient.icon-bgremoved.png" class="icon">
            </label>

            <input type="text" id="patient-id" placeholder="Enter Patient ID">
        </div>

        <div>
            <label class="label-inputs">
                    
            </label>
            <div class="comments">
                <input type="text" placeholder="Other Comments...">
            </div>
        </div>

        <button type="submit" class="submit-btn">Submit</button>


    </div>

</form>

</body>