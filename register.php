<?php
/*
Register page will let a new user register its own account. 
password_hash() creates a new password hash with a random generated salt
ID and the hashed password will be stored in the database
*/

//require "db.php";

//If form was submitted (user registered), insert username and password into the database
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    //PASSWORD_DEFAULT uses bcrypt algo + generates random salt
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Just for testing, echos can be removed
    echo "Username: " . $username; 
    echo "Password: " . $password;
    echo "Hashed password: " . $hashed_password;

 /* Add this codeblock when database is up + change to the database name

    $sql = "INSERT into PLACE_dbname_HOLDER (username, password) values (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $hashed_password);
    $result = $stmt->execute();

    if ($result) {
        echo "Successfully registered";
    } else {
        echo "Registration failed";
    }
*/
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/register.css">
    <title>InfecTractious Register</title>
</head>
<body>

    <img src="images/logo.png" class="logo">

    <form action="register.php" method="POST" class="inputs">
        Username: <input type="text" name="username" required>

        Password: <input type=password name="password" required>

        <input type="submit" value="Register" class="button">
    </form>

</body>
</html>