<?php
// checks if the user has permission to enter this page
require "authenticate_check.php";

include "navigation_bar.php";
require "config/db.php";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST"
    && ($_POST['action'] ?? '') == 'dismiss'
    && $_SESSION['privUser']->hasPrivilege('write_announcement')) {

    $awarning_id = $_POST['awarning_id'];

    $sql = "UPDATE Automatic_warning
            SET awarning_status = 'dismissed'
            WHERE awarning_id = ?
            AND awarning_status = 'active'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $awarning_id);
    $stmt->execute();
    $stmt->close();
}

// Populate pathogen dropdown
$pathogens = [];
$res = $conn->query("SELECT pathogen_id, pathogen_name FROM Pathogen ORDER BY pathogen_name");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $pathogens[] = $row;
    }
}

// Check if the user can write announcements
$can_write_announcement =
    $_SESSION['privUser']->hasPrivilege('Write an announcement');
$automatic_warnings = [];

if ($can_write_announcement) {

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

    // Get active automatic warnings for this hospital
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
}

$infection_cases = [];

// Get the current user's hospital (safe even if $hospital_id was not set above)
if (!isset($hospital_id)) {
    $sql = "SELECT hospital_id FROM System_user WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_SESSION['user_id']);
    $stmt->execute();
    $hospital_id = $stmt->get_result()->fetch_assoc()['hospital_id'] ?? null;
    $stmt->close();
}

if (!empty($hospital_id)) {
    $sql = "
        SELECT
            ic.case_id,
            ic.case_infection_date,
            ic.case_diagnosis_date,
            ic.case_comments,
            p.pathogen_name,
            s.severity_name,
            d.department_name,
            u.user_name,
            u.user_surname
        FROM Infection_case ic
        JOIN Pathogen p       ON ic.pathogen_id   = p.pathogen_id
        JOIN Severity_level s ON ic.severity_id   = s.severity_id
        JOIN Department d     ON ic.department_id = d.department_id
        JOIN System_user u    ON ic.user_id       = u.user_id
        WHERE ic.hospital_id = ?
        ORDER BY ic.case_infection_date DESC
        LIMIT 500
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $hospital_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $infection_cases[] = $row;
    }
    $stmt->close();
}

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

<?php if ($can_write_announcement): ?>
    <div class="automatic-warnings">
        <h2>Automatic Warnings</h2>
        <?php if (empty($automatic_warnings)): ?>
            <p>No active automatic warnings.</p>
        <?php else: ?>
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
                    <?php foreach ($automatic_warnings as $warning): ?>
                        <tr>
                            <td><?= htmlspecialchars($warning['pathogen_name']) ?></td>
                            <td><?= htmlspecialchars(date('Y-m-d', strtotime($warning['awarning_date']))) ?></td>
                            <td><?= htmlspecialchars($warning['awarning_message']) ?></td>
                            <td>
                                <form action="new_announcements.php" method="POST">
                                    <input type="hidden"
                                           name="action"
                                           value="prepare">

                                    <input type="hidden"
                                           name="awarning_id"
                                           value="<?= htmlspecialchars($warning['awarning_id']) ?>">

                                    <button type="submit" class="prepare-button">
                                        Prepare for Publishing
                                    </button>
                                </form>

                                <form action="statistics.php" method="POST">
                                    <input type="hidden"
                                           name="action"
                                           value="dismiss">

                                    <input type="hidden"
                                           name="awarning_id"
                                           value="<?= htmlspecialchars($warning['awarning_id']) ?>">

                                    <button type="submit" class="dismiss-button">
                                        Dismiss
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php endif; ?>
    <div class="cases-section">
        <button type="button" id="toggleCasesBtn" class="toggle-cases-button">
            Show all cases
        </button>

        <div id="casesContainer" class="cases-container" style="display: none;">
            <h2>All Infection Cases</h2>

            <?php if (empty($infection_cases)): ?>
                <p class="empty">No infection cases recorded.</p>
            <?php else: ?>
                <table class="cases-table">
                    <thead>
                        <tr>
                            <th>Infection date</th>
                            <th>Diagnosis date</th>
                            <th>Department</th>
                            <th>Pathogen</th>
                            <th>Severity</th>
                            <th>Reported by</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($infection_cases as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['case_infection_date']) ?></td>
                                <td><?= htmlspecialchars($c['case_diagnosis_date']) ?></td>
                                <td><?= htmlspecialchars($c['department_name']) ?></td>
                                <td><?= htmlspecialchars($c['pathogen_name']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($c['severity_name'])) ?></td>
                                <td>
                                    <?= htmlspecialchars(
                                        trim($c['user_name'] . ' ' . $c['user_surname'])
                                    ) ?>
                                </td>
                                <td>
                                    <?= $c['case_comments']
                                        ? htmlspecialchars($c['case_comments'])
                                        : '—' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
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

// ---------- Toggle the infection cases table ----------
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