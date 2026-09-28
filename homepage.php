<?php
// checks if the user has permission to enter this page
require "authenticate_check.php";
access(['doctor', 'epidemiologist', 'admin']);

require "config/db.php";

$sql="SELECT mwarning_message, mwarning_severity_level, mwarning_icon, mwarning_date 
FROM Manual_warning 
ORDER BY mwarning_date DESC   
LIMIT 3";

$result = $conn->query($sql);


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
    <link rel="stylesheet" href="css/homepage.css">

</head>
<body>

<?php include 'hamburger_menu.php'; ?>

 <!-- Inserts the logo picture  -->
 
 
 


<!--sets boarders for webpages-->
<div class="container">
    <img src="Pictures/logo_nobackground.png" alt="Logo" width="500" height="233"  >
    <img src="Pictures/virus_bl_nobg.png" class="virus_bl">
    <img src="Pictures/virus_tr_nobg.png" class="virus_tr">



    <div class="dashboard">
        <!-- Log a case button -->  
        <a href="new_case.php" style="text-decoration: none;">
            <button class="logcase_button">
            <b>Log a Case</b><br> 
            Click here</button>
        </a>

        <!-- Placeholder for graph -->     
        <img src="Pictures/Graph_placeholder.png" alt="Logo" width="750" height="453" >
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

</body>
</html>