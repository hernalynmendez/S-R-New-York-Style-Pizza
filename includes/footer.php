<?php
/**
 * Footer
 * Food Ordering System
 */
?>
    <!-- Footer -->
    <footer class="bg-black text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><i class="fas fa-pizza-slice"></i> S&R New York Style Pizza</h5>
                    <p>BIG TASTE, BIT FUN! Crafted for premium New York-style pizza lovers.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo SITE_URL; ?>" class="text-white-50">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>user/menu.php" class="text-white-50">Menu</a></li>
                        <li><a href="<?php echo SITE_URL; ?>user/orders.php" class="text-white-50">Orders</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Contact Us</h5>
                    <p class="text-white-50">
                        Email: s&rnewyorkstylepizza.com<br>
                        Phone: +63 1234 567-0198<br>
                        Address: Rosario Street, Cavite, Philippines
                    </p>
                </div>
            </div>
            <hr class="bg-white-50">
            <div class="text-center text-white-50">
                <p>&copy; 2026 S&R New York Style Pizza. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?php echo SITE_URL; ?>js/script.js"></script>
    <?php if (!empty($extra_js) && is_array($extra_js)): ?>
        <?php foreach ($extra_js as $jsFile): ?>
            <script src="<?php echo $jsFile; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
