# Food Ordering System

A complete web-based food ordering system built with PHP, MySQL, HTML, CSS, JavaScript, and Bootstrap.

## Features

### 1. User Authentication
- User registration with validation
- Secure login with password hashing
- Session management
- Logout functionality
- Profile editing
- Change password
- Profile picture upload

### 2. Food Menu
- Display food items with images, descriptions, and prices
- Search functionality
- Food categorization
- Responsive card layout
- Vegetarian item indicators

### 3. Shopping Cart
- Add items to cart
- Update quantities
- Remove items
- Clear cart
- Real-time cart count
- Cart total calculation

### 4. Order Management
- Place orders
- Order summary
- Order history
- Cancel orders
- Order status tracking
- Order details view

### 5. Payment Module
- Cash on Delivery option
- Online payment option
- Payment confirmation
- Payment status tracking

### 6. Admin Panel
- Dashboard with statistics
- Manage food items (add, edit, delete)
- Manage users (view, activate, deactivate)
- Manage orders (view, update status)
- Manage categories
- Activity logging

### 7. Security
- Password hashing using bcrypt
- Prepared statements to prevent SQL injection
- Session-based authentication
- Security headers
- Input validation and sanitization

### 8. UI/UX
- Modern responsive design
- Mobile-friendly interface
- Bootstrap 5 framework
- Intuitive navigation
- Professional styling

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- WAMP Server (or similar local server)
- Modern web browser

## Installation Steps

### 1. Setup Database

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `food_ordering_system`
3. Import the database schema:
   - Go to the Import tab
   - Select the file: `database/food_ordering_system.sql`
   - Click Import

### 2. Configure Database Connection

1. Open `includes/config.php`
2. Update database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASSWORD', ''); // If you have a password
   define('DB_NAME', 'food_ordering_system');
   ```

### 3. Create Upload Directories

The following directories should have write permissions:
- `uploads/food/` - For food item images
- `uploads/profiles/` - For user profile pictures

### 4. Access the Application

1. Start WAMP Server
2. Navigate to: `http://localhost/FoodSystem/`

## Default Admin Credentials

After importing the database, use these credentials:
- **Username**: admin
- **Password**: admin123 (change this immediately)

## Folder Structure

```
FoodSystem/
├── admin/                      # Admin panel pages
│   ├── dashboard.php
│   ├── manage_food.php
│   ├── manage_users.php
│   ├── manage_orders.php
│   ├── manage_categories.php
│   └── ... (other admin files)
├── user/                       # User pages
│   ├── register.php
│   ├── login.php
│   ├── menu.php
│   ├── cart.php
│   ├── checkout.php
│   ├── orders.php
│   └── ... (other user files)
├── includes/                   # PHP includes
│   ├── config.php             # Database configuration
│   ├── auth.php               # Authentication functions
│   ├── functions.php          # Utility functions
│   ├── admin.php              # Admin functions
│   ├── header.php             # Navigation header
│   └── footer.php             # Footer
├── css/                        # Stylesheets
│   └── style.css              # Main stylesheet
├── js/                         # JavaScript
│   └── script.js              # Main script
├── database/                   # Database files
│   └── food_ordering_system.sql
├── uploads/                    # User uploads
│   ├── food/                  # Food images
│   └── profiles/              # Profile pictures
├── index.php                   # Home page
└── README.md                   # This file
```

## Database Tables

- **users** - User accounts
- **categories** - Food categories
- **food_items** - Menu items
- **orders** - Customer orders
- **order_items** - Items in each order
- **payments** - Payment records
- **reviews** - Customer reviews
- **delivery_partners** - Delivery service info
- **activity_logs** - Admin activity tracking

## Key Features Explanation

### User Flow

1. **Registration/Login**
   - Users register with email and password
   - Secure login with session management

2. **Browse Menu**
   - View food items by category
   - Search functionality
   - Add items to cart

3. **Checkout**
   - Provide delivery address
   - Select payment method
   - Add special instructions
   - Place order

4. **Order Tracking**
   - View order history
   - Track order status
   - Cancel orders if needed

5. **Account Management**
   - Edit profile
   - Upload profile picture
   - Change password

### Admin Flow

1. **Dashboard**
   - View statistics
   - Quick links to management sections

2. **Food Management**
   - Add new food items
   - Edit existing items
   - Upload images
   - Delete items

3. **User Management**
   - View all users
   - Activate/Deactivate accounts
   - View user details

4. **Order Management**
   - View all orders
   - Update order status
   - Update payment status
   - View order details

## Security Features

1. **Password Security**
   - Passwords hashed using bcrypt
   - `password_hash()` and `password_verify()`

2. **SQL Injection Prevention**
   - Prepared statements with bound parameters
   - Input validation

3. **Session Security**
   - Secure session handling
   - Session timeout (30 minutes)
   - Session regeneration

4. **Input Validation**
   - Server-side validation
   - File type checking
   - Size limitations

## Payment Methods

### Cash on Delivery
- No immediate payment required
- Payment collected at delivery

### Online Payment
- Ready for integration with payment gateways
- Update payment status in admin panel

## Usage Tips

### For Users

1. Register a new account
2. Login to your account
3. Browse food items or use search
4. Add items to cart
5. Proceed to checkout
6. Select delivery address and payment method
7. Place order
8. Track order status in "My Orders"

### For Admins

1. Login with admin credentials
2. Access admin dashboard
3. Manage food items (CRUD operations)
4. Monitor orders and update status
5. Manage users
6. View activity logs

## Customization

### Adding New Payment Methods

Edit `user/checkout.php` to add new payment options:
```php
<div class="form-check mb-3">
    <input class="form-check-input" type="radio" name="payment_method" value="new_method">
    <label class="form-check-label">New Payment Method</label>
</div>
```

### Changing Color Theme

Edit `css/style.css` to modify colors:
```css
:root {
    --primary-color: #007bff;
    --secondary-color: #6c757d;
    /* ... */
}
```

### Adding Email Notifications

Update `includes/functions.php` to add email functionality:
```php
function sendOrderConfirmationEmail($email, $order) {
    // Implementation here
}
```

## Troubleshooting

### Database Connection Error
- Check database credentials in `includes/config.php`
- Ensure MySQL server is running
- Verify database exists

### Upload Directory Permissions
- Ensure `uploads/` directories have write permissions
- Run: `chmod 755 uploads/`

### Session Issues
- Clear browser cookies
- Check PHP session save path
- Verify session.save_path in php.ini

### Password Reset
- To reset admin password, update the database:
```sql
UPDATE users SET password = '$2y$10$hash' WHERE username = 'admin';
```

## Testing Account

### Demo User
- **Username**: demo
- **Password**: demo123 (if created in SQL)

Note: Change default passwords for production use!

## Future Enhancements

1. Integration with payment gateways (Stripe, PayPal)
2. Email notifications
3. SMS notifications
4. Rating and review system
5. Coupon/discount codes
6. Delivery partner integration
7. Mobile app
8. Real-time order tracking with GPS
9. Analytics and reporting
10. Push notifications

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review the code comments
3. Check error logs in your server

## License

This project is provided as-is for educational purposes.

## Version

Version 1.0 - May 2026

---

**Enjoy your Food Ordering System!**
