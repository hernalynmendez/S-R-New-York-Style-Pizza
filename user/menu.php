<?php
/**
 * Food Menu - S&R New York Style Pizza
 * Food Ordering System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = 'Menu';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . 'user/login.php');
    exit;
}

checkSessionTimeout();

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : null;

$categories = getAllCategories();
$food_items = getAllFoodItems($category_id, $search);

require_once '../includes/header.php';
?>

<!-- Menu Hero Section -->
<div class="menu-hero" style="background-image: linear-gradient(135deg, rgba(227, 47, 47, 0.12) 0%, rgba(0, 0, 0, 0.8) 50%, rgba(0, 0, 0, 0.9) 100%), linear-gradient(to right, rgba(255, 87, 69, 0.08), transparent 60%), url('<?php echo SITE_URL; ?>uploads/profiles/lr2.jpg'); background-size: cover; background-position: center center; background-repeat: no-repeat; background-attachment: fixed;">
    <div class="container">
        <h1 class="menu-hero-title">Our Premium Menu</h1>
        <p class="menu-hero-subtitle">Handcrafted New York-style pizzas & more</p>
    </div>
</div>

<!-- Search & Filter Section -->
<div class="menu-search-section">
    <div class="container">
        <div class="row g-3 align-items-end">
            <div class="col-md-6">
                <form method="GET" class="search-form">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text search-icon">
                            <i class="fas fa-search"></i>
                        </span>
                        <input class="form-control search-input" 
                               type="search" 
                               name="search" 
                               placeholder="Search pizzas, pasta, wings..." 
                               value="<?php echo htmlspecialchars($search ?? ''); ?>">
                        <button class="btn btn-search" type="submit">
                            Find
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-md-6">
                <div class="text-md-end text-muted-light small">
                    <i class="fas fa-info-circle"></i> Free delivery on orders over ₱900
                </div>
            </div>
        </div>

        <!-- Category Filter Tabs -->
        <div class="category-tabs mt-4">
            <a href="menu.php" class="category-tab <?php echo $category_id === null ? 'active' : ''; ?>">
                <i class="fas fa-th"></i> All Items
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="menu.php?category=<?php echo $cat['id']; ?>" 
                   class="category-tab <?php echo $category_id === $cat['id'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Menu Content -->
<div class="menu-content">
    <div class="container">
        <?php if (empty($food_items)): ?>
            <div class="no-results-card">
                <i class="fas fa-search"></i>
                <h3>No Items Found</h3>
                <p>We couldn't find any items matching your search. Try a different term or browse all categories.</p>
                <a href="menu.php" class="btn btn-red mt-3">
                    <i class="fas fa-redo"></i> Browse All Menu
                </a>
            </div>
        <?php else: ?>
            <div class="food-grid">
                <?php foreach ($food_items as $food): ?>
                    <div class="food-card-wrapper">
                        <div class="food-card">
                            <!-- Image Section -->
                            <div class="food-image-container">
                                <?php if (!empty($food['image'])): ?>
                                    <img src="<?php echo SITE_URL; ?>uploads/food/<?php echo htmlspecialchars($food['image']); ?>" 
                                         class="food-image" 
                                         alt="<?php echo htmlspecialchars($food['name']); ?>">
                                <?php else: ?>
                                    <div class="food-image-placeholder">
                                        <i class="fas fa-pizza-slice"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Category Badge -->
                                <?php if (!empty($food['category_name'])): ?>
                                    <span class="food-category-badge">
                                        <?php echo htmlspecialchars($food['category_name']); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <!-- Vegetarian Badge -->
                                 
                                <?php if ($food['is_vegetarian']): ?>
                                    <span class="food-vegetarian-badge">
                                     <i class="fas fa-leaf"></i> Vegetarian
                                    </span>
                                <?php endif; ?> 
                                
                            </div> 
                                

                            <!-- Content Section -->
                            <div class="food-card-content">
                                <h3 class="food-name"><?php echo htmlspecialchars($food['name']); ?></h3>
                                <p class="food-description">
                                    <?php echo htmlspecialchars(substr($food['description'], 0, 85)); ?>
                                </p>

                                <!-- Footer Section -->
                                <div class="food-footer">
                                    <div class="food-price">
                                        <?php echo formatPrice($food['price']); ?>
                                    </div>

                                    <form method="POST" action="cart_action.php" class="add-to-cart-form">
                                        <input type="hidden" name="food_id" value="<?php echo $food['id']; ?>">
                                        <input type="hidden" name="action" value="add">
                                        <div class="qty-input-group">
                                            <button type="button" class="qty-btn minus" onclick="decrementQty(this)">−</button>
                                            <input type="number" name="quantity" class="qty-input" value="1" min="1" max="10">
                                            <button type="button" class="qty-btn plus" onclick="incrementQty(this)">+</button>
                                        </div>
                                        <button type="submit" class="btn-add-to-cart">
                                            <i class="fas fa-shopping-cart"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Menu Hero Section */
.menu-hero {
    background: linear-gradient(135deg, rgba(227,47,47,0.08) 0%, rgba(0,0,0,0.2) 100%),
                linear-gradient(180deg, #0f0f0f 0%, #080808 100%);
    border-bottom: 1px solid rgba(255,255,255,0.05);
    padding: 4rem 0;
    margin-top: -8px;
    margin-bottom: 3rem;
}

.menu-hero-title {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 700;
    letter-spacing: -0.02em;
    color: #fff;
    margin-bottom: 0.5rem;
}

.menu-hero-subtitle {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.72);
    letter-spacing: 0.05em;
    margin: 0;
}

/* Search & Filter Section */
.menu-search-section {
    padding: 2.5rem 0 3.5rem;
    background: linear-gradient(180deg, rgba(15,15,15,0.5) 0%, transparent 100%);
    border-bottom: 1px solid rgba(255,255,255,0.04);
    margin-bottom: 2.5rem;
}

.search-form {
    position: relative;
}

.input-group {
    background: #111111;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    overflow: hidden;
}

.input-group-text {
    background: transparent;
    border: none;
    color: rgba(255,255,255,0.5);
    padding: 0 1.2rem;
}

.search-icon {
    border-right: 1px solid rgba(255,255,255,0.08);
}

.search-input {
    background: #111111 !important;
    border: none !important;
    color: #fff !important;
    padding: 1rem 1.2rem !important;
    font-size: 1rem;
}

.search-input::placeholder {
    color: rgba(255,255,255,0.5) !important;
}

.search-input:focus {
    box-shadow: none !important;
    background: #141414 !important;
}

.btn-search {
    background: #e32f2f;
    border: none;
    color: #fff;
    padding: 0 1.8rem;
    font-weight: 600;
    transition: background 0.2s ease;
    border-radius: 0;
}

.btn-search:hover {
    background: #ff4136;
    color: #fff;
}

.text-muted-light {
    color: rgba(255,255,255,0.65) !important;
}

/* Category Tabs */
.category-tabs {
    display: flex;
    gap: 0.75rem;
    overflow-x: auto;
    padding-bottom: 0.5rem;
    scroll-behavior: smooth;
}

.category-tabs::-webkit-scrollbar {
    height: 4px;
}

.category-tabs::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.04);
    border-radius: 2px;
}

.category-tabs::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.12);
    border-radius: 2px;
}

.category-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.2rem;
    background: #111111;
    border: 1px solid rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.7);
    border-radius: 6px;
    white-space: nowrap;
    transition: all 0.24s ease;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    cursor: pointer;
}

.category-tab:hover {
    background: #141414;
    border-color: rgba(255,255,255,0.16);
    color: rgba(255,255,255,0.9);
}

.category-tab.active {
    background: #e32f2f;
    border-color: #e32f2f;
    color: #fff;
}

/* Menu Content */
.menu-content {
    padding-bottom: 4rem;
}

.no-results-card {
    text-align: center;
    padding: 4rem 2rem;
    background: #111111;
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 8px;
    margin: 2rem 0;
}

.no-results-card i {
    font-size: 3.5rem;
    color: rgba(255,255,255,0.2);
    margin-bottom: 1rem;
}

.no-results-card h3 {
    color: #fff;
    margin-bottom: 0.5rem;
}

.no-results-card p {
    color: rgba(255,255,255,0.65);
}

.btn-red {
    background: #e32f2f;
    border-color: #e32f2f;
    color: #fff;
    transition: all 0.2s ease;
}

.btn-red:hover {
    background: #ff4136;
    border-color: #ff4136;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(227,47,47,0.3);
}

/* Food Grid */
.food-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.8rem;
    margin: 2rem 0;
}

@media (max-width: 768px) {
    .food-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1.2rem;
    }
}

@media (max-width: 480px) {
    .food-grid {
        grid-template-columns: 1fr;
    }
}

/* Food Card */
.food-card-wrapper {
    perspective: 1000px;
}

.food-card {
    background: #111111;
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 10px;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    transition: all 0.32s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.food-card:hover {
    transform: translateY(-4px);
    border-color: rgba(255,255,255,0.12);
    box-shadow: 0 20px 50px rgba(227,47,47,0.15), 0 15px 40px rgba(0,0,0,0.4);
}

/* Image Container */
.food-image-container {
    position: relative;
    overflow: hidden;
    background: #0a0a0a;
    aspect-ratio: 1;
    flex-shrink: 0;
}

.food-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease, filter 0.4s ease;
    display: block;
}

.food-card:hover .food-image {
    transform: scale(1.08);
    filter: brightness(1.1);
}

.food-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(227,47,47,0.08), rgba(0,0,0,0.2));
    color: rgba(255,255,255,0.2);
}

.food-image-placeholder i {
    font-size: 3.5rem;
}

/* Badges */
.food-category-badge,
.food-vegetarian-badge {
    position: absolute;
    padding: 0.35rem 0.8rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    backdrop-filter: blur(8px);
    text-transform: uppercase;
}

.food-category-badge {
    top: 12px;
    left: 12px;
    background: rgba(227,47,47,0.9);
    color: #fff;
}

.food-vegetarian-badge {
    top: 12px;
    right: 12px;
    background: rgba(34,197,94,0.9);
    color: #fff;
}

/* Card Content */
.food-card-content {
    display: flex;
    flex-direction: column;
    flex: 1;
    padding: 1.4rem;
}

.food-name {
    font-size: 1.15rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 0.5rem;
    line-height: 1.3;
    letter-spacing: -0.01em;
}

.food-description {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.65);
    line-height: 1.45;
    margin-bottom: auto;
    min-height: 2.4em;
}

/* Food Footer */
.food-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding-top: 1.2rem;
    border-top: 1px solid rgba(255,255,255,0.05);
    margin-top: 1.2rem;
}

.food-price {
    display: flex;
    align-items: baseline;
    gap: 0.2rem;
}

.currency {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.7);
}

.amount {
    font-size: 1.6rem;
    font-weight: 700;
    color: #e32f2f;
    letter-spacing: -0.01em;
}

/* Qty Input Group */
.qty-input-group {
    display: flex;
    align-items: center;
    background: #0a0a0a;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 4px;
    overflow: hidden;
    flex: 1;
}

.qty-btn {
    background: transparent;
    border: none;
    color: rgba(255,255,255,0.7);
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: bold;
    font-size: 1rem;
}

.qty-btn:hover {
    background: rgba(227,47,47,0.2);
    color: #e32f2f;
}

.qty-input {
    flex: 1;
    border: none;
    background: transparent;
    color: #fff;
    text-align: center;
    padding: 0.25rem 0.5rem;
    font-weight: 600;
    font-size: 0.9rem;
}

.qty-input:focus {
    outline: none;
}

/* Add to Cart Button */
.add-to-cart-form {
    display: flex;
    gap: 0.5rem;
}

.btn-add-to-cart {
    background: #e32f2f;
    border: none;
    color: #fff;
    width: 36px;
    height: 36px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: 600;
    flex-shrink: 0;
}

.btn-add-to-cart:hover {
    background: #ff4136;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(227,47,47,0.3);
}

.btn-add-to-cart:active {
    transform: translateY(0);
}

/* Responsive */
@media (max-width: 768px) {
    .menu-hero {
        padding: 2.5rem 0;
        margin-bottom: 2rem;
    }

    .menu-hero-title {
        font-size: 2rem;
    }

    .menu-search-section {
        padding: 1.5rem 0 2rem;
        margin-bottom: 1.5rem;
    }

    .food-card-content {
        padding: 1rem;
    }

    .food-name {
        font-size: 1rem;
    }

    .food-description {
        font-size: 0.8rem;
    }

    .amount {
        font-size: 1.4rem;
    }
}
</style>

<script>
function incrementQty(btn) {
    const input = btn.parentElement.querySelector('.qty-input');
    const max = parseInt(input.getAttribute('max'));
    if (parseInt(input.value) < max) {
        input.value = parseInt(input.value) + 1;
    }
}

function decrementQty(btn) {
    const input = btn.parentElement.querySelector('.qty-input');
    const min = parseInt(input.getAttribute('min'));
    if (parseInt(input.value) > min) {
        input.value = parseInt(input.value) - 1;
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
