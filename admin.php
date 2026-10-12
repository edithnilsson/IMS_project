<?php

require "authenticate_check.php";
// change to 'Admin' later 
access('manage_user');
require "config/db.php";

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
            echo "<script>alert('Department added!'); window.location.href='admin.php';</script>";
        } else {
            echo "<script>alert('Something went wrong');</script>";
        }

    }
    }

 // Get pending users   checking 
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
// Get epidemiologists and chief epidemiologists
$departments= [];

$sql = "SELECT department_id, department_name
        FROM Department
        WHERE hospital_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hospital_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin Page</title>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>

<?php include 'hamburger_menu.php'; ?>


<!-- Pending Accounts checking -->

<h2>Pending Accounts</h2>

<?php if (empty($pending_users)) { ?>

    <p>No pending accounts.</p>

<?php } else { ?>

<table class="user-table">
    <tr>
        <th>Name</th>
        <th>Username</th>
        <th>Email</th>
        <th>Action</th>
    </tr>

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
            <form action="confirm_user.php" method="GET">
                <input type="hidden" name="id"
                       value="<?php echo $pending['user_id']; ?>">

                <button type="submit">Confirm</button>
            </form>
        </td>
    </tr>

    <?php } ?>

</table>

<?php } ?>




<!-- Account Removal Requests -->

<h2>Account Removal Requests</h2>

<?php if (empty($deletion_requests)): ?>

    <p>No pending account removal requests.</p>

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
                    <form action="process_deletion_request.php" method="POST">

                        <input type="hidden"
                               name="request_id"
                               value="<?= htmlspecialchars($request['request_id']) ?>">

                        <button type="submit"
                                name="action"
                                value="approve">
                            Approve
                        </button>

                        <button type="submit"
                                name="action"
                                value="reject">
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

<h2>Administrators</h2>

<?php if (empty($admins)): ?>

    <p>No administrators found.</p>

<?php else: ?>

<div class="table-container">

<table class="user-table">

    <thead>
        <tr>
            <th>ID</th>
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
                <td><?= htmlspecialchars($admin['user_id']) ?></td>
                <td><?= htmlspecialchars($admin['user_name'] . " " . $admin['user_surname']) ?></td>
                <td><?= htmlspecialchars($admin['user_username']) ?></td>
                <td><?= htmlspecialchars($admin['user_phone_number']) ?></td>
                <td><?= htmlspecialchars($admin['user_email']) ?></td>
                <td>
                    <!-- To know which ID was selected, put it into POST -->
                    <form action="edit_user.php" method="POST">

                        <input type="hidden"
                               name="user_id"
                               value="<?= htmlspecialchars($admin['user_id']) ?>">

                        <button type="submit">Edit</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<!-- Doctors -->
<h2>Doctors</h2>

<?php if (empty($doctors)): ?>

    <p>No doctors found.</p>

<?php else: ?>

<div class="table-container">

<table class="user-table">

    <thead>
        <tr>
            <th>ID</th>
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
                <td><?= htmlspecialchars($doctor['user_id']) ?></td>
                <td><?= htmlspecialchars($doctor['user_name'] . " " . $doctor['user_surname']) ?></td>
                <td><?= htmlspecialchars($doctor['user_username']) ?></td>
                <td><?= htmlspecialchars($doctor['user_phone_number']) ?></td>
                <td><?= htmlspecialchars($doctor['user_email']) ?></td>
                <td>
                    <!-- To know which ID was selected, put it into POST -->
                    <form action="edit_user.php" method="POST">

                        <input type="hidden"
                               name="user_id"
                               value="<?= htmlspecialchars($doctor['user_id']) ?>">

                        <button type="submit">Edit</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<!-- Epidemiologists -->
<h2>Epidemiologists</h2>

<?php if (empty($epidemiologists)): ?>

    <p>No epidemiologists found.</p>

<?php else: ?>

<div class="table-container">

<table class="user-table">

    <thead>
        <tr>
            <th>ID</th>
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
                <td><?= htmlspecialchars($epidemiologist['user_id']) ?></td>
                <td><?= htmlspecialchars($name) ?></td>
                <td><?= htmlspecialchars($epidemiologist['user_username']) ?></td>
                <td><?= htmlspecialchars($epidemiologist['user_phone_number']) ?></td>
                <td><?= htmlspecialchars($epidemiologist['user_email']) ?></td>
                <td>
                    <!-- To know which ID was selected, put it into POST -->
                    <form action="edit_user.php" method="POST">

                        <input type="hidden"
                               name="user_id"
                               value="<?= htmlspecialchars($epidemiologist['user_id']) ?>">

                        <button type="submit">Edit</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<!-- Wards -->
<h2>Wards</h2>

<form action="" method="post">
Add a department: <input type="text" name="department_name"><br>
<input type="submit">
</form>

<?php if (empty($departments)): ?>

    <p>No wards found.</p>

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
        <?php foreach ($departments as $departments): ?>
            <?php $Hospital = $departments['department_id'];
            ?>
            <tr>
                <td><?= htmlspecialchars($departments['department_name']) ?></td>
                <td>
                    <!-- To know which ID was selected, put it into POST -->
                    <form action="edit_department.php" method="POST">
                        <input type="hidden"
                               name="department_id"
                               value="<?= htmlspecialchars($departments['department_id']) ?>">

                        <button type="submit">Edit</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>


</body>
</html>