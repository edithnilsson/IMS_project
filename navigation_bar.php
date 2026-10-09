<?php 

require_once "PrivilegedUser.php"; 

$pages = $_SESSION['privUser']->getPages();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="css/navigation_bar.css">

</head>
<body>

<div class="topnav">
  
  <!-- homepage  -->
  <a href="homepage.php">Home</a>
  
  <!-- loops through the  -->
  <?php foreach ($pages as $desc => $url): ?>
    <a href="<?php echo $url; ?>"><?php echo $desc; ?></a><br>
  <?php endforeach; ?>
  
  <a href="announcements.php">Announcements</a>
  <a href="statistics.php">Statistics</a>
  

  <a href="logout.php" class="split">Log out</a>
</div>


</nav>

</body>
</html>
