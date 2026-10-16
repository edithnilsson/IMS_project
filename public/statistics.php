<?php
// checks if the user has permission to enter this page
require __DIR__ . "/../src/authenticate_check.php";
require __DIR__ . "/../config/db.php";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST"
    && ($_POST['action'] ?? '') == 'dismiss'
    && $_SESSION['privUser']->hasPrivilege('Write Announcement')) {

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
    $_SESSION['privUser']->hasPrivilege('Write Announcement');
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

// -----------------------------------------------------------------
// Infection cases: filtering + pagination
// -----------------------------------------------------------------
$infection_cases = [];
$total_cases     = 0;
$total_pages     = 1;
$current_page    = 1;
$per_page        = 10;

// Read filters from the query string
$filter_department = isset($_GET['case_department']) && $_GET['case_department'] !== ''
                     ? (int)$_GET['case_department'] : null;
$filter_pathogen   = isset($_GET['case_pathogen']) && $_GET['case_pathogen'] !== ''
                     ? (int)$_GET['case_pathogen'] : null;
$filter_severity   = isset($_GET['case_severity']) && $_GET['case_severity'] !== ''
                     ? (int)$_GET['case_severity'] : null;
$filter_start      = $_GET['case_start'] ?? '';
$filter_end        = $_GET['case_end']   ?? '';

$current_page = isset($_GET['case_page']) && (int)$_GET['case_page'] > 0
              ? (int)$_GET['case_page'] : 1;

// Ensure the user has a hospital (already fetched above)
if (!empty($hospital_id)) {

    // -------- Build WHERE clause --------
    $where  = ['ic.hospital_id = ?'];
    $params = [$hospital_id];
    $types  = 's';

    if ($filter_department !== null) {
        $where[]  = 'ic.department_id = ?';
        $params[] = $filter_department;
        $types   .= 'i';
    }
    if ($filter_pathogen !== null) {
        $where[]  = 'ic.pathogen_id = ?';
        $params[] = $filter_pathogen;
        $types   .= 'i';
    }
    if ($filter_severity !== null) {
        $where[]  = 'ic.severity_id = ?';
        $params[] = $filter_severity;
        $types   .= 'i';
    }
    if ($filter_start !== '') {
        $where[]  = 'ic.case_infection_date >= ?';
        $params[] = $filter_start;
        $types   .= 's';
    }
    if ($filter_end !== '') {
        $where[]  = 'ic.case_infection_date <= ?';
        $params[] = $filter_end;
        $types   .= 's';
    }

    $whereSql = implode(' AND ', $where);

    // -------- Count total rows --------
    $countSql = "SELECT COUNT(*) AS total
                 FROM Infection_case ic
                 WHERE $whereSql";
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total_cases = (int)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    $total_pages = max(1, (int)ceil($total_cases / $per_page));
    if ($current_page > $total_pages) {
        $current_page = $total_pages;
    }
    $offset = ($current_page - 1) * $per_page;

    // -------- Fetch the page --------
    $dataSql = "
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
        WHERE $whereSql
        ORDER BY ic.case_infection_date DESC
        LIMIT ? OFFSET ?
    ";

    $dataTypes  = $types . 'ii';
    $dataParams = array_merge($params, [$per_page, $offset]);

    $stmt = $conn->prepare($dataSql);
    $stmt->bind_param($dataTypes, ...$dataParams);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $infection_cases[] = $row;
    }
    $stmt->close();
}

// Dropdown data for the case filters
$case_departments = [];
$res = $conn->query("SELECT department_id, department_name
                     FROM Department
                     WHERE hospital_id = '$hospital_id'
                     ORDER BY department_name");
while ($row = $res->fetch_assoc()) $case_departments[] = $row;

$case_severities = [];
$res = $conn->query("SELECT severity_id, severity_name FROM Severity_level ORDER BY severity_risk_score");
while ($row = $res->fetch_assoc()) $case_severities[] = $row;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Statistics</title>
    <link rel="stylesheet" href="/assets/css/statistics.css?v=193">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0"></script>
</head>
<body>
 
<?php include __DIR__ . "/../src/navigation_bar.php"; ?>

<img src="/assets/img/virus_bl_nobg.png" class="virus_bl">
<img src="/assets/img/virus_tr_nobg.png" class="virus_tr">

<div class="stats-content">
<div class="stats-grid">

    <!-- Column 1: warnings + filter -->
    <div class="stats-left">
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
                                <form action="/announcements/write_announcement.php" method="POST">
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

                                <form action="/statistics.php" method="POST">
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
    <form class="inputs" id="filterForm">
        <div class="column">
            <label class="form-label" for="pathogen">
                <span class="icon">
                    <img src="/assets/img/pathogen.icon_bgremoved.png" class="icon">
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
                    <img src="/assets/img/calender.icon-bgremoved.png" class="cicon">
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
    </div><!-- /stats-left -->

    <!-- Column 2: big line chart -->
    <div class="chart-card chart-main">
        <canvas id="lineChart"></canvas>
    </div>

    <!-- Column 3: pie on top of bar -->
    <div class="stats-right">
        <div class="chart-card">
            <h3 class="charthead">Severity Distribution</h3>
            <canvas id="pieChart"></canvas>
        </div>

        <div class="chart-card">
            <h3 class="charthead">Weekly Case Counts</h3>
            <canvas id="barChart"></canvas>
        </div>
    </div>

</div><!-- /stats-grid -->

    <div class="cases-section">
    <h2>All Infection Cases</h2>

    <!-- ---------------- Filters ---------------- -->
    <form method="GET" class="cases-filters" id="casesFilterForm">
        <div class="cases-filter">
            <label for="case_department">Department</label>
            <select name="case_department" id="case_department">
                <option value="">All departments</option>
                <?php foreach ($case_departments as $d): ?>
                    <option value="<?= (int)$d['department_id'] ?>"
                        <?= $filter_department === (int)$d['department_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['department_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="cases-filter">
            <label for="case_pathogen">Pathogen</label>
            <select name="case_pathogen" id="case_pathogen">
                <option value="">All pathogens</option>
                <?php foreach ($pathogens as $p): ?>
                    <option value="<?= (int)$p['pathogen_id'] ?>"
                        <?= $filter_pathogen === (int)$p['pathogen_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['pathogen_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="cases-filter">
            <label for="case_severity">Severity</label>
            <select name="case_severity" id="case_severity">
                <option value="">All severities</option>
                <?php foreach ($case_severities as $s): ?>
                    <option value="<?= (int)$s['severity_id'] ?>"
                        <?= $filter_severity === (int)$s['severity_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst($s['severity_name'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="cases-filter">
            <label for="case_start">From</label>
            <input type="date" name="case_start" id="case_start"
                   value="<?= htmlspecialchars($filter_start) ?>">
        </div>

        <div class="cases-filter">
            <label for="case_end">To</label>
            <input type="date" name="case_end" id="case_end"
                   value="<?= htmlspecialchars($filter_end) ?>">
        </div>

        <div class="cases-filter cases-filter-actions">
            <button type="submit">Apply</button>
            <a class="cases-reset" href="/statistics.php#cases-section">Reset</a>
        </div>
    </form>

    <!-- ---------------- Table ---------------- -->
    <div class="cases-container" id="cases-section">
        <?php if (empty($infection_cases)): ?>
            <p class="empty">No infection cases match the selected filters.</p>
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
                            <td><?= htmlspecialchars(trim($c['user_name'] . ' ' . $c['user_surname'])) ?></td>
                            <td><?= $c['case_comments'] ? htmlspecialchars($c['case_comments']) : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- ---------------- Pagination ---------------- -->
    <?php if ($total_pages > 1): ?>
        <?php
        // Preserve existing filters in pagination links
        $baseParams = $_GET;
        unset($baseParams['case_page']);
        $baseQuery = http_build_query($baseParams);
        ?>
        <nav class="cases-pagination" aria-label="Case pages">
            <?php if ($current_page > 1): ?>
                <a class="page-link"
                   href="?<?= htmlspecialchars($baseQuery) ?>&case_page=<?= $current_page - 1 ?>#cases-section">
                    &larr; Previous
                </a>
            <?php endif; ?>

            <?php
            // Show up to 5 page numbers around the current page
            $start = max(1, $current_page - 2);
            $end   = min($total_pages, $current_page + 2);
            for ($i = $start; $i <= $end; $i++):
            ?>
                <a class="page-link <?= $i === $current_page ? 'active' : '' ?>"
                   href="?<?= htmlspecialchars($baseQuery) ?>&case_page=<?= $i ?>#cases-section">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
                <a class="page-link"
                   href="?<?= htmlspecialchars($baseQuery) ?>&case_page=<?= $current_page + 1 ?>#cases-section">
                    Next &rarr;
                </a>
            <?php endif; ?>

            <span class="page-summary">
                Showing <?= count($infection_cases) ?> of <?= $total_cases ?> cases
            </span>
        </nav>
    <?php elseif ($total_cases > 0): ?>
        <p class="page-summary">
            Showing <?= $total_cases ?> case<?= $total_cases === 1 ? '' : 's' ?>
        </p>
    <?php endif; ?>
    </div><!-- /cases-section -->

    <p id="status"></p>
</div><!-- /stats-content -->

<script>
const statusEl = document.getElementById('status');

// ---------- Chart instances ----------

const lineChart = new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: { labels: [], datasets: [] },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, boxHeight: 8, padding: 20, generateLabels: c => Chart.defaults.plugins.legend.labels.generateLabels(c).map(l => ({ ...l, fillStyle: l.strokeStyle, lineWidth: 0 })) } } },  
        scales: {
        y: {beginAtZero: true, ticks: {precision: 0, stepSize: 1},grid: {display: false}},
        x: {grid: {display: false}}
            }}
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
        maintainAspectRatio: false,
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
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
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
        buildUrl('/api/top_pathogen.php', start, end, pid)
    );
    if (data.error) throw new Error(data.detail || data.error);
    lineChart.data.labels   = data.labels;
    lineChart.data.datasets = data.datasets;
    lineChart.update();
    return data;
}

async function loadPie(start, end, pid) {
    const data = await fetchJSON(buildUrl('/api/severity_distribution.php', start, end, pid));
    if (data.error) throw new Error(data.detail || data.error);
    pieChart.data.labels           = data.labels;
    pieChart.data.datasets[0].data = data.data;
    pieChart.update();
    return data;
}

async function loadBar(start, end, pid) {
    const data = await fetchJSON(buildUrl('/api/weekly_cases.php', start, end, pid));
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