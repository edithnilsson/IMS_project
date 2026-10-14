<?php
// checks if the user has permission to enter this page
require __DIR__ . "/../src/authenticate_check.php";

require __DIR__ . "/../config/db.php";   

include __DIR__ . "/../src/navigation_bar.php";

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


$sql = "SELECT Manual_warning.*, System_user.user_name, System_user.user_surname
        FROM Manual_warning
        JOIN System_user ON Manual_warning.user_id = System_user.user_id
        WHERE Manual_warning.hospital_id = ?
        ORDER BY Manual_warning.mwarning_date DESC
        LIMIT 3";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hospital_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();




$mwarning_severity_level = [
    1 =>"green",
    2 =>"yellow",
    3 =>"red"
];

$mwarning_icon = [
    1 => "mask", 
    2 => "hands",
    3 => "warning", 
    4 => "needle",
    5 => "house"
];

?>



<!-- HTML starts -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

<!-- links to the css file -->
    <link rel="stylesheet" href="/assets/css/home.css?v=1">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0">< </script>
</head>
<body>

    <img src="/assets/img/virus_bl_nobg.png" class="virus_bl">
    <img src="/assets/img/virus_tr_nobg.png" class="virus_tr">
    

    <!--sets boarders for webpages-->
<div class="container">    
    <!-- Inserts the logo picture  -->



    <div class="dashboard">
        <!-- Log a case button -->  
        <a href="/cases/register_case.php" style="text-decoration: none;">
            <button class="logcase_button">
            <b>Log a Case</b><br> 
            Click here</button>
        </a>

        <div class="charts">
            <div class="chart-card">
                <h3>Top 3 Pathogens — Daily Cases</h3>
                <canvas id="lineChart"></canvas>
            </div>
        </div>

     </div>



    <div> 
        <div class='header header h1'>   
            <h1> Announcements </h1>
        </div>
        
         <!-- loads the annoucements from database-->
        <?php while ($row = $result->fetch_assoc()): ?>
        
        <!-- Creates an container for the annoucement based on its threat level  -->
        <div class="announcement announcement-<?php echo $mwarning_severity_level[$row['mwarning_severity_level']]; ?>">
        
        <!-- Inserts the chosen icon-->
        
        
            <span 
                class= "icon icon-<?php echo $mwarning_icon[$row['mwarning_icon']]; ?>">
            </span> 
            
        <!-- Announcement message and author -->
<div class="announcement-content">

    <div class="announcement-message">
        <?php echo htmlspecialchars($row['mwarning_message']); ?>
    </div>

    <div class="announcement-author">
        Posted by:
        <?php echo htmlspecialchars($row['user_name'] . ' ' . $row['user_surname']); ?>
    </div>

</div>

        <!-- Writes out the date and time-->
            <span class="date">
                <?php echo ($row['mwarning_date']); ?>
            </span>

        
        </div>
        <br>

        <?php endwhile; ?>

    </div>


</div>

<script>
const ctx = document.getElementById('lineChart');
const statusEl = document.getElementById('status');

const lineChart = new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: { labels: [], datasets: [] },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Cases' } },
            x: { title: { display: true, text: 'Date' } },
        },
    },
});

(function initCasesToggle() {
    const btn       = document.getElementById('toggleCasesBtn');
    const container = document.getElementById('casesContainer');
    if (!btn || !container) return;

    btn.addEventListener('click', () => {
        const isHidden = container.style.display === 'none';
        container.style.display = isHidden ? 'block' : 'none';
        btn.textContent = isHidden ? 'Hide cases' : 'Show all cases';
    });
})();

function buildUrl(endpoint, start, end, pathogenId) {
    const url = new URL(endpoint, location.href);
    if (start)      url.searchParams.set('start_date', start);
    if (end)        url.searchParams.set('end_date', end);
    if (pathogenId) url.searchParams.set('pathogen_id', pathogenId);
    return url;
}

async function fetchJSON(url) {
    const res = await fetch(url);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
}

async function loadLine(start, end, pid) {
    const data = await fetchJSON(
        buildUrl('/api/top_pathogen.php', start, end, pid)
    );
    if (data.error) throw new Error(data.detail || data.error);
    lineChart.data.labels   = data.labels;
    lineChart.data.datasets = data.datasets;
    lineChart.update();
    return data;
}

async function loadAllStats(start, end, pid) {
    try {
        await loadLine(start, end, pid);
    } catch (err) {
        console.error('Failed to load chart:', err);
    }
}

// ---------- Initialise dates ----------

(function loadHomepageChart() {
    const today = new Date();
    const past  = new Date();
    past.setDate(today.getDate() - 29);

    const fmt = d => d.toISOString().slice(0, 10);
    const start = fmt(past);
    const end   = fmt(today);

    loadAllStats(start, end, null);
})();

// No filter form on the homepage, so just show the last 30 days

// First load
loadAllStats(
    document.getElementById('startdate').value,
    document.getElementById('enddate').value,
    null
);
</script>
</body>
</html>