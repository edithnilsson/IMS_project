<?php

session_start();

// Checks if the user exists and is not null --> the user has logged in
if(isset($_SESSION['user_id'])) {
    
    //Checks if more than 20 minutes of inactivity have passed, and if so they are logged out 
    if (time() - $_SESSION['last_activity'] > 1200) {
        session_unset();
        session_destroy();
        
        //destroys the session cookie  
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        header('Location: index.php'); 
        exit;
}
    // if less than 20 minutes have passed reset the time
        $_SESSION['last_activity'] = time(); 
}

// if the user is not logged in they are redirected to the login page
else {
    header("Location: index.php");
    exit;
} 


// Checks if the user has permission to go into the page and if they do not then they are sent to an access denied page
function access($permission) {
    //if the user role that is stored in the global variable is not listed in the permission array 
    //(specified in the page .php) then the user does not have access and is redirected to the access denied page
    if (!in_array($_SESSION['user_role'], $permission, true )) {
    header("Location: access_denied.php");
    exit; 
}}

?>