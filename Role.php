<?php

class Role
{
    protected $permissions;

    protected function __construct() {
        $this->permissions = array();
    }

    public static function getRolePerms($role_id) {
        global $conn;

        $role = new Role();

        $sql = "SELECT p.permission_desc
                FROM Role_permission rp
                JOIN Permissions p ON rp.permission_id = p.permission_id
                WHERE rp.role_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $role_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $role->permissions[$row["permission_desc"]] = true;
        }

        return $role;
    }

    public function hasPerm($permission) {
        return isset($this->permissions[$permission]);
    }

    // Returnerar hela permissions-arrayen för rollen.
    public function getPermissions(): array {
        return $this->permissions;
    }
}