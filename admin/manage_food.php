<?php
/**
 * Manage Food Items
 * Food Ordering System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/admin.php';

$page_title = 'Manage Food Items';

requireAdmin();
checkSessionTimeout();

$error = '';
$success = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'add') {
        $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
        $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
        $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        $is_vegetarian = isset($_POST['is_vegetarian']) ? 1 : 0;
        $preparation_time = isset($_POST['preparation_time']) ? (int)$_POST['preparation_time'] : 30;
        $quantity_in_stock = isset($_POST['quantity_in_stock']) ? (int)$_POST['quantity_in_stock'] : 0;
        
        $result = addFoodItem($category_id, $name, $description, $price, $is_vegetarian, $preparation_time, $quantity_in_stock);
        if ($result['status']) {
            $success = $result['message'];
            $food_id = $conn->insert_id;
            
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
                uploadFoodImage($_FILES['image'], $food_id);
            }
        } else {
            $error = $result['message'];
        }
    }
    
    if ($action === 'delete') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $result = deleteFoodItem($id);
        if ($result['status']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

$categories = getAllCategories();
$food_items = getAllFoodItems();

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Manage Food Items</h2>
    
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
    
    <!-- Add Food Form -->
    <div class="manage-food-dropdown-scope">
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Add New Food Item</h5>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="category_id" class="form-label">Category *</label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Food Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="price" class="form-label">Price *</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="preparation_time" class="form-label">Preparation Time (min)</label>
                        <input type="number" class="form-control" id="preparation_time" name="preparation_time" value="30" min="5" max="120">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="quantity_in_stock" class="form-label">Quantity in Stock</label>
                        <input type="number" class="form-control" id="quantity_in_stock" name="quantity_in_stock" value="0">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="image" class="form-label">Food Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <small class="text-muted">Maximum 5MB. Formats: JPG, PNG, GIF</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_vegetarian" name="is_vegetarian">
                            <label class="form-check-label" for="is_vegetarian">
                                Vegetarian Item
                            </label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Food Item
                </button>
            </form>
        </div>
    </div>
    
    <!-- Food Items Table -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Food Items List</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Vegetarian</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($food_items as $food): ?>
                        <tr>
                            <td>
                                <?php if (!empty($food['image'])): ?>
                                    <img src="<?php echo SITE_URL; ?>uploads/food/<?php echo htmlspecialchars($food['image']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 3px;">
                                <?php else: ?>
                                    <span class="badge bg-light text-muted">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($food['name']); ?></td>
                            <td><?php echo htmlspecialchars($food['category_name']); ?></td>
                            <td><?php echo formatPrice($food['price']); ?></td>
                            <td><?php echo $food['is_vegetarian'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'; ?></td>
                            <td><?php echo $food['quantity_in_stock']; ?></td>
                            <td>
                                <?php if ($food['is_available']): ?>
                                    <span class="badge bg-success">Available</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Not Available</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit_food.php?id=<?php echo $food['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $food['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
