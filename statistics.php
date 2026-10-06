<?php
// checks if the user has permission to enter this page
require "authenticate_check.php";


require "config/db.php";   

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Load dropdown data -------------------------------------------------
$pathogens = [];
$res = $conn->query("SELECT pathogen_id, pathogen_name FROM Pathogen ORDER BY pathogen_name");
while ($row = $res->fetch_assoc()) $pathogens[] = $row;


// Get the hospital of the logged-in user
$user_id = $_SESSION['user_id'];

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


// Get automatic warnings for this hospital
$automatic_warnings = [];

$sql = "SELECT 
            aw.awarning_id,
            aw.awarning_date,
            aw.awarning_message,
            p.pathogen_name
        FROM Automatic_warning aw
        JOIN Threshold t
            ON aw.threshold_id = t.threshold_id
        JOIN Pathogen p
            ON t.pathogen_id = p.pathogen_id
        WHERE aw.hospital_id = ?
        AND aw.awarning_status = 'active'
        ORDER BY aw.awarning_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hospital_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $automatic_warnings[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Statistics</title>
    <link rel="stylesheet" href="css/statistics.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0"></script>
</head>
<body>

    <?php include 'hamburger_menu.php'; ?>
    
<img src="/Pictures/logo_nobackground.png" class="logo">
<img src="/Pictures/virus_bl_nobg.png" class="virus_bl">
<img src="/Pictures/virus_tr_nobg.png" class="virus_tr">
<img src="/Pictures/rings_br_nobg.png" class="rings_br">

<div class="content">
    <div class="automatic-warnings">
        <h2>Automatic Warnings</h2>

        <table>
            <thead>
                <tr>
                    <th>Pathogen</th>
                    <th>Date</th>
                    <th>Message</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($automatic_warnings)): ?>
                    <tr>
                        <td colspan="4">No active automatic warnings.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($automatic_warnings as $warning): ?>
                        <tr>
                            <td><?= htmlspecialchars($warning['pathogen_name']) ?></td>
                            <td><?= htmlspecialchars(date('Y-m-d', strtotime($warning['awarning_date']))) ?></td>
                            <td><?= htmlspecialchars($warning['awarning_message']) ?></td>
                            <td> <a href="new_announcements.php" class="prepare-button">
                                    Prepare for Publishing
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <form class="inputs" id="filterForm">
        <div class="column">
            <label class="form-label" for="pathogen">
                <span class="icon">
                    <img src="/Pictures/pathogen.icon_bgremoved.png" class="icon">
                </span>
            </label>
            <select id="pathogen" class="pathogen">
                <option value="" disabled selected>Select Pathogen</option>
                    <?php foreach ($pathogens as $p): ?>
                        <option value="<?= (int)$p['pathogen_id'] ?>">
                            <?= htmlspecialchars($p['pathogen_name']) ?>
                    </option>
                    <?php endforeach; ?>
            </select>
        </div>

        <div class="column">
            <label class="form-label">
                <span class="icon">
                    <img src="/Pictures/calender.icon-bgremoved.png" class="icon">
                </span>
            </label>
            <div>
                <div class="date-group">
                    <input type="date" id="startdate" class="startdate" aria-label="Start Date"> -
                    <input type="date" id="enddate"   class="enddate"   aria-label="End Date">
                </div>
                <button type="submit" class="search_button">Search</button>
            </div>
        </div>
    </form>

    <div class="charts">
        <div class="chart-card">
            <h3>Top 3 Pathogens — Daily Cases</h3>
            <canvas id="lineChart"></canvas>
        </div>

        <div class="chart-card">
            <h3>Severity Distribution</h3>
            <canvas id="pieChart"></canvas>
        </div>

        <div class="chart-card">
            <h3>Weekly Case Counts</h3>
            <canvas id="barChart"></canvas>
        </div>
    </div>

<p id="status" style="font-family:sans-serif;color:#555;"></p>
    <p id="status" style="font-family:sans-serif;color:#555;"></p>
</div>

<script>
const statusEl = document.getElementById('status');

// ---------- Chart instances ----------

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

const pieChart = new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
        labels: [],
        datasets: [{
            data: [],
            backgroundColor: ['#27ae60', '#f39c12', '#e74c3c'], // mild, moderate, severe
        }],
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            tooltip: {
                callbacks: {
                    label: (ctx) => {
                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0) || 1;
                        const pct = ((ctx.parsed / total) * 100).toFixed(1);
                        return `${ctx.label}: ${ctx.parsed} (${pct}%)`;
                    },
                },
            },
        },
    },
});

const barChart = new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: [],
        datasets: [{
            label: 'Cases',
            data: [],
            backgroundColor: '#2980b9',
        }],
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Cases' }, ticks: { precision: 0 } },
            x: { title: { display: true, text: 'Week' } },
        },
    },
});

// ---------- Fetch helpers ----------

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

// ---------- Individual loaders ----------

async function loadLine(start, end, pid) {
    const data = await fetchJSON(
        buildUrl('api/top_pathogen.php', start, end, pid)
    );
    if (data.error) throw new Error(data.detail || data.error);
    lineChart.data.labels   = data.labels;
    lineChart.data.datasets = data.datasets;
    lineChart.update();
    return data;
}

async function loadPie(start, end, pid) {
    const data = await fetchJSON(buildUrl('api/severity_distribution.php', start, end, pid));
    if (data.error) throw new Error(data.detail || data.error);
    pieChart.data.labels           = data.labels;
    pieChart.data.datasets[0].data = data.data;
    pieChart.update();
    return data;
}

async function loadBar(start, end, pid) {
    const data = await fetchJSON(buildUrl('api/weekly_cases.php', start, end, pid));
    if (data.error) throw new Error(data.detail || data.error);
    barChart.data.labels           = data.labels;
    barChart.data.datasets[0].data = data.data;
    barChart.update();
    return data;
}

// ---------- Master loader ----------

async function loadAllStats(start, end, pid) {
    statusEl.textContent = 'Loading…';
    try {
        const [line, pie, bar] = await Promise.all([
            loadLine(start, end, pid),
            loadPie(start, end, pid),
            loadBar(start, end, pid),
        ]);
        statusEl.textContent = 'Charts updated.';
    } catch (err) {
        statusEl.textContent = 'Error: ' + err.message;
    }
}

// ---------- Initialise dates ----------

(function initDates() {
    const today = new Date();
    const past  = new Date();
    past.setDate(today.getDate() - 29);

    const fmt = d => d.toISOString().slice(0, 10);
    document.getElementById('enddate').value   = fmt(today);
    document.getElementById('startdate').value = fmt(past);
})();

// ---------- Filter form ----------

document.getElementById('filterForm').addEventListener('submit', e => {
    e.preventDefault();
    const start = document.getElementById('startdate').value;
    const end   = document.getElementById('enddate').value;
    const pid   = document.getElementById('pathogen').value || null;
    loadAllStats(start, end, pid);
});

// First load
loadAllStats(
    document.getElementById('startdate').value,
    document.getElementById('enddate').value,
    null
);
</script>
</body>
</html>