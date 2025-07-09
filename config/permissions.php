<?php
// Advanced Permission Management System

class PermissionManager {
    private $db;
    private $rolePermissions;
    
    public function __construct($database) {
        $this->db = $database;
        $this->initializePermissions();
    }
    
    private function initializePermissions() {
        $this->rolePermissions = [
            'admin' => [
                // System Administration
                'system.manage',
                'system.settings',
                'system.backup',
                'system.logs',
                
                // User Management
                'users.create',
                'users.read',
                'users.update',
                'users.delete',
                'users.permissions',
                
                // Organization Management
                'organization.manage',
                'organization.hierarchy',
                'organization.departments',
                
                // All Lead Operations
                'leads.create',
                'leads.read.all',
                'leads.update.all',
                'leads.delete',
                'leads.assign',
                'leads.import',
                'leads.export',
                
                // Campaign Management
                'campaigns.create',
                'campaigns.read.all',
                'campaigns.update.all',
                'campaigns.delete',
                'campaigns.manage',
                
                // Analytics & Reporting
                'analytics.system',
                'analytics.users',
                'analytics.performance',
                'reports.generate',
                'reports.export',
                
                // Integrations
                'integrations.manage',
                'integrations.configure',
                'api.access',
                
                // Communication
                'messages.send.all',
                'messages.templates.manage',
                'calls.monitor.all',
                
                // Financial
                'billing.manage',
                'subscriptions.manage'
            ],
            
            'manager' => [
                // Team Management
                'team.manage',
                'team.performance',
                'team.schedule',
                
                // Lead Management (Team Level)
                'leads.read.team',
                'leads.update.team',
                'leads.assign.team',
                'leads.distribute',
                
                // Campaign Management (Team Level)
                'campaigns.create.team',
                'campaigns.read.team',
                'campaigns.update.team',
                'campaigns.manage.team',
                
                // Call Management
                'calls.monitor.team',
                'calls.quality.control',
                'calls.listen',
                'calls.whisper',
                'calls.barge',
                
                // Analytics (Team Level)
                'analytics.team',
                'analytics.agents',
                'reports.team',
                
                // Communication
                'messages.send.team',
                'messages.templates.team',
                
                // Scheduling
                'schedule.manage.team',
                'schedule.assign',
                
                // Basic User Operations
                'users.read.team',
                'profile.update'
            ],
            
            'caller' => [
                // Personal Operations
                'profile.read',
                'profile.update',
                
                // Lead Management (Personal)
                'leads.read.assigned',
                'leads.update.assigned',
                'leads.notes.add',
                
                // Calling Operations
                'calls.make',
                'calls.receive',
                'calls.disposition',
                'calls.notes',
                'calls.schedule',
                
                // Communication
                'messages.send.assigned',
                'messages.templates.use',
                'whatsapp.send',
                'sms.send',
                'email.send',
                
                // Personal Analytics
                'analytics.personal',
                'performance.view',
                
                // Schedule Management
                'schedule.personal',
                'schedule.callbacks',
                
                // Follow-up Management
                'followups.manage',
                'reminders.set'
            ]
        ];
    }
    
    public function hasPermission($userId, $permission) {
        $userRole = $this->getUserRole($userId);
        return $this->roleHasPermission($userRole, $permission);
    }
    
    public function roleHasPermission($role, $permission) {
        return in_array($permission, $this->rolePermissions[$role] ?? []);
    }
    
    public function getUserRole($userId) {
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['role'] ?? null;
    }
    
    public function getRolePermissions($role) {
        return $this->rolePermissions[$role] ?? [];
    }
    
    public function getAllPermissions() {
        return $this->rolePermissions;
    }
    
    public function getDefaultRoute($role) {
        $routes = [
            'admin' => 'admin/dashboard.php',
            'manager' => 'manager/dashboard.php',
            'caller' => 'caller/dashboard.php'
        ];
        return $routes[$role] ?? 'login.php';
    }
    
    public function canAccessResource($userId, $resource, $resourceOwnerId = null) {
        $userRole = $this->getUserRole($userId);
        
        // Admin can access everything
        if ($userRole === 'admin') {
            return true;
        }
        
        // Manager can access team resources
        if ($userRole === 'manager') {
            return $this->isTeamMember($userId, $resourceOwnerId);
        }
        
        // Caller can only access own resources
        if ($userRole === 'caller') {
            return $userId == $resourceOwnerId;
        }
        
        return false;
    }
    
    private function isTeamMember($managerId, $userId) {
        // Check if user is in manager's team
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM users 
            WHERE id = ? AND (manager_id = ? OR id = ?)
        ");
        $stmt->execute([$userId, $managerId, $managerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    }
}

// Global permission functions
function requirePermission($permission) {
    global $permissionManager;
    
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    
    if (!$permissionManager->hasPermission($_SESSION['user_id'], $permission)) {
        header('Location: unauthorized.php');
        exit;
    }
}

function hasPermission($permission) {
    global $permissionManager;
    
    if (!isLoggedIn()) {
        return false;
    }
    
    return $permissionManager->hasPermission($_SESSION['user_id'], $permission);
}

function canAccessResource($resource, $resourceOwnerId = null) {
    global $permissionManager;
    
    if (!isLoggedIn()) {
        return false;
    }
    
    return $permissionManager->canAccessResource($_SESSION['user_id'], $resource, $resourceOwnerId);
}

// Initialize permission manager
$permissionManager = new PermissionManager($db);
?>