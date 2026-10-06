<?php
require_once "Role.php";
require_once "PrivilegedUser.php";

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


// Access check 

function access($permission) {
    // check if there is a PrivilegedUser object saved in the session variable
    if (!isset($_SESSION['privUser'])) {
        header("Location: index.php");
        exit;
    }

    // Check if the content of the $_SESSION['privUser'] is a PrivilegedUser object
    if (!($_SESSION['privUser'] instanceof PrivilegedUser)) {
        header("Location: index.php");
        exit;
    }

    // does the user stored in the PrivilegedUser object have the right access? 
    if (!$_SESSION['privUser']->hasPrivilege($permission)) {
        header("Location: access_denied.php");
        exit;
    }
}
?>