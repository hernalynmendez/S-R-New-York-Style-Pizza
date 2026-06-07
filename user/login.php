<?php
/**
 * User Login
 * Food Ordering System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = 'Login';

// Check if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: ' . SITE_URL . 'admin/dashboard.php');
    } else {
        header('Location: ' . SITE_URL . 'user/menu.php');
    }
    exit;
}

$error = '';
// Preserve username across attempts
$username = '';
$showInvalid = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? sanitize($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    $result = loginUser($username, $password);
    if ($result['status']) {
        // Redirect admin users to admin dashboard
        if (isAdmin()) {
            header('Location: ' . SITE_URL . 'admin/dashboard.php');
        } else {
            header('Location: ' . SITE_URL . 'user/menu.php');
        }
        exit;
    } else {
        $error = $result['message'];
        $showInvalid = true;
    }
}

require_once '../includes/header.php';
?>

<div style="background-image: linear-gradient(135deg, rgba(10,10,10,0.75), rgba(10,10,10,0.75)), url('<?php echo SITE_URL; ?>uploads/profiles/lr2.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed; min-height: 100vh; display: flex; align-items: center;">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Login</h2>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show py-3" role="alert">
                            <strong class="me-2">Login failed:</strong> <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control<?php echo $showInvalid ? ' is-invalid' : ''; ?>" id="username" name="username" required autofocus value="<?php echo htmlspecialchars($username); ?>" aria-invalid="<?php echo $showInvalid ? 'true' : 'false'; ?>">
                            <?php if ($showInvalid): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control<?php echo $showInvalid ? ' is-invalid' : ''; ?>" id="password" name="password" required aria-invalid="<?php echo $showInvalid ? 'true' : 'false'; ?>">
                            <?php if ($showInvalid): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
                    </form>
                    
                    <hr>
                    <p class="text-center">Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
