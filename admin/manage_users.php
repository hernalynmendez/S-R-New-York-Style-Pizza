<?php
/**
 * Manage Users
 * Food Ordering System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/admin.php';

$page_title = 'Manage Users';

requireAdmin();
checkSessionTimeout();

$success = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    
    if ($action === 'activate') {
        $result = activateUser($user_id);
        if ($result['status']) {
            $success = $result['message'];
        }
    } elseif ($action === 'deactivate') {
        $result = deactivateUser($user_id);
        if ($result['status']) {
            $success = $result['message'];
        }
    }
}

$users = getAllUsers();

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Manage Users</h2>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success ?? ''); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Users List</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Member Since</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($user['phone'] ?? ''); ?></td>
                            <td><?php echo formatDate($user['created_at']); ?></td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="view_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                
                                <?php if ($user['is_active']): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="deactivate">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Deactivate this user?')">
                                            <i class="fas fa-lock"></i> Deactivate
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="activate">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-unlock"></i> Activate
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
