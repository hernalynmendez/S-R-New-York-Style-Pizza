<?php
/**
 * Header Navigation
 * Food Ordering System
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - S&R New York Style Pizza' : 'S&R New York Style Pizza'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Select2 for enhanced, styleable selects -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>css/style.css">
    <?php if (!empty($extra_css) && is_array($extra_css)): ?>
        <?php foreach ($extra_css as $cssFile): ?>
            <link rel="stylesheet" href="<?php echo $cssFile; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<?php
// Determine if current request is for admin area
$isAdminPage = (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) || (function_exists('isAdmin') && isAdmin());
$activeScript = basename($_SERVER['SCRIPT_NAME']);
?>
<body <?php if($isAdminPage) echo 'class="admin-layout"'; ?>>

<?php if ($isAdminPage): ?>
    <nav class="admin-navbar navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo SITE_URL; ?>admin/dashboard.php">
                <img src="<?php echo SITE_URL; ?>uploads/profiles/logo2.jpg" alt="logo" class="navbar-logo me-2">
                <div class="d-flex flex-column" style="line-height:1">
                    <span style="font-weight:700">S&amp;R New York Style Pizza</span>
                    <small style="color:#9aa0a6">Admin Dashboard</small>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav" aria-controls="adminNav" aria-expanded="false">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link <?php echo $activeScript==='dashboard.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo in_array($activeScript,['manage_orders.php','view_order.php','edit_order.php']) ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/manage_orders.php">Orders</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo in_array($activeScript,['manage_food.php','add_food.php','edit_food.php']) ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/manage_food.php">Menu</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo in_array($activeScript,['manage_categories.php','edit_category.php']) ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/manage_categories.php">Inventory</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo $activeScript==='dashboard.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/dashboard.php">Analytics</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo in_array($activeScript,['manage_users.php','view_user.php']) ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/manage_users.php">Users</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>user/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
<?php else: ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-black">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center fw-bold" href="<?php echo SITE_URL; ?>">
                <img src="<?php echo SITE_URL; ?>uploads/profiles/logo2.jpg" alt="S&R New York Style Pizza" class="navbar-logo me-2">
                <span>
                    S&R New York Style Pizza

                </span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (!isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>user/register.php">Register</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>user/login.php">Login</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>user/menu.php">Menu</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>user/cart.php">
                                <i class="fas fa-shopping-cart"></i> Cart
                                <?php $cart_count = getCartCount(); ?>
                                <?php if ($cart_count > 0): ?>
                                    <span class="badge bg-warning text-dark"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>user/orders.php">Orders</a>
                        </li>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>admin/dashboard.php">Admin</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>user/profile.php">My Account</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>user/logout.php">Logout</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
<?php endif; ?>
