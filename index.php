
<?php
session_start();

## Captcha

$characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';

$captcha = '';

for ($i = 0; $i < 5; $i++) {
    $captcha .= $characters[rand(0, strlen($characters) - 1)];
}

$_SESSION['captcha_answer'] = $captcha;

?>

<!DOCTYPE html>

<html>

<head>
    <title>InfTraction</title>

            <link rel="stylesheet" href="css/login.css">
</head>

<body style="background: linear-gradient(90deg, #cdffd8, #94d9ff);">
<img src="/Pictures/logo_nobackground.png" alt="Logo home screen" style="width: 75%;height:auto;" class= "logo">

<form action="login_processing.php" method="post" class="login_form">

    <div class="input-row">
        <img src="/Pictures/user.icon-bgremoved.png" alt="User Icon"
             style="width: 50px; height:50px;" class="icon">
        <input type="text" id="user" name="user" placeholder="Enter Username">
        <br><br>
    </div>

    <div class="input-row">
        <img src="/Pictures/password.icon-bgremoved.png" alt="Password Icon"
             style="width: 50px; height:50px;" class="icon">
        <input type="password" id="pwd" name="pwd" placeholder="Enter Password">
    </div>

    <div class="input-row captcha-row">

    <label class="captcha-label">
        CAPTCHA: <strong><?php echo $captcha; ?></strong>
    </label>

    <input type="text"
           id="captcha"
           name="captcha"
           placeholder="Enter CAPTCHA"
           required>

          </div>
    




    <button type="submit" class="login_button">
        Log in
    </button>

</form>

<button type="button" onclick="location.href='register.php'" class="register_button">
        Or sign up here
    </button>

</body>

</html>