<?php
// checks if the user has permission to enter this page
require "authenticate_check.php";
access(['doctor', 'epidemiologist', 'admin']);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Design Idea</title>
    <link rel="stylesheet" href="css/statistics.css">    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0"></script>
</head>
<body style="background: linear-gradient(90deg, #daf8e0, #c9ebfd);">
   <?php include 'hamburger_menu.php'; ?>
<img src="/Pictures/logo_nobackground.png" class= "logo">
<img src="/Pictures/virus_bl_nobg.png" class="virus_bl">
<img src="/Pictures/virus_tr_nobg.png" class="virus_tr">
<img src="/Pictures/rings_br_nobg.png" class="rings_br">
<div class="content">
        <form method= "GET" class="inputs">
            <div class="column">
            <label class="form-label" for="pathogen">
     
                <span class="icon"><img src="/Pictures/pathogen.icon_bgremoved.png" class="icon"></span> </label>
                <select id="pathogen" class="pathogen">
                    <option value="" disabled selected>Select Pathogen</option>
                    <option value="covid">COVID-19</option>
                    <option value="flu">Influenza</option>
                </select>
            </div>
            <div class="column">
            <label class="form-label">
                <span class="icon"><img src="/Pictures/calender.icon-bgremoved.png" class="icon"></span>
            </label>
            <div>
            <div class="date-group">
                <input type="date" class="startdate" placeholder="Start Date" aria-label="Day"> -
                <input type="date" class="enddate" placeholder="End Date" aria-label="Month">
            </div>
            <button type="submit" class="search_button" value="Search">
            Search
            </button>
            </div>
            </div>
</form>
        
<canvas id="myChart" style="width:100%;max-width:600px"></canvas>

<script>
const xValues = ["monday","tuesday","wednesday","thursday","friday","saturday","sunday"];

const ctx = document.getElementById('myChart');

new Chart(ctx, {
  type: "line",
  data: {
    labels: xValues,
    datasets: [{ 
      data: [4,6,7,9,6,7,4],
      borderColor: "red",
      pointRadius: 2,
      tension: 0.4,
    }, { 
      data: [8,6,4,2,7,9,10],
      borderColor: "green",
      pointRadius: 2,
      tension: 0.4,
    }, { 
      data: [0,0,6,7,8,6,0],
      borderColor: "blue",
      pointRadius: 2,
      tension: 0.4,
    }]
  },
  options: {
    plugins: {
      legend: { display: false }
    }
  }
});
</script>

</div>
</body>