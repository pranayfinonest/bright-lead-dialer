<?php
// Role-based access control configuration

class RoleManager {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public function getUserRole($userId) {
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['role'] ?? null;
    }
    
    public function hasPermission($userId, $permission) {
        $role = $this->getUserRole($userId);
        return $this->roleHasPermission($role, $permission);
    }
    
    public function roleHasPermission($role, $permission) {
        $permissions = $this->getRolePermissions();
        return in_array($permission, $permissions[$role] ?? []);
    }
    
    public function getRolePermissions() {
        return [
            'admin' => [
                'view_dashboard',
                'manage_users',
                'manage_campaigns',
                'view_analytics',
                'system_settings',
                'manage_integrations',
                'view_all_leads',
                'manage_all_leads',
                'view_all_calls',
                'manage_permissions',
                'export_data',
                'import_data'
            ],
            'manager' => [
                'view_dashboard',
                'view_team_performance',
                'manage_team_campaigns',
                'view_team_analytics',
                'assign_leads',
                'monitor_calls',
                'view_team_leads',
                'manage_team_schedule',
                'view_reports',
                'quality_control'
            ],
            'caller' => [
                'view_dashboard',
                'make_calls',
                'view_assigned_leads',
                'manage_own_leads',
                'send_messages',
                'view_schedule',
                'update_call_disposition',
                'view_own_analytics'
            ]
        ];
    }
    
    public function getDefaultRoute($role) {
        $routes = [
            'admin' => 'admin/dashboard.php',
            'manager' => 'manager/dashboard.php',
            'caller' => 'caller/dashboard.php'
        ];
        return $routes[$role] ?? 'login.php';
    }
}

function requirePermission($permission) {
    global $db;
    
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    
    $roleManager = new RoleManager($db);
    if (!$roleManager->hasPermission($_SESSION['user_id'], $permission)) {
        header('Location: unauthorized.php');
        exit;
    }
}

function getUserRole() {
    global $db;
    if (!isLoggedIn()) return null;
    
    $roleManager = new RoleManager($db);
    return $roleManager->getUserRole($_SESSION['user_id']);
}
?>