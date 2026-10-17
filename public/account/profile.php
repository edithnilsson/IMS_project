<?php
require __DIR__ . "/../../src/authenticate_check.php";

require_once __DIR__ . "/../../config/db.php";

require __DIR__ . "/../../src/navigation_bar.php";


$user_id = $_SESSION['user_id'];

$message = "";

// Get the hospital and other information of the logged-in doctor
$sql = "SELECT s.hospital_id, s.user_username, s.user_name, s.user_surname, s.user_email, s.user_phone_number, s.role_id, h.hospital_name, r.role_name
        FROM System_user s
        JOIN Hospital h ON s.hospital_id = h.hospital_id
        JOIN Roles r ON s.role_id = r.role_id
        WHERE user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

$hospital_id = $hospital['hospital_id'];

// current time stamp
$current_time = time();

// Number of results to show on each page.
$num_results_on_page = 10;

// ---------- Cases -------------

// Get the total number of cases in the database
$sql="SELECT COUNT(*) 
FROM Infection_case
WHERE hospital_id = ?
AND user_id = ?";

$stmt= $conn->prepare($sql);
$stmt->bind_param("ss", $hospital_id, $user_id);
$stmt->execute();
$result = $stmt-> get_result();
$total_cases  = $result->fetch_row()[0];
$stmt->close();

// Check if the page number is specified and check if it's a number, if not return the default page number which is 1.
$current_case_page = isset($_GET['case_page']) && is_numeric($_GET['case_page']) ? $_GET['case_page'] : 1;

if ($user['role_name'] === 'Doctor'|| $user['role_name'] === 'Admin') 
{
    $sql = "SELECT inf.*, patho.pathogen_name, s.severity_name, d.department_name
    FROM Infection_case inf
    JOIN Pathogen patho ON inf.pathogen_id = patho.pathogen_id
    JOIN Severity_level s ON inf.severity_id = s.severity_id
    JOIN Department d ON inf.department_id = d.department_id
    WHERE inf.hospital_id = ?
    AND inf.user_id = ?
    LIMIT ?,? ";

    $calc_page = ($current_case_page - 1) * $num_results_on_page;

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssii", $hospital_id, $user_id, $calc_page, $num_results_on_page);
        $stmt->execute();
    
        $cases = $stmt->get_result();

        $stmt->close();
        }
}

// ---------- Announcements -------------
// Check if the page number is specified and check if it's a number, if not return the default page number which is 1.
$current_announcement_page = isset($_GET['announcement_page']) && is_numeric($_GET['announcement_page']) ? $_GET['announcement_page'] : 1;

// Get the total number of announcements in the database
$sql="SELECT COUNT(*) 
FROM Manual_warning
WHERE hospital_id = ?
AND user_id = ?";

$stmt= $conn->prepare($sql);
$stmt->bind_param("ss", $hospital_id, $user_id);
$stmt->execute();
$result = $stmt-> get_result();
$total_announcements  = $result->fetch_row()[0];
$stmt->close();

// Get the all information of the  announcements in the database to display
if ($user['role_name']=== 'Epidemiologist'||$user['role_name']=== 'Chief Epidemiologist'|| $user['role_name'] === 'Admin') 
{
    $sql="SELECT mw.*, p.pathogen_name, d.department_name
    FROM Manual_warning mw
    JOIN Pathogen p ON mw.pathogen_id = p.pathogen_id
    JOIN Department d ON mw.department_id = d.department_id
    WHERE mw.hospital_id =?
    AND mw.user_id= ?
    ORDER BY mwarning_date DESC 
    LIMIT ?,?";

    $calc_page = ($current_announcement_page  - 1) * $num_results_on_page;

    $stmt = $conn->prepare($sql);
    
    if($stmt) {
         $stmt->bind_param("ssii", $hospital_id, $user_id, $calc_page, $num_results_on_page);
        $stmt->execute();
        $announcements = $stmt->get_result();
        $stmt->close();
    }
}

// Request to delete user

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get the user's hospital
    $sql = "SELECT hospital_id
            FROM System_user
            WHERE user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $hospital = $result->fetch_assoc();

    $stmt->close();

    $hospital_id = $user['hospital_id'];

    // Check if there is already a pending request
    $sql = "SELECT request_id
            FROM Account_deletion_request
            WHERE user_id = ?
            AND request_status = 'pending'
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();


    if ($result->num_rows > 0) {

        $message = "You already have a pending account deletion request.";

    } else {

        // Create a new deletion request
        $sql = "INSERT INTO Account_deletion_request
                (user_id, hospital_id)
                VALUES (?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $user_id, $hospital_id);

        if ($stmt->execute()) {
            $message = "Your account deletion request has been submitted.";
        } else {
            $message = "There was a problem submitting your request.";
        }
    }

    $stmt->close();
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=, initial-scale=1.0">

    <link rel="stylesheet" href="/assets/css/profile.css?v=343">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0">< </script>

    <title>Document</title>


</head>

<body>
<img src="/assets/img/virus_bl_nobg.png" class="virus_bl">
<img src="/assets/img/virus_tr_nobg.png" class="virus_tr">
<img src="/assets/img/rings_br_nobg.png" class="rings_br">

<h1 class="rubrik"><?= htmlspecialchars($user['user_name'] . " " . $user['user_surname']) ?></h1>
    <div class = "container">
        <div class = "profile_info">

            <div class = "form-row" >
                <img src="/assets/img/user.icon-bgremoved.png" class="icon">
                <?= htmlspecialchars($user['user_username']) ?>

                <img src="/assets/img/phone.icon-bgremoved.png" class="icon">
                <?= htmlspecialchars($user['user_phone_number']) ?>

                <img src="/assets/img/mail.icon-bgremoved.png" class="icon">
                <?= htmlspecialchars($user['user_email']) ?>

            </div>

            <div class = "form-row" >

                <img src="/assets/img/role.icon.png" class="icon">
                    <?= htmlspecialchars($user['role_name']) ?>

                <img src="/assets/img/ward_black.icon.png" class="icon">
                    <?= htmlspecialchars($user['hospital_name']) ?>
            </div>
        </div>
    <form method="POST"
        onsubmit="return confirm('Are you sure you want to request for your account to be deleted?');">

        <button type="submit" class="delbutt">
            Request Account Deletion
        </button>

    </form>

    <?php if ($message !== ""): ?>
    <p class="deletion-message">
        <?= htmlspecialchars($message) ?>
    </p>
    <?php endif; ?>

        <?php if ($user['role_name'] === 'Doctor'|| $user['role_name'] === 'Admin'): ?>
            <h1 class='titel'>Cases</h1>
        <table class="user-table">
            <thead>
            <tr>
                <th>Date of diagnosis</th>
                <th>Date of infection</th>
                <th>Pathogen</th>
                <th>Ward</th>
                <th>Severity</th>
                <th>Comments</th>
                <th>Edit</th>
            </tr>
            </thead>
            <tbody>
                    <?php foreach ($cases as $case): ?>
                        <tr>
                            <td><?= htmlspecialchars($case['case_diagnosis_date']) ?></td>
                            <td><?= htmlspecialchars($case['case_infection_date']) ?></td>
                            <td><?= htmlspecialchars($case['pathogen_name']) ?></td>
                            <td><?= htmlspecialchars($case['department_name']) ?></td>
                            <td><?= htmlspecialchars($case['severity_name']) ?></td>
                            <td><?= htmlspecialchars($case['case_comments']) ?></td>
                            <td>
                                <?php if($current_time - strtotime($case['case_date_issued']) < 48 * 3600): ?>
                                <form action="/cases/user_edit_case.php" method="POST">
                                    <input type="hidden"
                                        name="case_id"
                                        value="<?= htmlspecialchars($case['case_id']) ?>">
                                    <button type="submit" class="buttbutt">Edit</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
            </tbody>
        <?php endif;?>
        </table>

        <?php  
        // pagination - case
        //how many pages will be needed? 
        $page_count = ceil($total_cases / $num_results_on_page);

        if ($page_count > 0): ?>
            <ul class="pagination">
                <!-- if you are on another page than 1 then show the prev button -->
                <?php if ($current_case_page  > 1): ?>
                <li><a href="profile.php?case_page=<?php echo $current_case_page -1 ?>">Prev</a></li>
                <?php endif; ?>

                <!-- if you are on a page further down than 3 then show ... instead of all the previous pages  -->
                <?php if ($current_case_page  > 3): ?>
                <li ><a href="profile.php?case_page=1">1</a></li>
                <li >...</li>
                <?php endif; ?>

                <!-- show previous pages if the pagenumber does not become negative -->
                <?php if ($current_case_page -2 > 0): ?><li class="page"><a href="profile.php?case_page=<?php echo $current_case_page -2 ?>"><?php echo $current_case_page -2 ?></a></li><?php endif; ?>
                <?php if ($current_case_page -1 > 0): ?><li class="page"><a href="profile.php?case_page=<?php echo $current_case_page -1 ?>"><?php echo $current_case_page -1 ?></a></li><?php endif; ?>
                    
                <!-- mark the curren page you are on-->
                <li class="currentpage"><a href="profile.php?case_page=<?php echo $current_case_page  ?>"><?php echo $current_case_page ?></a></li>
                
                <!-- show the up comming pages if they do not exceed the number of pages needed  -->
                <?php if ($current_case_page +1 < ceil($total_cases/ $num_results_on_page)+1): ?><li class="page"><a href="profile.php?case_page=<?php echo $current_case_page +1 ?>"><?php echo $current_case_page +1 ?></a></li><?php endif; ?>
                <?php if ($current_case_page +2 < ceil($total_cases / $num_results_on_page)+1): ?><li class="page"><a href="profile.php?case_page=<?php echo $current_case_page +2 ?>"><?php echo $current_case_page +2 ?></a></li><?php endif; ?>
                
                <!-- if the current page is furhter away from the end page than 2 pages then show ... -->
                <?php if ($current_case_page  < ceil($total_cases/ $num_results_on_page)-2): ?>
                <li>...</li>
                <li><a href="profile.php?case_page=<?php echo ceil($total_cases/ $num_results_on_page) ?>"><?php echo ceil($total_cases / $num_results_on_page) ?></a></li>
                <?php endif; ?>
            
                <!-- show the next button if you are not already on the last page -->
                <?php if ($current_case_page  < ceil($total_cases/ $num_results_on_page)): ?>
                <li><a href="profile.php?case_page=<?php echo $current_case_page +1 ?>">Next</a></li>
                <?php endif; ?>
            </ul>
            <?php endif; ?>
        


        <?php if ($user['role_name'] === 'Epidemiologist'||$user['role_name']=== 'Chief Epidemiologist'|| $user['role_name'] === 'Admin'): ?>
            <h1 class='titel'>Announcements</h1>

            <table class="user-table">
            <thead>
            <tr>
                    <th>Date</th>
                    <th>Message</th>
                    <th>Pathogen</th>
                    <th>Ward</th>
                    <th>Edit</th>
            </tr>
            </thead>
            <tbody>
                    <?php foreach ($announcements as $announcement): ?>
                        <tr>
                            <td><?= htmlspecialchars($announcement['mwarning_date']) ?></td>
                            <td><?= htmlspecialchars($announcement['mwarning_message']) ?></td>
                            <td><?= htmlspecialchars($announcement['pathogen_name']) ?></td>
                            <td><?= htmlspecialchars($announcement['department_name']) ?></td>
                            <td>
                                <?php if($current_time - strtotime($announcement['mwdate_issued']) < 48 * 3600): ?>
                                <form action="/announcements/edit_announcement.php" method="POST">
                                    <input type="hidden"
                                        name="mwarning_id"
                                        value="<?= htmlspecialchars($announcement['mwarning_id']) ?>">
                                    <button type="submit" class="buttbutt">Edit</button> 
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

            </tbody>
        </table>
        <?php endif; ?>
<?php 
// pagination - announcements
//how many pages will be needed? 
$page_count = ceil($total_announcements / $num_results_on_page);

if ($page_count > 0): ?>
    <ul class="pagination">
        <!-- if you are on another page than 1 then show the prev button -->
        <?php if ($current_announcement_page  > 1): ?>
        <li><a href="profile.php?announcement_page=<?php echo $current_announcement_page -1 ?>">Prev</a></li>
        <?php endif; ?>

        <!-- if you are on a page further down than 3 then show ... instead of all the previous pages  -->
        <?php if ($current_announcement_page  > 3): ?>
        <li ><a href="profile.php?announcement_page=1">1</a></li>
        <li >...</li>
        <?php endif; ?>

        <!-- show previous pages if the pagenumber does not become negative -->
        <?php if ($current_announcement_page -2 > 0): ?><li class="page"><a href="profile.php?announcement_page=<?php echo $current_announcement_page -2 ?>"><?php echo $current_announcement_page -2 ?></a></li><?php endif; ?>
        <?php if ($current_announcement_page -1 > 0): ?><li class="page"><a href="profile.php?announcement_page=<?php echo $current_announcement_page -1 ?>"><?php echo $current_announcement_page -1 ?></a></li><?php endif; ?>
            
        <!-- mark the curren page you are on-->
        <li class="currentpage"><a href="profile.php?announcement_page=<?php echo $current_announcement_page  ?>"><?php echo $current_announcement_page ?></a></li>
        
        <!-- show the up comming pages if they do not exceed the number of pages needed  -->
        <?php if ($current_announcement_page +1 < ceil($total_announcements/ $num_results_on_page)+1): ?><li class="page"><a href="profile.php?announcement_page=<?php echo $current_announcement_page +1 ?>"><?php echo $current_announcement_page +1 ?></a></li><?php endif; ?>
        <?php if ($current_announcement_page +2 < ceil($total_announcements / $num_results_on_page)+1): ?><li class="page"><a href="profile.php?announcement_page=<?php echo $current_announcement_page +2 ?>"><?php echo $current_announcement_page +2 ?></a></li><?php endif; ?>
        
        <!-- if the current page is furhter away from the end page than 2 pages then show ... -->
        <?php if ($current_announcement_page  < ceil($total_announcements/ $num_results_on_page)-2): ?>
        <li>...</li>
        <li><a href="profile.php?announcement_page=<?php echo ceil($total_announcements / $num_results_on_page) ?>"><?php echo ceil($total_announcements / $num_results_on_page) ?></a></li>
        <?php endif; ?>
    
        <!-- show the next button if you are not already on the last page -->
        <?php if ($current_announcement_page  < ceil($total_announcements/ $num_results_on_page)): ?>
        <li><a href="profile.php?announcement_page=<?php echo $current_announcement_page +1 ?>">Next</a></li>
        <?php endif; ?>
    </ul>
    <?php endif; ?>
    


    </div>
</body>
</html>