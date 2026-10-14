<?php
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Account Pending</title>

    <link rel="stylesheet" href="/assets/css/pending.css?v=2">
</head>

<body>

    <div class="container">

        <!-- Logo -->
        <img src="/assets/img/logo_nobackground.png" class="logo">

        <!-- Decorative viruses -->
        <img src="/assets/img/virus_bl_nobg.png" class="virus_bl">
        <img src="/assets/img/virus_tr_nobg.png" class="virus_tr">

        <!-- Pending message -->
        <div class="pending-box">

            <h1>Account Pending Approval</h1>

            <p>
                Your account has been created but has not been approved yet.
            </p>

            <p>
                Please wait until an administrator approves your account.
            </p>

            <a href="/index.php" class="back-button">
                Back to Login
            </a>

        </div>

    </div>

</body>
</html>