<?php
require __DIR__ . "/../../src/authenticate_check.php";

require_once __DIR__ . "/../../config/db.php";

require __DIR__ . "/../../src/navigation_bar.php";

$user_id = $_SESSION['user_id'];
$message = "";

$per_page = 3;
$current_page = isset($_GET['page']) && is_numeric($_GET['page'])
    ? max(1, (int)$_GET['page'])
    : 1;

$offset = ($current_page - 1) * $per_page;


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
$is_admin = ($user['role_name'] === 'Admin');
$hospital_id = $user['hospital_id'];


// Get all cases/ announcements of the user


$cases = []; 
$announcements = [];
if ($user['role_name'] === 'Doctor'|| $user['role_name'] === 'Admin') {
    $sql = "SELECT inf.*, patho.pathogen_name, s.severity_name, d.department_name
    FROM Infection_case inf
    JOIN Pathogen patho ON inf.pathogen_id = patho.pathogen_id
    JOIN Severity_level s ON inf.severity_id = s.severity_id
    JOIN Department d ON inf.department_id = d.department_id
    WHERE inf.user_id = ?
    ORDER BY inf.case_diagnosis_date DESC
    LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $user_id, $per_page, $offset);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $cases[] = $row;
    }

    $stmt->close();

}

if ($user['role_name']=== 'Epidemiologist'|| $user['role_name'] === 'Admin') {
    $sql="SELECT mw.*, p.pathogen_name, d.department_name
    FROM Manual_warning mw
    JOIN Pathogen p ON mw.pathogen_id = p.pathogen_id
    JOIN Department d ON mw.department_id = d.department_id
    WHERE mw.user_id = ?
    ORDER BY mwarning_date DESC
    LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $user_id, $per_page, $offset);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    $stmt->close();

    }
// count the records
    
$total_cases = 0;
$total_announcements = 0;

if ($user['role_name'] === 'Doctor' || $user['role_name'] === 'Admin') {
    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM Infection_case WHERE user_id = ?"
    );
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($total_cases);
    $stmt->fetch();
    $stmt->close();
}

if ($user['role_name'] === 'Epidemiologist' || $user['role_name'] === 'Admin') {
    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM Manual_warning WHERE user_id = ?"
    );
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($total_announcements);
    $stmt->fetch();
    $stmt->close();
}

$total_rows = max($total_cases, $total_announcements);
$total_pages = (int) ceil($total_rows / $per_page);


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
    $user = $result->fetch_assoc();

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
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=, initial-scale=1.0">

    <link rel="stylesheet" href="/assets/css/profile.css?v=173">
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
                <?php if ($is_admin): ?>
                      <th>Delete</th>
                <?php endif; ?>
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
                                <form action="/cases/user_edit_case.php" method="POST">
                                    <input type="hidden"
                                        name="case_id"
                                        value="<?= htmlspecialchars($case['case_id']) ?>">
                                    <button type="submit" class="buttbutt">Edit</button>
                                </form>
                            </td>

                            <?php if ($is_admin): ?>
                              <td>
        <form action="/cases/delete_case.php" method="POST"
              onsubmit="return confirm('Are you sure you want to delete this case?');">
            <input type="hidden" name="case_id"
                   value="<?= htmlspecialchars($case['case_id']) ?>">
            <button type="submit" class="delete-btn">Delete</button>
        </form>
                              </td>
                             <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
            </tbody>
        <?php endif; ?>
        </table>


        <?php if ($user['role_name'] === 'Epidemiologist'|| $user['role_name'] === 'Admin'): ?>
            <h1 class='titel'>Announcements</h1>

            <table class="user-table">
            <thead>
            <tr>
                    <th>Date</th>
                    <th>Message</th>
                    <th>Pathogen</th>
                    <th>Ward</th>
                    <th>Edit</th>

                    <?php if ($is_admin): ?>
                       <th>Delete</th>
                      <?php endif; ?>
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
                                <form action="/announcements/edit_announcement.php" method="POST">
                                    <input type="hidden"
                                        name="mwarning_id"
                                        value="<?= htmlspecialchars($announcement['mwarning_id']) ?>">
                                    <button type="submit" class="buttbutt">Edit</button>
                                </form>
                            </td>
                            <?php if ($is_admin): ?>
    <td>
        <form action="/announcements/delete_announcement.php" method="POST"
              onsubmit="return confirm('Are you sure you want to delete this announcement?');">
            <input type="hidden" name="id"
                   value="<?= htmlspecialchars($announcement['mwarning_id']) ?>">
            <button type="submit" class="delete-btn">Delete</button>
        </form>
    </td>
<?php endif; ?>

                        </tr>
                    <?php endforeach; ?>

            </tbody>
        </table>
        <?php endif; ?>

        <?php if ($total_pages > 1): ?>
         <div class="pagination">
        <?php for ($page = 1; $page <= $total_pages; $page++): ?>
            <a href="?page=<?= $page ?>"
               class="<?= $page == $current_page ? 'active' : '' ?>">
                <?= $page ?>
            </a>
        <?php endfor; ?>

        <?php if ($current_page < $total_pages): ?>
            <a href="?page=<?= $current_page + 1 ?>">Next</a>
        <?php endif; ?>
    </div>
       <?php endif; ?>

    <form method="POST"
        onsubmit="return confirm('Are you sure you want to request for your account to be deleted?');">

        <button type="submit" class="delbutt">
            Request Account Deletion
        </button>

    </form>

    <?php
    if ($message !== "") {echo "<p>" . htmlspecialchars($message) . "</p>";}
    ?>
</body>
</html>