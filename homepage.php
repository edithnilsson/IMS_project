<?php
$servername = "localhost:3333";
$username = "root";
$password = "root";
$dbname = "infection_tracking";

$link = mysqli_connect($servername, $username, $password, $dbname);

if (mysqli_connect_error()) {
    die("Connection failed: " . mysqli_connect_error());
}
// $warning_date = $warning_date . ' ' . date('H:i:s'); 
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

        <!-- Placeholder for graph -->     
        <img src="Pictures/Graph_placeholder.png" alt="Logo" width="750" height="453" >
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

</body>
</html>