<?php 

session_start();

/*empty the array*/
$_SESSION = array(); 

/*delete the session cookie*/
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}


/*delete the session cookie*/
session_destroy()

?>