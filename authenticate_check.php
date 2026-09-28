<?php

session_start();

//checks if the user is logged in and if not they are redirected to the login page
// isset retunerar true if the variable exists; so if $_SESSION['user'] is not true then redirect user to the log in page
if(!isset($_SESSION['user_id'])) {
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