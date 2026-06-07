<?php
/**
 * Contact Page
 * Food Ordering System
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$page_title = 'Contact Us';

$success = '';
$error = '';

// Note: This is a simple contact form. 
// To enable email: uncomment mail() function below and configure your email settings

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? sanitize($_POST['subject']) : '';
    $message = isset($_POST['message']) ? sanitize($_POST['message']) : '';
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'All fields are required';
    } else {
        // Store message in database (optional)
        $success = 'Thank you for your message. We will contact you soon!';
        
        // To enable email, uncomment this:
        // $to = 'info@foodsystem.com';
        // $headers = "From: " . $email . "\r\n";
        // mail($to, $subject, $message, $headers);
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4">Contact Us</h2>
            
            <div class="row mb-5">
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-map-marker-alt text-primary" style="font-size: 2rem;"></i>
                            <h5 class="card-title mt-3">Location</h5>
                            <p class="card-text">Rosario Street, Cavite, Philippines<br><br>Open daily 11am–11pm</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-phone text-primary" style="font-size: 2rem;"></i>
                            <h5 class="card-title mt-3">Contact</h5>
                            <p class="card-text">+63 1234 567-0198<br>s&rnewyorkstylepizza.com<br>Fresh slices, fast support</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Send us a Message</h5>
                </div>
                <div class="card-body">
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
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-send"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
