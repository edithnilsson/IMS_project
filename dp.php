<?php
/*
 Adjust MAMP settings to match your setup
  MAMP > Preferences > Ports
  Go to this webpage to see http://localhost/MAMP/?language=English and click on SQL for the correct passwords
 */ 
 
$servername = "localhost";
$username = "root"; 
$password = "root";
$dbname = "code grading";
$port = 8888;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>