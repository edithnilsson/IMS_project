<?php 

require_once __DIR__ . "/PrivilegedUser.php"; 

$pages = $_SESSION['privUser']->getPages();
?>

    <link rel="stylesheet" href="/css/navigation_bar.css">

<div class="topnav">
    <img src="Pictures/logo_navbar.png" class="navlogo" alt="Logo">

    <a href="homepage.php">Home</a>

    <?php foreach ($pages as $desc => $url): ?>
        <a href="<?php echo htmlspecialchars($url); ?>">
            <?php echo htmlspecialchars($desc); ?>
        </a>
    <?php endforeach; ?>

    <a href="announcements.php">Announcements</a>
    <a href="statistics.php">Statistics</a>

    <div class="rightside">
        <div class="row">
            <img src="Pictures/patient.icon-bgremoved.png"
                 class="navicon" alt="">
            <a href="user_page.php">Profile</a>
        </div>

        <a href="logout.php">Log out</a>
    </div>
</div>