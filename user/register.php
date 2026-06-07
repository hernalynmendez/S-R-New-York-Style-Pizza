<?php
/**
 * User Registration
 * Food Ordering System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = 'Register';

// Check if already logged in
if (isLoggedIn()) {
    header('Location: ' . SITE_URL . 'user/menu.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? sanitize($_POST['username']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $first_name = isset($_POST['first_name']) ? sanitize($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize($_POST['last_name']) : '';
    
    if ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $result = registerUser($username, $email, $password, $first_name, $last_name);
        if ($result['status']) {
            $success = $result['message'];
            echo '<script>setTimeout(() => window.location.href = "' . SITE_URL . 'user/login.php", 2000);</script>';
        } else {
            $error = $result['message'];
        }
    }
}

require_once '../includes/header.php';
?>

<div style="background-image: linear-gradient(135deg, rgba(10,10,10,0.75), rgba(10,10,10,0.75)), url('<?php echo SITE_URL; ?>uploads/profiles/lr2.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed; min-height: 100vh; display: flex; align-items: center;">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Create Account</h2>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($success); ?> Redirecting to login...
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                            <small class="text-muted">Username must be unique</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="text-muted">At least 6 characters</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">Register</button>
                    </form>
                    
                    <p class="text-center">Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<?php require_once '../includes/footer.php'; ?>
