<?php

// checks if the user has permission to enter this page
require __DIR__ . "/../src/authenticate_check.php";
access('Write an announcement');

date_default_timezone_set('Europe/Stockholm');

require_once 'config/db.php';


$user_id = $_SESSION['user_id'];

// Get the hospital of the logged-in epidemiologist 
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


// get the mwarning_id from the edit button
if (isset($_POST['mwarning_id'])) {
    $mwarning_id = $_POST['mwarning_id'];
} else {
    die("Announcement ID is missing.");
}

// get the message etc from database - mwarning_id and user_id must overlap
$sql = "SELECT * 
        FROM Manual_warning 
        WHERE mwarning_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $mwarning_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$announcement = $result->fetch_assoc();
$stmt->close();

// Pre-fill form with the existing announcement
$message = $announcement['mwarning_message'];
$pathogen_id = $announcement['pathogen_id'];
$department_id = $announcement['department_id'];
$severity = $announcement['mwarning_severity_level'];
$icon = $announcement['mwarning_icon'];
$warning_date = date('Y-m-d', strtotime($announcement['mwarning_date']));


// Update announcement when the form is resubmitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST['action'] ?? '') == 'update') {

    $pathogen_id = $_POST['pathogen'];
    $department_id = $_POST['department'];
    $severity = $_POST['severity'];
    $warning_date = $_POST['warning_date'] . ' ' . date('H:i:s');
    $message = $_POST['message'];
    $icon = $_POST['icon'];

    $sql = "UPDATE Manual_warning
            SET pathogen_id = ?, department_id = ?, mwarning_severity_level = ?,
                mwarning_date = ?, mwarning_message = ?, mwarning_icon = ?
            WHERE mwarning_id = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "iiississ",
        $pathogen_id,
        $department_id,
        $severity,
        $warning_date,
        $message,
        $icon,
        $mwarning_id,
        $user_id
    );

    $result = $stmt->execute();
    $stmt->close();

    if ($result) {
        echo "<script>alert('Announcement updated successfully!'); window.location.href='user_page.php';</script>";
    } else {
        echo "<script>alert('Error updating announcement.');</script>";
    }
}




//Get departments for the dropdown

$sql = "SELECT department_id, department_name FROM Department WHERE hospital_id = ?";
$stmt= $conn->prepare($sql);
$stmt->bind_param("s", $hospital_id);
$stmt->execute();
$dept_result = $stmt->get_result();
$stmt->close();


if (!$dept_result) {
    die("Error getting departments: " . mysqli_error($conn));
}


//Get pathogens for the dropdown

$sql = "SELECT pathogen_id, pathogen_name FROM Pathogen";
$pathogen_result = mysqli_query($conn, $sql);


if (!$pathogen_result) {
    die("Error getting pathogens: " . mysqli_error($conn));
}
?>



<!DOCTYPE html>

<html>

<head>

    <title>Create Warning</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <link rel="stylesheet" href="/assets/css/announcements.css">

</head>

<body>


    <img src="/assets/img/virus_tr_nobg.png" class="virus_tr">
    <img src="/assets/img/rings_br_nobg.png" class="rings_br">
    
    <div class="page">
    <?php include __DIR__ . "/../src/navigation_bar.php"; ?>
        <div class="logo">
            <a href="homepage.php">
                <img src="/assets/img/logo_nobackground.png" alt="Logo">
            </a>
        </div>

        <form method="POST" action="">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="mwarning_id" value="<?= htmlspecialchars($mwarning_id) ?>">

            <!-- ANNOUNCEMENT AREA -->

            <div class="message">

                <h3>Edit announcement</h3>

                <textarea
                    name="message"
                    placeholder="Write the announcement here..."
                    required
                ><?= htmlspecialchars($message) ?></textarea>

            </div>

            <!-- THREE SECTIONS -->

            <div class="three-sections">

                <!-- FIRST SECTION (PATHOGEN, WARD AND DATE) -->

                <div class="left">

                    <div class="input-row">

                        <h3 class="head-row">Pathogen<img src="/assets/img/pathogen.icon_bgremoved.png" class="head-icon"></h3>

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

                    <div class="input-row">

                        <h3 class="head-row">Ward<img src="/assets/img/ward.icon-bgremoved.png" class="head-icon"></h3>

                        <select name="department" id="department" required>

                            <option value="" hidden>
                                Select ward
                            </option>

                            <?php
                            while ($row = mysqli_fetch_assoc($dept_result)) {
                                $selected = ($row['department_id'] == $department_id) ? 'selected' : '';
                                echo "<option value='" . $row['department_id'] . "' $selected>" . htmlspecialchars($row['department_name']) . "</option>";
                            }
                            ?>

                        </select>

                    </div>

                    <div class="input-row">

                        <h3 class="head-row">Date<img src="/assets/img/calender.icon-bgremoved.png" class="head-icon"></h3>

                        <input
                            type="date"
                            name="warning_date"
                            value="<?= htmlspecialchars($warning_date) ?>"
                            max="<?= date('Y-m-d') ?>"
                            required
                        >

                    </div>

                </div>

                <!-- SECOND SECTION (THREAT AND ICONS) -->

                <div class="right">

                    <div class="input-row">

                        <h3 class="head-row">Threat level</h3>

                        <div class="threat-level">

                            <label>

                                <input
                                    type="radio"
                                    name="severity"
                                    value="1"
                                    <?= ($severity == 1) ? 'checked' : '' ?>
                                    required
                                >

                                <span class="bubble green"></span>

                            </label>

                            <label>

                                <input
                                    type="radio"
                                    name="severity"
                                    value="2"
                                    <?= ($severity == 2) ? 'checked' : '' ?>
                                >

                                <span class="bubble yellow"></span>

                            </label>

                            <label>

                                <input
                                    type="radio"
                                    name="severity"
                                    value="3"
                                    <?= ($severity == 3) ? 'checked' : '' ?>
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
                                    <?= ($icon == 0) ? 'checked' : '' ?>
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
                                    <?= ($icon == 1) ? 'checked' : '' ?>
                                >

                                <span class="icon">
                                    <img src="/assets/img/mask_icon_bgremoved.png">
                                </span>

                            </label>


                            <label>

                                <input
                                    type="radio"
                                    name="icon"
                                    value="2"
                                    <?= ($icon == 2) ? 'checked' : '' ?>
                                >

                                <span class="icon">
                                    <img src="/assets/img/hands_icon_removebg.png">
                                </span>

                            </label>


                            <label>

                                <input
                                    type="radio"
                                    name="icon"
                                    value="3"
                                    <?= ($icon == 3) ? 'checked' : '' ?>
                                >

                                <span class="icon">
                                    <img src="/assets/img/warning_icon_removebg.png">
                                </span>

                            </label>


                            <label>

                                <input
                                    type="radio"
                                    name="icon"
                                    value="4"
                                    <?= ($icon == 4) ? 'checked' : '' ?>
                                >

                                <span class="icon">
                                    <img src="/assets/img/needle_icon_removebg.png">
                                </span>

                            </label>


                            <label>

                                <input
                                    type="radio"
                                    name="icon"
                                    value="5"
                                    <?= ($icon == 6) ? 'checked' : '' ?>
                                >

                                <span class="icon">
                                    <img src="/assets/img/house_icon_removebg.png">
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- THIRD SECTION (BUTTON) -->

                <div class="button-section">
                    <button type="submit">
                        Save
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

mysqli_close($conn);

?>