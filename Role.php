<?php

class Role

{
 // protected --> the variable can only be reached within this class
    protected $permissions;

    protected function __construct() {

        //  $this refers to the current object's properties and methods
        $this->permissions = array();
    }

    // this function will return a role object ($role) with its associated permissions

    // public --> can be accessed through the class anywhere 
    public static function getRolePerms($role_id) {
   
    global $conn;
    // creates an object ("box") to hold all roles and their permissions
    $role = new Role();

     // queries the database for infortmation to fill the "box" with
    $sql = "SELECT p.permission_desc, p.permission_page
            FROM Role_permission rp
            JOIN Permissions p ON rp.permission_id = p.permission_id
            WHERE rp.role_id = ?";


    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // fill the box 
    // every permission a role found in database will be set to true
        while ($row = $result->fetch_assoc()) {
        $role->permissions[$row["permission_desc"]] = $row["permission_page"];
        }

    // returns the "box" filled with all permissions a role have
        return $role;
    }

    // check if a role has a specific permission
    public function hasPerm($permission) {
        return isset($this->permissions[$permission]);
    }

    // returns all permissions the user has 
    public function getPerm() {
        return $this -> permissions;
    }

}