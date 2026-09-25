<?php
$servername = "localhost:3333";
$username = "root";
$password = "root";
$dbname = "infection_tracking";

$link = mysqli_connect($servername, $username, $password, $dbname);

if (mysqli_connect_error()) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql="SELECT mwarning_message, mwarning_severity_level, mwarning_icon, mwarning_date 
FROM Manual_warning 
ORDER BY mwarning_date DESC   
LIMIT 3";
$result = $link->query($sql);


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
    5 => "home"
]



?>



<!-- HTML starts -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

<!-- links to the css file -->
    <link rel="stylesheet" href="test.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0">< </script>
</head>
<body>


 <!-- Inserts the logo picture  -->
 <img src="Pictures/logo_nobackground.png" alt="Logo" width="300" height="133" >
 
 <!-- Log out button  -->
 <a href="index.php">
    <button class="logout_button logout_button_hover">
        Log out
    </button>

<!--sets boarders for webpages-->
<div class="container">

    <div class="dashboard">
        <!-- Log a case button -->  
        <a href="new_case.php" style="text-decoration: none;">
            <button class="logcase_button">
            <b>Log a Case</b><br> 
            Click here</button>
        </a>

        <div class="graph-section">
            <canvas id="myChart" style="width:100%;max-width:600px;height:200px;"></canvas>
            <p id="status" style="font-family:sans-serif;color:#555;"></p>
        </div>
     </div>



    <div> 
         <!-- loads the annoucements from database-->
        <?php while ($row = $result->fetch_assoc()): ?>
        
        <!-- Creates an container for the annoucement based on its threat level  -->
        <div class="announcement announcement-<?php echo $mwarning_severity_level[$row['mwarning_severity_level']]; ?>">
             
        
        <!-- Inserts the chosen icon-->
            <span 
                class= "icon icon-<?php echo $mwarning_icon[$row['mwarning_icon']]; ?>">
            </span>
            
        <!-- Writes the message-->
            <?php echo ($row['mwarning_message']); ?>

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
const ctx = document.getElementById('myChart');
const statusEl = document.getElementById('status');

const chart = new Chart(ctx, {
    type: 'line',
    data: { labels: [], datasets: [] },
    options: {
        responsive: true,
        plugins: { legend: { display: true, position: 'bottom' } },
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

// No filter form on the homepage, so just show the last 30 days
loadStats();
</script>
</body>
</html>