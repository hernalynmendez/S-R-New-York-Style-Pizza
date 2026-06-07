<?php
/**
 * Edit Food Item
 * Food Ordering System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/admin.php';

$page_title = 'Edit Food Item';

requireAdmin();
checkSessionTimeout();

$food_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($food_id === 0) {
    header('Location: ' . SITE_URL . 'admin/manage_food.php');
    exit;
}

$food = getFoodItemById($food_id);

if (!$food) {
    header('Location: ' . SITE_URL . 'admin/manage_food.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
    $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $is_vegetarian = isset($_POST['is_vegetarian']) ? 1 : 0;
    $preparation_time = isset($_POST['preparation_time']) ? (int)$_POST['preparation_time'] : 30;
    $quantity_in_stock = isset($_POST['quantity_in_stock']) ? (int)$_POST['quantity_in_stock'] : 0;
    
    $stmt = $conn->prepare("UPDATE food_items SET category_id = ?, name = ?, description = ?, price = ?, is_vegetarian = ?, preparation_time = ?, quantity_in_stock = ? WHERE id = ?");
    $stmt->bind_param("issdiiii", $category_id, $name, $description, $price, $is_vegetarian, $preparation_time, $quantity_in_stock, $food_id);
    
    if ($stmt->execute()) {
        $success = 'Food item updated successfully';
        $food = getFoodItemById($food_id);
        $stmt->close();
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
            uploadFoodImage($_FILES['image'], $food_id);
        }
    } else {
        $error = 'Failed to update food item';
        $stmt->close();
    }
}

$categories = getAllCategories();

require_once '../includes/header.php';
?>

<div class="container py-5">
    <a href="manage_food.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Back to Food Items
    </a>
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Edit Food Item</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Category *</label>
                                <select class="form-control" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] === $food['category_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Food Name *</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($food['name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($food['description']); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="price" class="form-label">Price *</label>
                                <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo $food['price']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="preparation_time" class="form-label">Preparation Time (min)</label>
                                <input type="number" class="form-control" id="preparation_time" name="preparation_time" value="<?php echo $food['preparation_time']; ?>" min="5" max="120">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="quantity_in_stock" class="form-label">Quantity in Stock</label>
                                <input type="number" class="form-control" id="quantity_in_stock" name="quantity_in_stock" value="<?php echo $food['quantity_in_stock']; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="image" class="form-label">Food Image</label>
                                <?php if (!empty($food['image'])): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo SITE_URL; ?>uploads/food/<?php echo htmlspecialchars($food['image']); ?>" style="max-width: 200px; height: auto; border-radius: 5px;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <small class="text-muted">Leave empty to keep current image</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_vegetarian" name="is_vegetarian" <?php echo $food['is_vegetarian'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_vegetarian">
                                        Vegetarian Item
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Food Item
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
