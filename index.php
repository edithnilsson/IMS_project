

<!DOCTYPE html>
<html>

<head>
    <title>InfTraction</title>
    <style>
img.logo {
  display: block;
  margin-left: auto;
  margin-right: auto;
  margin-top: auto;}

img.icon {
    display: block;
    float: left;
    margin-left: auto;
    margin-right: auto;}

.login_form {
    width: 400px; 
    margin: 20px auto;}

.input-row { 
    display: flex; 
    align-items: center; 
    margin-bottom: 15px;}

input {
    width: 300px; 
    height: 35px; 
    font-size: 16px;
    border-radius: 5px;
    border: 1px solid #004aad}

button {
    background-color: #519bd2;
    color: #004aad;
    font-size: 22px;
    font-weight: bold;
    border: 2px solid #004aad;
    border-radius: 10px;
    cursor: pointer;
    float: right;
}

</style>
</head>

<body style="background-image: linear-gradient(90deg, #cdffd8, #94d9ff);">
<img src="/Pictures/logo_nobackground.png" alt="Logo home screen" style="width: 75%;height:auto;" class= "logo">

<form action="/login.code.php" method ="post" class= "login_form">
    <div class = "input-row">
        <img src="/Pictures/user.icon-bgremoved.png" alt="User Icon" style="width: 50px; height:50px;" class="icon">
        <input type= "text" id="user" name="user" placeholder= "Enter Username"> <br> <br>
    </div>  
    <div class = "input-row">
        <img src="/Pictures/password.icon-bgremoved.png" alt="Password Icon" style="width: 50px; height:50px;" class="icon">
        <input type= "password" id="pwd" name="pwd" placeholder= "Enter Password">
    </div>  
 
    <button type="button" onclick="location.href='homepage.php'">
        Log in
    </button>

</form>

</body>