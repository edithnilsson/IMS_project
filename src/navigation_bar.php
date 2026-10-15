<?php 

require_once __DIR__ . "/PrivilegedUser.php"; 

$pages = $_SESSION['privUser']->getPages();
?>

    <link rel="stylesheet" href="/assets/css/navigation_bar.css?v=2">

<div class="topnav">
    <img src="/assets/img/logo_navbar.png" class="navlogo" alt="Logo">

    <a href="/home.php">Home</a>

    <?php foreach ($pages as $desc => $url): ?>
        <a href="<?php echo htmlspecialchars($url); ?>">
            <?php echo htmlspecialchars($desc); ?>
        </a>
    <?php endforeach; ?>

    <a href="/announcements/announcement_history.php">Announcements</a>
    <a href="/statistics.php">Statistics</a>

    <div class="rightside">
        <div class="row">
            <img src="/assets/img/patient.icon-bgremoved.png"
                 class="navicon" alt="">
            <a href="/account/profile.php">Profile</a>
        </div>

        <a href="/account/logout.php">LogOut</a>
    </div>
</div>