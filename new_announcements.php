<?php
// checks if the user has permission to enter this page
require "authenticate_check.php";
access(['epidemiologist', 'admin']);

date_default_timezone_set('Europe/Stockholm');

require_once 'config/db.php';


//only process the submission after the form has been submitted (if) and then insert it into the database

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Fetch data from POST request
    $user_id = $_SESSION['user_id'];
    $pathogen_id = $_POST['pathogen'];
    $department_id = $_POST['department'];
    $severity = $_POST['severity'];
    $warning_date = $_POST['warning_date'];
    // add timestamp 
    $warning_date = $warning_date . ' ' . date('H:i:s'); 

    $message = $_POST['message'];
    $icon = $_POST['icon'];

    $sql = "INSERT INTO Manual_warning (mwarning_id, user_id, pathogen_id, department_id, mwarning_severity_level, mwarning_date, mwarning_message, mwarning_icon)
    VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $link->prepare($sql);

    $stmt->bind_param("siiissi", $user_id, $pathogen_id, $department_id, $severity, $warning_date, $message, $icon);

    $result = $stmt->execute();

    if ($result) {
        echo "<script>alert('Warning created successfully!');</script>";
    } else {
        echo "<script>alert('Error creating warning: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}


//Get departments for the dropdown

$sql = "SELECT department_id, department_name FROM Department";
$dept_result = mysqli_query($link, $sql);

if (!$dept_result) {
    die("Error getting departments: " . mysqli_error($link));
}



//Get pathogens for the dropdown

$sql = "SELECT pathogen_id, pathogen_name FROM Pathogen";
$pathogen_result = mysqli_query($link, $sql);

if (!$pathogen_result) {
    die("Error getting pathogens: " . mysqli_error($link));
}
?>

<!DOCTYPE html>

<html>

<head>

    <title>Create Warning</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <link rel="stylesheet" href="css/announcements.css">

</head>

<body>

    <img class="bottom-decoration" src="Pictures/rings_br_nobg.png" alt="">
    <img class="top-decoration" src="Pictures/virus_tr_nobg.png" alt="">

    <div class="page">
        <div class="logo">
            <a href="homepage.php">
                <img src="Pictures/Logo.png" alt="Logo">
            </a>
        </div>

        <form method="POST" action="">

            <!-- ANNOUNCEMENT AREA -->

            <div class="message">

                <h3>Write announcement</h3>

                <textarea
                    name="message"
                    placeholder="Write the announcement here..."
                    required
                ></textarea>

            </div>

            <!-- THREE SECTIONS -->

            <div class="three-sections">

                <!-- FIRST SECTION (PATHOGEN, WARD AND DATE) -->

                <div class="left">

                    <div class="input-row">

                        <h3>Pathogen</h3>

                        <select name="pathogen" id="pathogen" required>

                            <option value="" hidden>
                                Select pathogen
                            </option>

                            <?php
                            while ($row = mysqli_fetch_assoc($pathogen_result)) {
                                echo "<option value='" . $row['pathogen_id'] . "'>" . $row['pathogen_name'] . "</option>";
                            }
                            ?>

                        </select>

                    </div>

                    <div class="input-row">

                        <h3>Ward</h3>

                        <select name="department" id="department" required>

                            <option value="" hidden>
                                Select ward
                            </option>

                            <?php
                            while ($row = mysqli_fetch_assoc($dept_result)) {
                                echo "<option value='" . $row['department_id'] . "'>" . $row['department_name'] . "</option>";
                            }
                            ?>

                        </select>

                    </div>

                    <div class="input-row">

                        <h3>Date</h3>

                        <input
                            type="date"
                            name="warning_date"
                            value="<?php echo date('Y-m-d'); ?>"
                            max="<?php echo date('Y-m-d'); ?>"
                            required
                        >

                    </div>

                </div>

                <!-- SECOND SECTION (THREAT AND ICONS) -->

                <div class="right">

                    <div class="input-row">

                        <h3>Threat level</h3>

                        <div class="threat-level">

                            <label>

                                <input
                                    type="radio"
                                    name="severity"
                                    value="1"
                                    required
                                >

                                <span class="bubble green"></span>

                            </label>

                            <label>

                                <input
                                    type="radio"
                                    name="severity"
                                    value="2"
                                    >

                                <span class="bubble yellow"></span>

                            </label>

                            <label>

                                <input
                                    type="radio"
                                    name="severity"
                                    value="3"
                                >

                                <span class="bubble red"></span>

                            </label>
                        </div>
                    </div>


                    <div class="input-row">

                        <h3>Icon</h3>

                        <div class="icons">

                            <label>

                                <input
                                    type="radio"
                                    name="icon"
                                    value="0"
                                    checked
                                >

                                <span class="icon">
                                    None
                                </span>

                            </label>


                            <label>

                                <input
                                    type="radio"
                                    name="icon"
                                    value="1"
                                >

                                <span class="icon">
                                    <img src="Pictures/icon1.png">
                                </span>

                            </label>


                            <label>

                                <input
                                    type="radio"
                                    name="icon"
                                    value="2"
                                >

                                <span class="icon">
                                    <img src="Pictures/icon2.png">
                                </span>

                            </label>


                            <label>

                                <input
                                    type="radio"
                                    name="icon"
                                    value="3"
                                >

                                <span class="icon">
                                    <img src="Pictures/icon3.png">
                                </span>

                            </label>


                            <label>

                                <input
                                    type="radio"
                                    name="icon"
                                    value="4"
                                >

                                <span class="icon">
                                    <img src="Pictures/icon4.png">
                                </span>

                            </label>


                            <label>

                                <input
                                    type="radio"
                                    name="icon"
                                    value="5"
                                >

                                <span class="icon">
                                    <img src="Pictures/icon5.png">
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- THIRD SECTION (BUTTON) -->

                <div class="button-section">
                    <button type="submit">
                        Submit
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- choices.js script for the searchable dropdown of pathogen and ward -->

    <!-- https://github.com/Choices-js/Choices#setup -->

    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <script>

        const pathogenSelect = new Choices('#pathogen', {
            searchEnabled: true,
            searchPlaceholderValue: 'Select pathogen',
            itemSelectText: '',
            searchChoices: true,
            fuseOptions: {threshold: 0}
        });

        const departmentSelect = new Choices('#department', {
            searchEnabled: true,
            searchPlaceholderValue: 'Select ward',
            itemSelectText: '',
            searchChoices: true,
            fuseOptions: {threshold: 0}
        });

    </script>

</body>
</html>

<?php

mysqli_close($link);

?>