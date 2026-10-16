<?php

// checks if the user has permission to enter this page
require __DIR__ . "/../../src/authenticate_check.php";
access('Write Announcement');

date_default_timezone_set('Europe/Stockholm');

include __DIR__ . "/../../src/navigation_bar.php";
require_once __DIR__ . "/../../config/db.php";


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

$message = "";
$pathogen_id = "";
$department_id = "";
$warning_date = date('Y-m-d');
$severity = "";

// If an automatic warning was carried over, get its information

if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST['action'] ?? '') == 'prepare') {
    $awarning_id = $_POST['awarning_id'];

    $sql = "SELECT awarning_message, threshold_id, department_id, awarning_date
            FROM Automatic_warning
            WHERE awarning_id = ?
            AND awarning_status = 'active'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $awarning_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $automatic_warning = $result->fetch_assoc();

    $stmt->close();

    if ($automatic_warning) {
        $message = $automatic_warning['awarning_message'];
        $department_id = $automatic_warning['department_id'];
        // Get pathogen and time window from the threshold table

        $sql = "SELECT pathogen_id, threshold_time_window
                FROM Threshold
                WHERE threshold_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $automatic_warning['threshold_id']);
        $stmt->execute();

        $result = $stmt->get_result();
        $threshold = $result->fetch_assoc();

        $stmt->close();

        if ($threshold) {
            $pathogen_id = $threshold['pathogen_id'];
            $threshold_time_window = $threshold['threshold_time_window'];
            // Get the date of the automatic warning

            $warning_date = date(
                'Y-m-d',
                strtotime($automatic_warning['awarning_date'])
            );
            // Calculate the start of the threshold time window
            $start_date = date(
                'Y-m-d',
                strtotime($warning_date . " -" . ($threshold_time_window - 1) . " days")
            );
            // Calculate the mean severity risk score
            $sql = "SELECT AVG(sl.severity_risk_score) AS mean_risk
                    FROM Infection_case ic
                    JOIN Severity_level sl
                        ON ic.severity_id = sl.severity_id
                    WHERE ic.pathogen_id = ?
                    AND ic.department_id = ?
                    AND ic.case_diagnosis_date BETWEEN ? AND ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "iiss",
                $pathogen_id,
                $department_id,
                $start_date,
                $warning_date
            );

            $stmt->execute();

            $result = $stmt->get_result();
            $severity_result = $result->fetch_assoc();

            $stmt->close();

            if ($severity_result['mean_risk'] !== null) {
                $severity = round($severity_result['mean_risk']);
            }
        }
    }
}

//only process the submission after the form has been submitted (if) and then insert it into the database and also not use automatic warning fill ins for that

elseif ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST['action'] ?? '') == 'publish') {

    // Fetch data from POST request
    $awarning_id = $_POST['awarning_id'] ?? null;
    $user_id = $_SESSION['user_id'];
    $pathogen_id = $_POST['pathogen'];
    $department_id = $_POST['department'];
    $severity = $_POST['severity'];
    $warning_date = $_POST['warning_date'];
    // add timestamp 
    $warning_date = $warning_date . ' ' . date('H:i:s');


    $message = $_POST['message'];
    $icon = $_POST['icon'];

$conn->begin_transaction();

try {

    $sql = "INSERT INTO Manual_warning
            (mwarning_id, user_id, pathogen_id, department_id,
             mwarning_severity_level, mwarning_date,
             mwarning_message, mwarning_icon, hospital_id)
            VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "siiissis",
        $user_id,
        $pathogen_id,
        $department_id,
        $severity,
        $warning_date,
        $message,
        $icon,
        $hospital_id
    );

    $stmt->execute();
    $stmt->close();


    if ($awarning_id) {

        $sql = "UPDATE Automatic_warning
                SET awarning_status = 'published'
                WHERE awarning_id = ?
                AND awarning_status = 'active'";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $awarning_id);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();

    // Redirect to avoid form resubmission and repopulation
    header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
    exit;

    } catch (Exception $e) {

        $conn->rollback();

        // Redirect to show an error message
        header("Location: /write_announcement.php?error=1");
        exit;
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
    <link rel="stylesheet" href="/assets/css/announcements.css?v=183">

</head>

<body>
<img src="/assets/img/virus_tr_nobg.png" class="virus_tr">
<img src="/assets/img/rings_br_nobg.png" class="rings_br">
    <div class="page">

        <form method="POST" action="">
            <input type="hidden"
                name="action"
                value="publish">

            <?php if (isset($awarning_id)): ?>
                <input type="hidden"
                    name="awarning_id"
                    value="<?= htmlspecialchars($awarning_id) ?>">
            <?php endif; ?>

            <!-- ANNOUNCEMENT AREA -->

            <div class="message">

                <h3>Write announcement</h3>

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
                                    <img src="/assets/img/mask_icon_bgremoved.png">
                                </span>

                            </label>


                            <label>

                                <input
                                    type="radio"
                                    name="icon"
                                    value="2"
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
                    <button type="submit" class="submit-btn">
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

mysqli_close($conn);

?>