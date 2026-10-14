
<?php
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();


?>

<!DOCTYPE html>

<html>

<head>
    <title>InfTraction</title>

            <link rel="stylesheet" href="/assets/css/index.css">
            <link rel="stylesheet" href="/assets/css/captcha.css">

</head>

<body style="background: linear-gradient(90deg, #cdffd8, #94d9ff);">
<img src="/assets/img/logo_nobackground.png" class= "logo">

<form action="/account/login_processing.php" method="post" class="login_form">

    <div class="input-row">
        <img src="/assets/img/user.icon-bgremoved.png" class="icon">
        <input type="text" id="user" name="user" placeholder="Enter Username">
        <br><br>
    </div>

    <div class="input-row">
        <img src="/assets/img/password.icon-bgremoved.png" class="icon">
        <input type="password" id="pwd" name="pwd" placeholder="Enter Password">
    </div>

    <div class="captcha-row">

    <div class="captcha-icon">
        <img src="/assets/img/robot.icon-bgremoved.png" alt="CAPTCHA icon">
    </div>

    <div class="captcha-content">

        <img src="/account/captcha.php" alt="CAPTCHA" class="captcha-image">

        <input type="text"
               id="captcha"
               name="captcha"
               placeholder="Enter CAPTCHA"
               required>

    </div>

</div>

    <button type="submit" class="login_button">
        Log in
    </button>

</form>

<button type="button" onclick="location.href='/account/register.php'" class="register_button">
        Or sign up here
    </button>

</body>

</html>