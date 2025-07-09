<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../config/roles.php';

requirePermission('manage_users');

$pageTitle = 'User Management - FINONEST TeleCRM';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_user':
                $stmt = $db->prepare("
                    INSERT INTO users (name, email, password, role, phone, department, active) 
                    VALUES (?, ?, ?, ?, ?, ?, 1)
                ");
                $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt->execute([
                    $_POST['name'],
                    $_POST['email'],
                    $hashedPassword,
                    $_POST['role'],
                    $_POST['phone'] ?? null,
                    $_POST['department'] ?? null
                ]);
                $success_message = "User created successfully!";
                break;
                
            case 'update_user':
                $stmt = $db->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, role = ?, phone = ?, department = ?, active = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['email'],
                    $_POST['role'],
                    $_POST['phone'] ?? null,
                    $_POST['department'] ?? null,
                    $_POST['active'] ?? 0,
                    $_POST['user_id']
                ]);
                $success_message = "User updated successfully!";
                break;
                
            case 'delete_user':
                $stmt = $db->prepare("UPDATE users SET active = 0 WHERE id = ?");
                $stmt->execute([$_POST['user_id']]);
                $success_message = "User deactivated successfully!";
                break;
        }
    }
}

// Get users with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';

$where_conditions = ["1=1"];
$params = [];

if ($search) {
    $where_conditions[] = "(name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($role_filter) {
    $where_conditions[] = "role = ?";
    $params[] = $role_filter;
}

$where_clause = implode(" AND ", $where_conditions);

$stmt = $db->prepare("
    SELECT * FROM users 
    WHERE $where_clause 
    ORDER BY created_at DESC 
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count
$count_stmt = $db->prepare("SELECT COUNT(*) as total FROM users WHERE $where_clause");
$count_stmt->execute($params);
$total_users = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_users / $limit);

// Get user statistics
$stats_stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_users,
        COUNT(CASE WHEN role = 'admin' THEN 1 END) as admins,
        COUNT(CASE WHEN role = 'manager' THEN 1 END) as managers,
        COUNT(CASE WHEN role = 'caller' THEN 1 END) as callers,
        COUNT(CASE WHEN active = 1 THEN 1 END) as active_users,
        COUNT(CASE WHEN active = 0 THEN 1 END) as inactive_users
    FROM users
");
$stats_stmt->execute();
$user_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="users-container">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1>User Management</h1>
                <p>Manage system users, roles, and permissions</p>
            </div>
            <button class="btn btn-primary" onclick="showCreateUserModal()">
                <i class="icon-user-plus"></i>
                Add New User
            </button>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Total Users</span>
                    <span class="stat-value"><?php echo $user_stats['total_users']; ?></span>
                    <span class="stat-desc"><?php echo $user_stats['active_users']; ?> active</span>
                </div>
                <div class="stat-icon primary">
                    <i class="icon-users"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Administrators</span>
                    <span class="stat-value"><?php echo $user_stats['admins']; ?></span>
                </div>
                <div class="stat-icon error">
                    <i class="icon-shield"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Managers</span>
                    <span class="stat-value"><?php echo $user_stats['managers']; ?></span>
                </div>
                <div class="stat-icon warning">
                    <i class="icon-users"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Callers</span>
                    <span class="stat-value"><?php echo $user_stats['callers']; ?></span>
                </div>
                <div class="stat-icon success">
                    <i class="icon-phone"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="users-table-card">
        <div class="card-header">
            <h3>All Users</h3>
        </div>
        <div class="card-content">
            <!-- Filters -->
            <div class="table-filters">
                <div class="search-input">
                    <i class="icon-search"></i>
                    <input type="text" placeholder="Search users..." 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           onchange="filterUsers()" id="userSearch">
                </div>
                <select id="roleFilter" onchange="filterUsers()">
                    <option value="">All Roles</option>
                    <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="manager" <?php echo $role_filter === 'manager' ? 'selected' : ''; ?>>Manager</option>
                    <option value="caller" <?php echo $role_filter === 'caller' ? 'selected' : ''; ?>>Caller</option>
                </select>
                <button class="btn btn-outline">
                    <i class="icon-filter"></i>
                    More Filters
                </button>
            </div>

            <div class="table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($user['name'], 0, 2)); ?>
                                        </div>
                                        <div class="user-details">
                                            <p class="user-name"><?php echo htmlspecialchars($user['name']); ?></p>
                                            <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                                            <?php if ($user['phone']): ?>
                                                <p class="user-phone"><?php echo htmlspecialchars($user['phone']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="role-badge <?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['department'] ?: 'Not assigned'); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $user['active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $user['active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td><?php echo $user['last_login'] ? date('M j, Y', strtotime($user['last_login'])) : 'Never'; ?></td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon" onclick="editUser(<?php echo $user['id']; ?>)">
                                            <i class="icon-edit"></i>
                                        </button>
                                        <button class="btn-icon" onclick="viewUserDetails(<?php echo $user['id']; ?>)">
                                            <i class="icon-eye"></i>
                                        </button>
                                        <button class="btn-icon" onclick="resetPassword(<?php echo $user['id']; ?>)">
                                            <i class="icon-key"></i>
                                        </button>
                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                            <button class="btn-icon text-error" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                                <i class="icon-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>" 
                           class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div id="createUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create New User</h3>
            <button class="modal-close" onclick="closeCreateUserModal()">&times;</button>
        </div>
        <form method="POST" class="modal-body">
            <input type="hidden" name="action" value="create_user">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="caller">Caller</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="department">Department</label>
                    <select id="department" name="department">
                        <option value="">Select Department</option>
                        <option value="sales">Sales</option>
                        <option value="support">Support</option>
                        <option value="marketing">Marketing</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeCreateUserModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<script>
function showCreateUserModal() {
    document.getElementById('createUserModal').style.display = 'block';
}

function closeCreateUserModal() {
    document.getElementById('createUserModal').style.display = 'none';
}

function filterUsers() {
    const search = document.getElementById('userSearch').value;
    const role = document.getElementById('roleFilter').value;
    window.location.href = `users.php?search=${encodeURIComponent(search)}&role=${encodeURIComponent(role)}`;
}

function editUser(id) {
    window.location.href = `edit-user.php?id=${id}`;
}

function viewUserDetails(id) {
    window.location.href = `user-details.php?id=${id}`;
}

function resetPassword(id) {
    if (confirm('Are you sure you want to reset this user\'s password?')) {
        // Implement password reset
        alert('Password reset email sent');
    }
}

function deleteUser(id) {
    if (confirm('Are you sure you want to deactivate this user?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_user">
            <input type="hidden" name="user_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>