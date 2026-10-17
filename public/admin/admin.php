<?php

require __DIR__ . "/../../src/authenticate_check.php";
access('Admin');

include __DIR__ . "/../../src/navigation_bar.php";
require __DIR__ . "/../../config/db.php";

// Display a message from the deletion request processing page about the admin profile deletions
if (isset($_SESSION['admin_message'])) {
    $message = $_SESSION['admin_message'];
    unset($_SESSION['admin_message']);

    echo "<script>alert(" . json_encode($message) . ");</script>";
}


$user_id = $_SESSION['user_id'];

// Get the hospital of the logged-in admin
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

//adding departments: 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $department_name = $_POST['department_name'];

    if (empty($department_name)){
        echo "<script>alert('Department name can not be empty');</script>";
    }
    else {
        $sql = "INSERT INTO Department (department_name, hospital_id) VALUES (?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $department_name, $hospital_id);
        $result = $stmt->execute();
        

        $stmt -> close(); 


        if ($result) {
            echo "<script>alert('Department added!'); window.location.href='/admin/admin.php';</script>";
        } else {
            echo "<script>alert('Something went wrong');</script>";
        }

    }
    }

 // Get pending users   
$pending_users = [];

$sql = "SELECT user_id, user_name, user_surname,
               user_username, user_email
        FROM System_user
        WHERE hospital_id = ?
        AND user_status = 'pending'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hospital_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $pending_users[] = $row;
}

$stmt->close();

 


// Get pending account removal requests
$deletion_requests = [];

$sql = "SELECT adr.request_id, adr.requested_at, su.user_name, su.user_surname, su.user_username, r.role_name
        FROM Account_deletion_request adr
        JOIN System_user su ON adr.user_id = su.user_id
        JOIN Roles r ON su.role_id = r.role_id
        WHERE adr.hospital_id = ?
        AND adr.request_status = 'pending'
        ORDER BY adr.requested_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hospital_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $deletion_requests[] = $row;
}

$stmt->close();

// Get administrators
$admins = [];

$sql = "SELECT user_id, user_name, user_surname, user_username, user_phone_number, user_email
        FROM System_user
        WHERE hospital_id = ?
        AND role_id = 1
        ORDER BY user_surname, user_name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hospital_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $admins[] = $row;
}

$stmt->close();

// Get doctors
$doctors = [];

$sql = "SELECT user_id, user_name, user_surname, user_username, user_phone_number, user_email
        FROM System_user
        WHERE hospital_id = ?
        AND role_id = 2
        ORDER BY user_surname, user_name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hospital_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}

$stmt->close();

// Get epidemiologists and chief epidemiologists
$epidemiologists = [];

$sql = "SELECT user_id, user_name, user_surname, user_username, user_phone_number, user_email, role_id
        FROM System_user
        WHERE hospital_id = ?
        AND role_id IN (3, 4)
        ORDER BY user_surname, user_name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hospital_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $epidemiologists[] = $row;
}

$stmt->close();


//Get Wards
$department= [];

$sql = "SELECT department_id, department_name
        FROM Department
        WHERE hospital_id = ?
        AND department_status = 'active'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hospital_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}

$stmt->close();

// Get announcements
$announcements = [];

$sql = "SELECT Manual_warning.mwarning_id,
               Manual_warning.mwarning_message,
               Manual_warning.mwarning_date,
               System_user.user_name,
               System_user.user_surname
        FROM Manual_warning
        JOIN System_user
        ON Manual_warning.user_id = System_user.user_id
        WHERE Manual_warning.hospital_id = ?
        ORDER BY Manual_warning.mwarning_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hospital_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
}

$stmt->close();

// Get cases
$cases = [];

$sql = "SELECT Infection_case.case_id,
               Infection_case.case_diagnosis_date,
               Pathogen.pathogen_name,
               Department.department_name,
               System_user.user_name,
               System_user.user_surname
        FROM Infection_case
        JOIN Pathogen ON Infection_case.pathogen_id = Pathogen.pathogen_id
        JOIN Department ON Infection_case.department_id = Department.department_id
        JOIN System_user ON Infection_case.user_id = System_user.user_id
        WHERE Infection_case.hospital_id = ?
        AND Infection_case.user_id = ?
        ORDER BY Infection_case.case_diagnosis_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $hospital_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $cases[] = $row;
}

$stmt->close();


 // Pagination for Cases
$cases_per_page = 5;
$cases_page = isset($_GET['cases_page']) ? max(1, (int)$_GET['cases_page']) : 1;
$total_cases_pages = max(1, (int)ceil(count($cases) / $cases_per_page));
$cases_page = min($cases_page, $total_cases_pages);
$display_cases = array_slice($cases, ($cases_page - 1) * $cases_per_page, $cases_per_page);

// Pagination for Announcements
$announcements_per_page = 5;
$announcements_page = isset($_GET['announcements_page']) ? max(1, (int)$_GET['announcements_page']) : 1;
$total_announcements_pages = max(1, (int)ceil(count($announcements) / $announcements_per_page));
$announcements_page = min($announcements_page, $total_announcements_pages);
$display_announcements = array_slice($announcements, ($announcements_page - 1) * $announcements_per_page, $announcements_per_page);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin Page</title>
    <link rel="stylesheet" href="/assets/css/admin.css?v=123">
</head>

<body>
<img src="/assets/img/virus_bl_nobg.png" class="virus_bl">
<img src="/assets/img/rings_br_nobg.png" class="rings_br">

<!-- Pending Accounts checking -->
<div class="table-container">
<h1 class='title'>Pending Accounts</h1>

<?php if (empty($pending_users)) { ?>

    <p class="texts">No pending accounts.</p>

<?php } else { ?>

<table class="user-table pending-table">
    <thead>
        <tr>
            <th>Full Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>

    <?php foreach ($pending_users as $pending) { ?>

    <tr>
        <td>
            <?php echo htmlspecialchars($pending['user_name'] . " " . $pending['user_surname']); ?>
        </td>

        <td>
            <?php echo htmlspecialchars($pending['user_username']); ?>
        </td>

        <td>
            <?php echo htmlspecialchars($pending['user_email']); ?>
        </td>

        <td>
            <form action="/admin/confirm_user.php" method="POST">
                <input type="hidden" name="id"
                       value="<?php echo $pending['user_id']; ?>">

                       <input type="hidden" name="action" value="confirm">

                <button type="submit" class='butt'>Confirm</button>
            </form>

            <form action="/admin/confirm_user.php" method="POST"
               onsubmit="return confirm('Dismiss this account?');">
                 <input type="hidden" name="id" value="<?php echo $pending['user_id']; ?>">
                <input type="hidden" name="action" value="dismiss">
                <button type="submit" class="dismiss-btn">Dismiss</button>
             </form>
        </td>
    </tr>

    <?php } ?>

</table>
</div>
<?php } ?>




<!-- Account Removal Requests -->

<h1 class='title'>Account Removal Requests</h1>

<?php if (empty($deletion_requests)): ?>

    <p class="texts">No pending account removal requests.</p>

<?php else: ?>

<div class="table-container">

<table class="user-table">

    <thead>
        <tr>
            <th>Role</th>
            <th>Full Name</th>
            <th>Username</th>
            <th>Requested At</th>
            <th></th>
        </tr>
    </thead>

    <tbody>

        <?php foreach ($deletion_requests as $request): ?>
            <tr>
                <td><?= htmlspecialchars($request['role_name']) ?></td>
                <td><?= htmlspecialchars($request['user_name'] . " " . $request['user_surname']) ?></td>
                <td><?= htmlspecialchars($request['user_username']) ?></td>
                <td><?= htmlspecialchars($request['requested_at']) ?></td>
                <td>
                    <!-- To know which ID was selected, put it into POST -->
                    <form action="/admin/process_deletion_request.php" method="POST">

                        <input type="hidden"
                               name="request_id"
                               value="<?= htmlspecialchars($request['request_id']) ?>">

                        <button type="submit"
                                name="action"
                                value="approve"
                                class='butt'>
                            Approve
                        </button>

                        <button type="submit"
                                name="action"
                                value="reject"
                                class='butt'>
                            Decline
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<!-- Administrators -->

<h1 class='title'>Administrators</h1>

<?php if (empty($admins)): ?>

    <p class="texts">No administrators found.</p>

<?php else: ?>

<div class="table-container">

<table class="user-table">

    <thead>
        <tr>
            <th>Full Name</th>
            <th>Username</th>
            <th>Phone</th>
            <th>Email</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($admins as $admin): ?>
            <tr>
                <td><?= htmlspecialchars($admin['user_name'] . " " . $admin['user_surname']) ?></td>
                <td><?= htmlspecialchars($admin['user_username']) ?></td>
                <td><?= htmlspecialchars($admin['user_phone_number']) ?></td>
                <td><?= htmlspecialchars($admin['user_email']) ?></td>
                <td>
                    <!-- To know which ID was selected, put it into POST -->
                    <form action="/admin/edit_user.php" method="POST">

                        <input type="hidden"
                               name="user_id"
                               value="<?= htmlspecialchars($admin['user_id']) ?>">

                        <button type="submit" class='butt'>Edit</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<!-- Doctors -->
<h1 class='title'>Doctors</h1>

<?php if (empty($doctors)): ?>

    <p class="texts">No doctors found.</p>

<?php else: ?>

<div class="table-container">

<table class="user-table">

    <thead>
        <tr>
            <th>Full Name</th>
            <th>Username</th>
            <th>Phone</th>
            <th>Email</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($doctors as $doctor): ?>
            <tr>
                <td><?= htmlspecialchars($doctor['user_name'] . " " . $doctor['user_surname']) ?></td>
                <td><?= htmlspecialchars($doctor['user_username']) ?></td>
                <td><?= htmlspecialchars($doctor['user_phone_number']) ?></td>
                <td><?= htmlspecialchars($doctor['user_email']) ?></td>
                <td>
                    <!-- To know which ID was selected, put it into POST -->
                    <form action="/admin/edit_user.php" method="POST">

                        <input type="hidden"
                               name="user_id"
                               value="<?= htmlspecialchars($doctor['user_id']) ?>">

                        <button type="submit" class='butt'>Edit</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<!-- Epidemiologists -->
<h1 class='title'>Epidemiologists</h1>

<?php if (empty($epidemiologists)): ?>

    <p class="texts">No epidemiologists found.</p>

<?php else: ?>

<div class="table-container">

<table class="user-table">

    <thead>
        <tr>
            <th>Full Name</th>
            <th>Username</th>
            <th>Phone</th>
            <th>Email</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($epidemiologists as $epidemiologist): ?>
            <?php $name = $epidemiologist['user_name'] . " " . $epidemiologist['user_surname'];
            if ($epidemiologist['role_id'] == 4) {
                $name .= " (Chief)";
            }
            ?>
            <tr>
                <td><?= htmlspecialchars($name) ?></td>
                <td><?= htmlspecialchars($epidemiologist['user_username']) ?></td>
                <td><?= htmlspecialchars($epidemiologist['user_phone_number']) ?></td>
                <td><?= htmlspecialchars($epidemiologist['user_email']) ?></td>
                <td>
                    <!-- To know which ID was selected, put it into POST -->
                    <form action="/admin/edit_user.php" method="POST">

                        <input type="hidden"
                               name="user_id"
                               value="<?= htmlspecialchars($epidemiologist['user_id']) ?>">

                        <button type="submit" class='butt'>Edit</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<!-- Wards -->
<h1 class='title'>Wards</h1>

<form action="" method="post" class="blabla">
Add a Department: <input type="text" name="department_name"><br>
<input type="submit" class='butt2'>
</form>

<?php if (empty($departments)): ?>

    <p class="texts">No wards found.</p>

<?php else: ?>

<div class="table-container">

<table class="user-table">

    <thead>
        <tr>
            <th>Departments</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($departments as $department): ?>
            <?php $Hospital = $department['department_id'];
            ?>
            <tr>
                <td><?= htmlspecialchars($department['department_name']) ?></td>
                <td>
                    <!-- To know which ID was selected, put it into POST -->
                    <form action="/admin/delete_department.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this ward?');">
                        <input type="hidden" name="department_id" value="<?= htmlspecialchars($department['department_id']) ?>">
                        
                        <button type="submit" class='butt'>Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<!-- Cases -->
<h1 class='title'>Cases</h1>

<?php if (empty($cases)): ?>

    <p class="texts">No cases found.</p>

<?php else: ?>

<div class="table-container">
<table class="user-table">
    <thead>
        <tr>
            <th>Date of diagnosis</th>
            <th>Pathogen</th>
            <th>Ward</th>
            <th>Doctor's Name</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($display_cases as $case): ?>
        <tr>
            <td><?= htmlspecialchars($case['case_diagnosis_date']) ?></td>
            <td><?= htmlspecialchars($case['pathogen_name']) ?></td>
            <td><?= htmlspecialchars($case['department_name']) ?></td>
            <td><?= htmlspecialchars($case['user_name'] . " " . $case['user_surname']) ?></td>
            <td>
                <form action="/cases/delete_case.php" method="POST"
                      onsubmit="return confirm('Delete this case?');">
                    <input type="hidden" name="case_id"
                           value="<?= htmlspecialchars($case['case_id']) ?>">
                    <button type="submit" class="butt">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>


<div class="pagination">
    <?php if ($cases_page > 1): ?>
        <a href="?cases_page=<?= $cases_page - 1 ?>&announcements_page=<?= $announcements_page ?>">Previous</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_cases_pages; $i++): ?>
        <a href="?cases_page=<?= $i ?>&announcements_page=<?= $announcements_page ?>"
           class="<?= $i == $cases_page ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>

    <?php if ($cases_page < $total_cases_pages): ?>
        <a href="?cases_page=<?= $cases_page + 1 ?>&announcements_page=<?= $announcements_page ?>">Next</a>
    <?php endif; ?>
</div>


<?php endif; ?>

<!-- Announcements -->
<h1 class='title'>Announcements</h1>

<?php if (empty($announcements)): ?>
    <p class="texts">No announcements found.</p>
<?php else: ?>

<div class="table-container">
<table class="user-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Announcement</th>
            <th>Epidemiologist's Name</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($display_announcements as $announcement): ?>
        <tr>
            <td><?= substr($announcement['mwarning_date'], 0, 10) ?></td>

            <td><?= htmlspecialchars($announcement['mwarning_message']) ?></td>

            <td><?= htmlspecialchars($announcement['user_name'] . " " . $announcement['user_surname']) ?></td>

            <td>
                <form action="/announcements/delete_announcement.php"
                      method="POST"
                      onsubmit="return confirm('Delete this announcement?');">

                    <input type="hidden" name="id"
                           value="<?= htmlspecialchars($announcement['mwarning_id']) ?>">

                    <button type="submit" class="butt">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>


<div class="pagination">
    <?php if ($announcements_page > 1): ?>
        <a href="?cases_page=<?= $cases_page ?>&announcements_page=<?= $announcements_page - 1 ?>">Previous</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_announcements_pages; $i++): ?>
        <a href="?cases_page=<?= $cases_page ?>&announcements_page=<?= $i ?>"
           class="<?= $i == $announcements_page ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>

    <?php if ($announcements_page < $total_announcements_pages): ?>
        <a href="?cases_page=<?= $cases_page ?>&announcements_page=<?= $announcements_page + 1 ?>">Next</a>
    <?php endif; ?>
</div>


<?php endif; ?>


</body>
</html>