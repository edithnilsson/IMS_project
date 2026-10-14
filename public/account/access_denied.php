<?php 
//check if the user is logged in if not then they are kicked out 
require __DIR__ . "/../../src/authenticate_check.php"; 

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Denied</title>
    <link rel="stylesheet" href="/assets/css/home.css">
</head>
<body>

    <img src="/assets/img/logo_nobackground.png" alt="Logo" width="200">

    <h1>Access Denied</h1>
    <p>You do not have access to this page</p>

    <a href="/home.php" class="button">Back to the home page</a>

</body>
</html>