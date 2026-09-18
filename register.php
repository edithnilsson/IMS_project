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

    if (empty($username)){
        echo "Username can not be empty";
    } elseif (empty($password)){
        echo "Password can not be empty";
    } elseif (!preg_match("/^[A-Za-z0-9]+$/", $username)){ // ^ marks start of string, $ marks end of string, + go through the string one index at a time
        echo "Username can only contain letters and numbers";
    } elseif (
        strlen($password) <8 || //At least 8 characters long
        !preg_match("/[A-Z]/", $password) || //At least 1 uppercase letter
        !preg_match("/[a-z]/", $password) || //At least 1 lowercase letter
        !preg_match("/[0-9]/", $password) || //At least 1 number
        !preg_match('/[^A-Za-z0-9]/', $password) //At least 1 special character
    ) {
        echo "Password must be at least 8 characters and include an uppercase letter, a lowercase letter, a number, and a special character";
    } else {
        //PASSWORD_DEFAULT uses bcrypt algo + generates random salt
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
           // Just for testing, echos can be removed
        echo "Username: " . $username; 
        echo "Password: " . $password;
        echo "Hashed password: " . $hashed_password;
    }

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
        Username: <input type="text" name="username">

        Password: <input type=password name="password">

        <input type="submit" value="Register" class="button">
    </form>

</body>
</html>