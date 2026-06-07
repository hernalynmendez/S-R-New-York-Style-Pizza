<?php
/**
 * Edit Category
 * Food Ordering System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/admin.php';

$page_title = 'Edit Category';

requireAdmin();
checkSessionTimeout();

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id === 0) {
    header('Location: ' . SITE_URL . 'admin/manage_categories.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();
$stmt->close();

if (!$category) {
    header('Location: ' . SITE_URL . 'admin/manage_categories.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
    $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
    
    if (empty($name)) {
        $error = 'Category name is required';
    } else {
        $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $description, $category_id);
        
        if ($stmt->execute()) {
            $success = 'Category updated successfully';
            $category['name'] = $name;
            $category['description'] = $description;
        } else {
            $error = 'Failed to update category';
        }
        $stmt->close();
    }
}

require_once '../includes/header.php';
?>

<div class="container py-5">
    <a href="manage_categories.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Back to Categories
    </a>
    
    <div class="row justify-content-center">
        <div class="col-md-6">
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
                    <h5 class="mb-0">Edit Category</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name *</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($category['description']); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Update Category
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
