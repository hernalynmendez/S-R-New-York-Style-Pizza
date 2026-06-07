<?php
/**
 * Authentication Functions
 * Food Ordering System
 */

// Register new user
function registerUser($username, $email, $password, $first_name, $last_name) {
    global $conn;
    
    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        return array('status' => false, 'message' => 'All fields are required');
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return array('status' => false, 'message' => 'Invalid email format');
    }
    
    // Check if username exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return array('status' => false, 'message' => 'Username already exists');
    }
    $stmt->close();
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return array('status' => false, 'message' => 'Email already exists');
    }
    $stmt->close();
    
    // Validate password strength
    if (strlen($password) < 6) {
        return array('status' => false, 'message' => 'Password must be at least 6 characters');
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $hashed_password, $first_name, $last_name);
    
    if ($stmt->execute()) {
        $stmt->close();
        return array('status' => true, 'message' => 'Registration successful');
    } else {
        $stmt->close();
        return array('status' => false, 'message' => 'Registration failed');
    }
}

// Login user
function loginUser($username, $password) {
    global $conn;
    
    if (empty($username) || empty($password)) {
        return array('status' => false, 'message' => 'Username and password required');
    }
    
    $stmt = $conn->prepare("SELECT id, username, password, is_admin, is_active FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return array('status' => false, 'message' => 'Invalid credentials');
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Check if account is active
    if (!$user['is_active']) {
        return array('status' => false, 'message' => 'Account is inactive');
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        return array('status' => false, 'message' => 'Invalid credentials');
    }
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['is_admin'] = $user['is_admin'];
    $_SESSION['login_time'] = time();
    
    return array('status' => true, 'message' => 'Login successful');
}

// Logout user
function logoutUser() {
    session_destroy();
    return array('status' => true, 'message' => 'Logged out successfully');
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === 1;
}

// Get current user
function getCurrentUser() {
    global $conn;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result->fetch_assoc();
}

// Update user profile
function updateUserProfile($first_name, $last_name, $phone, $address, $city, $state, $postal_code, $country) {
    global $conn;
    
    if (!isLoggedIn()) {
        return array('status' => false, 'message' => 'User not logged in');
    }
    
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, address = ?, city = ?, state = ?, postal_code = ?, country = ? WHERE id = ?");
    $stmt->bind_param("ssssssssi", $first_name, $last_name, $phone, $address, $city, $state, $postal_code, $country, $user_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        return array('status' => true, 'message' => 'Profile updated successfully');
    } else {
        $stmt->close();
        return array('status' => false, 'message' => 'Failed to update profile');
    }
}

// Change password
function changePassword($old_password, $new_password, $confirm_password) {
    global $conn;
    
    if (!isLoggedIn()) {
        return array('status' => false, 'message' => 'User not logged in');
    }
    
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        return array('status' => false, 'message' => 'All fields are required');
    }
    
    if ($new_password !== $confirm_password) {
        return array('status' => false, 'message' => 'New passwords do not match');
    }
    
    if (strlen($new_password) < 6) {
        return array('status' => false, 'message' => 'Password must be at least 6 characters');
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Get current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Verify old password
    if (!password_verify($old_password, $user['password'])) {
        return array('status' => false, 'message' => 'Old password is incorrect');
    }
    
    // Hash new password
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    
    // Update password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        return array('status' => true, 'message' => 'Password changed successfully');
    } else {
        $stmt->close();
        return array('status' => false, 'message' => 'Failed to change password');
    }
}

// Upload profile image
function uploadProfileImage($file) {
    global $conn;
    
    if (!isLoggedIn()) {
        return array('status' => false, 'message' => 'User not logged in');
    }
    
    if ($file['size'] > 5242880) { // 5MB limit
        return array('status' => false, 'message' => 'File size exceeds 5MB limit');
    }
    
    $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
    if (!in_array($file['type'], $allowed_types)) {
        return array('status' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed');
    }
    
    $filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $filepath = UPLOAD_PROFILE_PATH . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        $stmt->bind_param("si", $filename, $user_id);
        $stmt->execute();
        $stmt->close();
        
        return array('status' => true, 'message' => 'Profile image updated', 'filename' => $filename);
    }
    
    return array('status' => false, 'message' => 'Failed to upload file');
}
?>
