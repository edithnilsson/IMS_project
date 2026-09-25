<!DOCTYPE html>
<html>
<head>
    <title>Statistics</title>
    <link rel="stylesheet" href="css/statistics.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0"></script>
</head>
<body style="background: linear-gradient(90deg, #daf8e0, #c9ebfd);">

<img src="/Pictures/logo_nobackground.png" class="logo">
<img src="/Pictures/virus_bl_nobg.png" class="virus_bl">
<img src="/Pictures/virus_tr_nobg.png" class="virus_tr">
<img src="/Pictures/rings_br_nobg.png" class="rings_br">

<div class="content">
    <form class="inputs" id="filterForm">
        <div class="column">
            <label class="form-label" for="pathogen">
                <span class="icon">
                    <img src="/Pictures/pathogen.icon_bgremoved.png" class="icon">
                </span>
            </label>
            <select id="pathogen" class="pathogen">
                <option value="" disabled selected>Select Pathogen</option>
                <option value="covid">COVID-19</option>
                <option value="flu">Influenza</option>
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

    <canvas id="myChart" style="width:100%;max-width:600px"></canvas>
    <p id="status" style="font-family:sans-serif;color:#555;"></p>
</div>

<script>
const ctx = document.getElementById('myChart');
const statusEl = document.getElementById('status');

// The idea is to make a single graph where data refreshes in the graph
const chart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [],
        datasets: []
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true, position: 'bottom' }
        },
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Cases' } },
            x: { title: { display: true, text: 'Date' } }
        }
    }
});

async function loadStats(start, end) {
    statusEl.textContent = 'Loading…';

    const url = new URL('api/statistics_top_pathogen.php', location.href);
    if (start) url.searchParams.set('start_date', start);
    if (end)   url.searchParams.set('end_date', end);

    // This is for the message that displays next to the graph 
    try {
        const res  = await fetch(url);
        const data = await res.json();

        if (data.error) {
            statusEl.textContent = 'API error: ' + (data.detail || data.error);
            return;
        }

        if (!data.labels.length) {
            statusEl.textContent = 'No cases found in this date range.';
        } else {
            statusEl.textContent = `Showing ${data.datasets.length} pathogen(s) from ${data.labels[0]} to ${data.labels[data.labels.length - 1]}.`;
        }

        chart.data.labels   = data.labels;
        chart.data.datasets = data.datasets;
        chart.update();
    } catch (err) {
        statusEl.textContent = 'Network error: ' + err.message;
    }
}

// Fills the date inputs to 30 days by default
(function initDates() {
    const today = new Date();
    const past  = new Date();
    past.setDate(today.getDate() - 29);

    const fmt = d => d.toISOString().slice(0, 10); // yyyy-mm-dd
    document.getElementById('enddate').value   = fmt(today);
    document.getElementById('startdate').value = fmt(past);
})();

// Search button logic
document.getElementById('filterForm').addEventListener('submit', e => {
    e.preventDefault();
    const start = document.getElementById('startdate').value;
    const end   = document.getElementById('enddate').value;
    loadStats(start, end);
});

// By default it will display the last 30 days 
loadStats();
</script>
</body>
</html>