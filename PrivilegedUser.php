<?php

class PrivilegedUser
{
    //defines attributes the PrivilegedUser object will have
    protected $user_id;
    protected $role_id;
    protected $username;
    protected $user_password_hash;
    protected $email_addr;

    // only this class can use this variable
    private $roles;

    protected function __construct() {
    }

    // Hämtar användare från System_user baserat på användarnamn
    public static function getByUsername($username) {
        global $conn; 

        $sql = "SELECT * FROM System_user WHERE user_username = ?";
        
        $stmt = $conn->prepare($sql);

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        //checks if the 
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // creates a new PrivilegedUser object 
            $privUser = new PrivilegedUser();

            // Assignes attributes from the selected $row 
            $privUser->user_id = $row["user_id"];
            $privUser->role_id = $row["role_id"]; 
            $privUser->username = $row["user_username"];
            $privUser->user_password_hash = $row["user_password_hash"];
            $privUser->email_addr = $row["user_email"];

            // calls the initRoles function 
            $privUser->initRoles();

            // returns a full object 
            return $privUser;
        } else {
            return false;
        }
    }

    // Laddar rollen och dess tillhörande rättigheter
    protected function initRoles() {
        // specific for this specific object, refers to the created PrivilegedUser object $privUser
        $this->roles = array();

        //checks if the user has an assigned role
        if (!empty($this->role_id)) {

            //fetches the role object and all its permissions and adds it to the PrivilegedUser object
            $this->roles[] = Role::getRolePerms($this->role_id);
        }
    }

    // Kontrollerar om användaren har en specifik rättighet
    public function hasPrivilege($perm) {
        // for each role a user has (for now only one role)
        foreach ($this->roles as $role) {
            //checks what permissions a role has
            if ($role->hasPerm($perm)) {
                return true;
            }
        }
        return false;
    }
}