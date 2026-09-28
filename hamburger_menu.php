<link rel="stylesheet" href="css/hamburger_menu.css?v=2">

<aside class="sidebar-card closed" id="sidebar">

    <div class="sidebar-header">

        <button type="button" class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <img
            src="Pictures/Logo.png"
            alt="InfTraction Logo"
            class="sidebar-logo">

    </div>

    <nav class="sidebar-nav">
        <a href="homepage.php">Home</a>
        <a href="new_case.php">Log a case</a>
        <a href="statistics.php">Statistics</a>
        <a href="new_announcements.php">Write Announcement</a>
    </nav>

    <a href="logout.php" class="sidebar-logout">
        Log Out
    </a>

</aside>

<script>
    const hamburger = document.getElementById("hamburger");
    const sidebar = document.getElementById("sidebar");

    hamburger.addEventListener("click", function () {
        sidebar.classList.toggle("closed");
    });
</script>