<?php
/**
 * Home Page / Index
 * Food Ordering System
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$page_title = 'Home';

require_once 'includes/header.php';
?>

<div class="container-fluid p-0">
    <!-- Hero Section -->
    <div class="hero-section text-white py-5" style="background-image: linear-gradient(135deg, rgba(10,10,10,0.6), rgba(10,10,10,0.6)), url('<?php echo SITE_URL; ?>uploads/profiles/homepage.jpg');">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span class="hero-quote">BIG TASTE, BIT FUN!</span>
                    <h1 class="display-4 fw-bold mb-4">S&R New York Style Pizza</h1>
                    <p class="lead mb-4">A pizza experience with bold New York flavor, fast ordering, and effortless delivery.</p>
                    
                    <?php if (isLoggedIn()): ?>
                        <a href="user/menu.php" class="btn btn-red btn-lg me-3">
                            <i class="fas fa-pizza-slice"></i> Browse Menu
                        </a>
                        <a href="user/orders.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-list"></i> My Orders
                        </a>
                    <?php else: ?>
                        <a href="user/register.php" class="btn btn-red btn-lg me-3">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                        <a href="user/login.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 d-none d-md-block"></div>
            </div>
        </div>
    </div>
    
    <!-- Features Section -->
    <div class="container py-5">
        <h2 class="text-center mb-5">Why Choose Us?</h2>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-bolt text-warning" style="font-size: 3rem; margin: 20px 0;"></i>
                        <h5 class="card-title">Fast Delivery</h5>
                        <p class="card-text">Get your food delivered quickly with our efficient delivery service.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-utensils text-success" style="font-size: 3rem; margin: 20px 0;"></i>
                        <h5 class="card-title">Quality Food</h5>
                        <p class="card-text">Fresh, delicious food prepared by our trusted restaurants.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-shield-alt text-info" style="font-size: 3rem; margin: 20px 0;"></i>
                        <h5 class="card-title">Secure Payment</h5>
                        <p class="card-text">Multiple payment options with secure and encrypted transactions.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Popular Items Section -->
    <?php if (isLoggedIn()): ?>
        <div class="bg-light py-5">
            <div class="container">
                <h2 class="mb-5">Popular Items</h2>
                
                <?php 
                $popular_items = getAllFoodItems(null, null);
                $limited_items = array_slice($popular_items, 0, 6);
                ?>
                
                <div class="row">
                    <?php foreach ($limited_items as $item): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card food-card h-100">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?php echo SITE_URL; ?>uploads/food/<?php echo htmlspecialchars($item['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['name']); ?>" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <p class="card-text text-muted small"><?php echo htmlspecialchars(substr($item['description'], 0, 60)) . '...'; ?></p>
                                    
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="h5 mb-0"><?php echo formatPrice($item['price']); ?></span>
                                        </div>
                                        
                                        <form method="POST" action="user/cart_action.php" class="d-flex gap-2">
                                            <input type="hidden" name="food_id" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="action" value="add">
                                            <input type="number" name="quantity" class="form-control form-control-sm" value="1" min="1" max="10">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-shopping-cart"></i> Add
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="text-center mt-4">
                    <a href="user/menu.php" class="btn btn-primary btn-lg">
                        View All Items
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Call to Action Section -->
    <div class="hero-section text-white py-5" style="background-image: linear-gradient(135deg, rgba(10,10,10,0.7), rgba(10,10,10,0.7)), url('<?php echo SITE_URL; ?>uploads/profiles/logo3.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
        <div class="container text-center">
            <h2 class="mb-4">Ready to Order?</h2>
            <p class="lead mb-4">Join thousands of satisfied customers enjoying premium New York-style pizza.</p>
            
            <?php if (!isLoggedIn()): ?>
                <a href="user/register.php" class="btn btn-red btn-lg me-3">
                    <i class="fas fa-user-plus"></i> Create Account
                </a>
            <?php else: ?>
                <a href="user/menu.php" class="btn btn-red btn-lg me-3">
                    <i class="fas fa-utensils"></i> Order Now
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
