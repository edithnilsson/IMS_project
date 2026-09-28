<?php 

session_start();

/*empty the array*/
session_unset(); 

/*delete the session cookie*/
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}


/*delete the session on the server*/
session_destroy();

header("Location: index.php");
exit; 

?>