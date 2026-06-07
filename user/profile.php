<?php
/**
 * User Profile
 * Food Ordering System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = 'My Profile';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . 'user/login.php');
    exit;
}

checkSessionTimeout();

$error = '';
$success = '';

$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = isset($_POST['first_name']) ? sanitize($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize($_POST['last_name']) : '';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $address = isset($_POST['address']) ? sanitize($_POST['address']) : '';
    $city = isset($_POST['city']) ? sanitize($_POST['city']) : '';
    $state = isset($_POST['state']) ? sanitize($_POST['state']) : '';
    $postal_code = isset($_POST['postal_code']) ? sanitize($_POST['postal_code']) : '';
    $country = isset($_POST['country']) ? sanitize($_POST['country']) : '';
    
    $result = updateUserProfile($first_name, $last_name, $phone, $address, $city, $state, $postal_code, $country);
    
    if ($result['status']) {
        $success = $result['message'];
        $user = getCurrentUser(); // Refresh user data
    } else {
        $error = $result['message'];
    }
    
    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['size'] > 0) {
        $result = uploadProfileImage($_FILES['profile_image']);
        if ($result['status']) {
            $success = $result['message'];
            $user = getCurrentUser();
        } else {
            $error = $result['message'];
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <h2 class="mb-4">My Profile</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error ?? ''); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success ?? ''); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Address Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country" value="<?php echo htmlspecialchars($user['country'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Profile Picture</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <input type="file" class="form-control" name="profile_image" accept="image/*">
                            <small class="text-muted">Maximum file size: 5MB. Allowed formats: JPG, PNG, GIF</small>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload Picture
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Username:</strong>
                        <p><?php echo htmlspecialchars($user['username'] ?? ''); ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Member Since:</strong>
                        <p><?php echo formatDate($user['created_at']); ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Account Status:</strong>
                        <p><?php echo $user['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'; ?></p>
                    </div>
                    
                    <hr>
                    
                    <a href="change_password.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-lock"></i> Change Password
                    </a>
                    <a href="logout.php" class="btn btn-outline-danger w-100">
                        <i class="fas fa-sign-out"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
