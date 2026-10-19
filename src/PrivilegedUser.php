<?php

class PrivilegedUser
{
    // defines attributes the PrivilegedUser object will have
    protected $user_id;
    protected $role_id;
    protected $username;

    // only this class can use this variable
    private $roles;

    protected function __construct() {
    }

    // Fetch user from  System_user based on  på username
    public static function getByUsername($username) {
        global $conn;

        $sql = "SELECT * FROM System_user WHERE user_username = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // creates a new PrivilegedUser object
            $privUser = new PrivilegedUser();

            // Assigns attributes from the selected $row
            $privUser->user_id            = $row["user_id"];
            $privUser->role_id            = $row["role_id"];
            $privUser->username           = $row["user_username"];

            // calls the initRoles function
            $privUser->initRoles();

            // returns a full object
            return $privUser;
        } else {
            return false;
        }
    }


    // pulls the role and its privileges 
    protected function initRoles() {
        $this->roles = array();

        if (!empty($this->role_id)) {
            $this->roles[] = Role::getRolePerms($this->role_id);
        }
    }

    // checks if the user has a specific  privilege
    public function hasPrivilege($perm) {
        foreach ($this->roles as $role) {
            if ($role->hasPerm($perm)) {
                return true;
            }
        }
        return false;
    }

    // what pages does a user have access to? 
    public function getPages() {
        $pages = array();
        foreach ($this->roles as $role) {
            $pages = array_merge($pages, $role->getPerm());
        }
        return $pages;
    }
}

    

?> 
