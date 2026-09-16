<?php
// code from L4
$servername = "localhost";
# go to this webpage to see http://localhost/MAMP/?language=English and click on SQL for the corretc passwords
$username = "root"; 
$password = "root";
$dbname = "code grading";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
die("Connection failed: " . $conn->connect_error);
}
# Commenting if successful, not necessary after a while
#echo " <br> Connected successfully :)";
?>