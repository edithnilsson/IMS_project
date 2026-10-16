<?php
require __DIR__ . "/../../src/authenticate_check.php";
access('Other Hospitals');

require __DIR__ . "/../../config/db.php";
include __DIR__ . "/../../src/navigation_bar.php";

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Populate filters
$hospitals = [];
$res = $conn->query("SELECT hospital_id, hospital_name FROM Hospital ORDER BY hospital_name");
while ($row = $res->fetch_assoc()) $hospitals[] = $row;

$pathogens = [];
$res = $conn->query("SELECT pathogen_id, pathogen_name FROM Pathogen ORDER BY pathogen_name");
while ($row = $res->fetch_assoc()) $pathogens[] = $row;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross-Hospital Cases</title>
    <link rel="stylesheet" href="/assets/css/cross_hospital_cases.css?v=698">
</head>
<body>

<img src="/assets/img/virus_bl_nobg.png" class="virus_bl">
<img src="/assets/img/rings_br_nobg.png" class="rings_br">

<h1 class='title'>Cases Across Hospitals</h1>
<p class='subs'>
        Aggregate case counts per hospital and pathogen.
        This page is restricted to epidemiologists and administrators.
</p>

<form class="filters" id="filterForm">
    <div class="filter">
        <label for="hospital">Hospital</label>
        <select id="hospital">
            <option value="">All hospitals</option>
            <?php foreach ($hospitals as $h): ?>
                <option value="<?= htmlspecialchars($h['hospital_id']) ?>">
                    <?= htmlspecialchars($h['hospital_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filter">
        <label for="pathogen">Pathogen</label>
        <select id="pathogen">
            <option value="">All pathogens</option>
            <?php foreach ($pathogens as $p): ?>
                <option value="<?= (int)$p['pathogen_id'] ?>">
                    <?= htmlspecialchars($p['pathogen_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filter">
        <label for="startdate">Start date</label>
        <input type="date" id="startdate">
    </div>

    <div class="filter">
        <label for="enddate">End date</label>
        <input type="date" id="enddate">
    </div>

    <div class="filter filter-actions">
        <button type="submit">Apply filters</button>
        <button type="button" id="resetBtn">Reset</button>
    </div>
</form>

<p id="status" class="status"></p>

<div class="table-wrapper">
    <table id="casesTable">
        <thead>
            <tr>
                <th>Hospital</th>
                <th>Pathogen</th>
                <th class="num">Cases</th>
                <th>Chief epidemiologist</th>
                <th>Contact</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <tr><td colspan="5" class="empty">Loading…</td></tr>
        </tbody>
    </table>
</div>

<script>
const tableBody = document.getElementById('tableBody');
const statusEl  = document.getElementById('status');

function buildUrl() {
    const url = new URL('/api/cross_hospital_cases.php', location.href);
    const h  = document.getElementById('hospital').value;
    const p  = document.getElementById('pathogen').value;
    const s  = document.getElementById('startdate').value;
    const e  = document.getElementById('enddate').value;

    if (h) url.searchParams.set('hospital_id', h);
    if (p) url.searchParams.set('pathogen_id', p);
    if (s) url.searchParams.set('start_date', s);
    if (e) url.searchParams.set('end_date', e);
    return url;
}

function renderRows(rows) {
    if (!rows.length) {
        tableBody.innerHTML = '<tr><td colspan="5" class="empty">No data for the selected filters.</td></tr>';
        return;
    }

    tableBody.innerHTML = rows.map(r => `
        <tr>
            <td>${escapeHtml(r.hospital_name)}</td>
            <td>${escapeHtml(r.pathogen_name)}</td>
            <td class="num ${r.cases === 0 ? 'zero' : ''}">${r.cases}</td>
            <td>${escapeHtml(r.chief_name)}</td>
            <td>
                <div class="contact">${escapeHtml(r.chief_email)}</div>
                <div class="contact">${escapeHtml(r.chief_phone)}</div>
            </td>
        </tr>
    `).join('');
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

async function loadTable() {
    statusEl.textContent = 'Loading…';
    try {
        const res  = await fetch(buildUrl());
        const data = await res.json();

        if (!res.ok || data.error) {
            statusEl.textContent = 'Error: ' + (data.detail || data.error || res.status);
            tableBody.innerHTML = '<tr><td colspan="5" class="empty">Failed to load.</td></tr>';
            return;
        }

        const rows = data.rows ?? [];
        renderRows(rows);

        const total = rows.reduce((sum, r) => sum + r.cases, 0);
        statusEl.textContent =
            `${rows.length} row(s) — ${total} case(s) in the selected range.`;
    } catch (err) {
        statusEl.textContent = 'Network error: ' + err.message;
        tableBody.innerHTML = '<tr><td colspan="5" class="empty">Failed to load.</td></tr>';
    }
}

// Default date range: last 30 days
(function initDates() {
    const today = new Date();
    const past  = new Date();
    past.setDate(today.getDate() - 29);
    const fmt = d => d.toISOString().slice(0, 10);
    document.getElementById('enddate').value   = fmt(today);
    document.getElementById('startdate').value = fmt(past);
})();

document.getElementById('filterForm').addEventListener('submit', e => {
    e.preventDefault();
    loadTable();
});

document.getElementById('resetBtn').addEventListener('click', () => {
    document.getElementById('hospital').value = '';
    document.getElementById('pathogen').value = '';
    initDates();
    loadTable();
});

// First load
loadTable();
</script>

</body>
</html>