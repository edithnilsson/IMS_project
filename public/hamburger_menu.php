<link rel="stylesheet" href="/assets/css/hamburger_menu.css?v=2">

<aside class="sidebar-card closed" id="sidebar">

    <div class="sidebar-header">

        <button type="button" class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <img
            src="/assets/img/Logo.png"
            alt="InfTraction Logo"
            class="sidebar-logo">

    </div>

    <nav class="sidebar-nav">
        <a href="/home.php">Home</a>
        <a href="/cases/register_case.php">Log a case</a>
        <a href="/statistics.php">Statistics</a>
        <a href="/announcements/write_announcement.php">Write Announcement</a>
        <a href="/announcements/announcement_history.php">Announcements</a>
        <a href="/cases/cross_hospital_cases.php">Other hospitals</a>
    </nav>

    <a href="/account/logout.php" class="sidebar-logout">
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