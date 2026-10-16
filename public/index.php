
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

            <link rel="stylesheet" href="/assets/css/index.css?v=132">
            <link rel="stylesheet" href="/assets/css/captcha.css?v=123">

</head>

<body style="background: linear-gradient(90deg, #cdffd8, #94d9ff);">
<img src="/assets/img/logo_nobackground.png" class= "logo">
<a href="/account/register.php" class="register_link">
    Click Here to Create a New Account
</a>
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

</body>

</html>