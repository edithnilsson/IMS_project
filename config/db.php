<?php
/*
 Adjust MAMP settings to match your setup
  MAMP > Preferences > Ports
  Go to this webpage to see http://localhost/MAMP/?language=English and click on SQL for the correct passwords
 */ 
 
$servername = "127.0.0.1"; /* Currently this works because it forces the connection with TCP and later picks the variable port*/
$username = "root"; 
$password = "root";
$dbname = "infection_tracking";
$port = 3333; /* This variable is not needed if you change servername for "localhost:3333" */ 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>