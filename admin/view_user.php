<?php
/**
 * View User Details
 * Food Ordering System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/admin.php';

$page_title = 'User Details';

requireAdmin();
checkSessionTimeout();

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id === 0) {
    header('Location: ' . SITE_URL . 'admin/manage_users.php');
    exit;
}

$user = getUserById($user_id);

if (!$user) {
    header('Location: ' . SITE_URL . 'admin/manage_users.php');
    exit;
}

// Get user orders
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders_count = $result->fetch_assoc()['total'];
$stmt->close();

// Get user orders total spent
$stmt = $conn->prepare("SELECT SUM(total_amount) as total FROM orders WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_spent = $result->fetch_assoc()['total'];
$stmt->close();

require_once '../includes/header.php';
?>

<div class="container py-5">
    <a href="manage_users.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Back to Users
    </a>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username'] ?? ''); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                            <p><strong>First Name:</strong> <?php echo htmlspecialchars($user['first_name'] ?? ''); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Last Name:</strong> <?php echo htmlspecialchars($user['last_name'] ?? ''); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?? ''); ?></p>
                            <p><strong>Member Since:</strong> <?php echo formatDate($user['created_at']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Address Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address'] ?? ''); ?></p>
                    <p><strong>City:</strong> <?php echo htmlspecialchars($user['city'] ?? ''); ?></p>
                    <p><strong>State:</strong> <?php echo htmlspecialchars($user['state'] ?? ''); ?></p>
                    <p><strong>Postal Code:</strong> <?php echo htmlspecialchars($user['postal_code'] ?? ''); ?></p>
                    <p><strong>Country:</strong> <?php echo htmlspecialchars($user['country'] ?? ''); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Account Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Total Orders</h6>
                        <h3><?php echo $orders_count; ?></h3>
                    </div>
                    <div class="mb-3">
                        <h6>Total Spent</h6>
                        <h3><?php echo formatPrice($total_spent ?? 0); ?></h3>
                    </div>
                    <div>
                        <h6>Account Status</h6>
                        <h3><?php echo $user['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
