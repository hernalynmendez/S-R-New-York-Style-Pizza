<?php
/**
 * Manage Categories
 * Food Ordering System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/admin.php';

$page_title = 'Manage Categories';

requireAdmin();
checkSessionTimeout();

$error = '';
$success = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'add') {
        $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
        $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
        
        if (empty($name)) {
            $error = 'Category name is required';
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $description);
            
            if ($stmt->execute()) {
                $success = 'Category added successfully';
            } else {
                $error = 'Failed to add category';
            }
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $success = 'Category deleted successfully';
        } else {
            $error = 'Failed to delete category';
        }
        $stmt->close();
    }
}

$categories = getAllCategories();

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Manage Categories</h2>
    
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
    
    <!-- Add Category Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Add New Category</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="mb-3">
                    <label for="name" class="form-label">Category Name *</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Category
                </button>
            </form>
        </div>
    </div>
    
    <!-- Categories Table -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Categories List</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><?php echo htmlspecialchars($category['description']); ?></td>
                            <td>
                                <?php if ($category['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit_category.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
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
