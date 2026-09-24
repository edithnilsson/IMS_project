<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfTraction - Hamburger Menu</title>
    <link rel="stylesheet" href="hamburgerMenu.css?v=<?php echo time(); ?>">
</head>

<body>

    <div class="dashboard-container">

        <!-- ================= LEFT: SIDEBAR CARD ================= -->
        <aside class="sidebar-card">

            <div class="sidebar-header">
                <a href="homepage.php" class="hamburger" aria-label="Back to Home">
                    <span></span>
                    <span></span>
                    <span></span>
                </a>

                <img src="images/Logo.png"
                     alt="InfTraction Logo"
                     class="sidebar-logo">
            </div>

            <nav class="sidebar-nav">
                <a href="new_case.php">Log a case</a>
                <a href="#">Statistics</a>
                <a href="#">More Announcements</a>
                <a href="new_announcements.php">Write Announcement</a>
            </nav>

            <a href="index.php" class="sidebar-logout">Log Out</a>

        </aside>

        <!-- ================= RIGHT COLUMN ================= -->
        <main class="right-column">

            <div class="graph-section">
                <img src="images/Graph_placeholder.png"
                     alt="Top 3 infections graph"
                     class="graph">
            </div>

            <section class="announcements-section">

                <div class="announcement-pill warning">
                    <img src="images/triangle.png"
                         class="icon"
                         alt="Warning">
                    <span>Warning! New mysterious infection spreading</span>
                </div>

                <div class="announcement-pill reminder">
                    <img src="images/handwash.png"
                         class="icon"
                         alt="Reminder">
                    <span>Do not forget to wash your hands!</span>
                </div>

                <div class="announcement-pill information">
                    <img src="images/home.png"
                         class="icon"
                         alt="Home">
                    <span class="italic">
                        A lot of infections spreading, stay home if you feel ill!
                    </span>
                </div>

            </section>

        </main>

    </div>

</body>
</html>