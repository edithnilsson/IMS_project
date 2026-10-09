<?php

class PrivilegedUser
{
    // defines attributes the PrivilegedUser object will have
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

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // creates a new PrivilegedUser object
            $privUser = new PrivilegedUser();

            // Assigns attributes from the selected $row
            $privUser->user_id            = $row["user_id"];
            $privUser->role_id            = $row["role_id"];
            $privUser->username           = $row["user_username"];
            $privUser->user_password_hash = $row["user_password_hash"];
            $privUser->email_addr         = $row["user_email"];

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
        $this->roles = array();

        if (!empty($this->role_id)) {
            $this->roles[] = Role::getRolePerms($this->role_id);
        }
    }

    // Kontrollerar om användaren har en specifik rättighet
    public function hasPrivilege($perm) {
        foreach ($this->roles as $role) {
            if ($role->hasPerm($perm)) {
                return true;
            }
        }
        return false;
    }

    // Returnerar alla rättigheter användaren har, som en enkel lista.
    // Används för felsökning och för att visa vad en roll faktiskt har tillgång till.
    public function getPermissions(): array {
        $all = [];
        foreach ($this->roles as $role) {
            foreach ($role->getPermissions() as $name => $on) {
                $all[] = $name;
            }
        }
        return array_values(array_unique($all));
    }

    // Enkel åtkomst till användarens id, användbar i andra delar av systemet
    public function getUserId() {
        return $this->user_id;
    }

    // Enkel åtkomst till användarnamnet
    public function getUsername() {
        return $this->username;
    }
}