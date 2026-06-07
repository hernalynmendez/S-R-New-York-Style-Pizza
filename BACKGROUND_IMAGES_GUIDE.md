# Background Images Guide
## Food Order System - Cart, Orders & Menu Pages

---

## Overview

Background images have been configured for three main pages to enhance visual appeal and user experience. The system uses a layered approach with:
- **Base Background Image** (from uploads folder)
- **Gradient Overlay** (semi-transparent gradients)
- **Animated Patterns** (subtle radial gradients)

---

## Page Configurations

### 1. 📋 Menu Page
**Class:** `.menu-hero`  
**Location:** user/menu.php (lines 28-34)

#### Current Background Image Path
```
../uploads/profiles/homepage.jpg
```

#### Styling Features
- **Gradient Overlay:** 135° gradient from red (#e32f2f with 12% opacity) to black (80-90% opacity)
- **Secondary Gradient:** Directional fade from right to left
- **Animated Pattern:** Dual radial gradients (10%, 30%) and (90%, 70%)
- **Background Attachment:** Fixed (parallax effect)
- **Padding:** 6rem (top and bottom)

#### CSS Properties
```css
background: linear-gradient(135deg, rgba(227, 47, 47, 0.12) 0%, rgba(0, 0, 0, 0.8) 50%, rgba(0, 0, 0, 0.9) 100%),
            linear-gradient(to right, rgba(255, 87, 69, 0.08), transparent 60%),
            url('../uploads/profiles/homepage.jpg');
background-size: cover;
background-position: center center;
background-attachment: fixed;
```

#### Text Styling
- **Title:** 3.5rem, 800 font-weight, text-shadow for depth
- **Subtitle:** 1.3rem, 300 font--weight, subtle shadow

---

### 2. 🛒 Cart Page
**Class:** `.page-background-cover`  
**Location:** user/cart.php (line 22)

#### Current Background Image Path
```
../uploads/profiles/order-cart-bg.jpg
```

#### Styling Features
- **Gradient Overlay:** 135° gradient from red (8% opacity) to black (75-85% opacity)
- **Secondary Gradient:** Directional red accent (right side transparent)
- **Animated Pattern:** Radial gradients for depth
- **Background Attachment:** Fixed (parallax effect)
- **Min Height:** 100vh (full viewport)
- **Padding:** 4rem (top and bottom)

#### CSS Properties
```css
background: linear-gradient(135deg, rgba(227, 47, 47, 0.08) 0%, rgba(0, 0, 0, 0.75) 50%, rgba(0, 0, 0, 0.85) 100%),
            linear-gradient(to right, rgba(255, 87, 69, 0.05), transparent 50%, rgba(227, 47, 47, 0.05)),
            url('../uploads/profiles/order-cart-bg.jpg');
background-size: cover;
background-position: center center;
background-attachment: fixed;
```

#### Enhanced Elements
- **Card Styling:** Red border (rgba 0.2), 92% background opacity, 10px backdrop blur
- **Card Hover:** Red border (rgba 0.4), 70px box-shadow with red glow
- **Table Headers:** Red gradient background, 2px red bottom border
- **Table Rows:** Hover effect with red tint (rgba 0.08)

---

### 3. 📦 Orders Page
**Class:** `.page-background-cover`  
**Location:** user/orders.php (line 30)

#### Current Background Image Path
```
../uploads/profiles/order-cart-bg.jpg
```

#### Styling Features
- **Same as Cart Page** (uses identical background configuration)
- **Additional Enhancements:** Status badges with gradient colors

#### Status Badge Colors
- **Pending:** Orange gradient (FF9800 → FF6F00)
- **Delivered:** Green gradient (4CAF50 → 388E3C)
- **Cancelled:** Red gradient (F44336 → D32F2F)
- **Preparing:** Blue gradient (2196F3 → 1565C0)

#### Card Styling
- **Order Cards:** Red accent border, hover scale transform (+8px), red glow shadow
- **Title Color:** Pure white (#fff)
- **Subtitle Color:** 50% opacity white

---

## Image File Locations

### Required Image Files
Place these images in: `uploads/profiles/`

```
FoodSystem/uploads/profiles/
├── homepage.jpg           ← Used for menu-hero
├── order-cart-bg.jpg      ← Used for cart & orders pages
├── loginlogo.jpg          (existing)
├── loginregister.jpg      (existing)
├── logo2.jpg              (existing)
├── logo3.jpg              (existing)
└── lr2.jpg                (existing)
```

### Recommended Image Specifications

#### For Menu Page (homepage.jpg)
- **Dimensions:** 1920x1080px or wider
- **Format:** JPG (optimized)
- **Content Suggestions:**
  - Pizza preparation/cooking
  - Restaurant interior
  - Premium food presentation
  - New York skyline
- **Colors:** Dark tones with red accents work best
- **File Size:** < 500KB (optimize for web)

#### For Cart & Orders Pages (order-cart-bg.jpg)
- **Dimensions:** 1920x1080px or wider
- **Format:** JPG (optimized)
- **Content Suggestions:**
  - Food items arranged beautifully
  - Delivery/packaging theme
  - Order preparation
  - Kitchen/counter scene
- **Colors:** Dark backgrounds with food texture
- **File Size:** < 500KB (optimize for web)

---

## Customization Guide

### To Change Background Image

#### Option 1: Replace Existing Image
Simply upload a new image with the same filename to overwrite:
- `uploads/profiles/homepage.jpg` (for menu)
- `uploads/profiles/order-cart-bg.jpg` (for cart/orders)

#### Option 2: Update CSS Path
Edit `css/style.css`:

**For Menu Page:**
```css
.menu-hero {
    background: linear-gradient(...),
                url('../uploads/profiles/YOUR_IMAGE.jpg');
}
```

**For Cart & Orders:**
```css
.page-background-cover {
    background: linear-gradient(...),
                url('../uploads/profiles/YOUR_IMAGE.jpg');
}
```

### To Adjust Gradient Overlay

Edit the gradient values in CSS:

```css
/* Current: Red accent (12% opacity) to Black (80-90% opacity) */
linear-gradient(135deg, rgba(227, 47, 47, 0.12) 0%, rgba(0, 0, 0, 0.8) 50%, rgba(0, 0, 0, 0.9) 100%)

/* Example: More transparent (10% to 70% opacity) */
linear-gradient(135deg, rgba(227, 47, 47, 0.10) 0%, rgba(0, 0, 0, 0.7) 50%, rgba(0, 0, 0, 0.80) 100%)

/* Example: Stronger red accent (20% to 85% opacity) */
linear-gradient(135deg, rgba(227, 47, 47, 0.20) 0%, rgba(0, 0, 0, 0.85) 50%, rgba(0, 0, 0, 0.92) 100%)
```

### To Disable Parallax Effect
Remove or change `background-attachment: fixed` to `scroll`:

```css
.menu-hero {
    background-attachment: scroll; /* Change from 'fixed' */
}
```

### To Adjust Overlay Pattern
Modify the radial gradient positions in `.menu-hero::before` or `.page-background-cover::before`:

```css
background-image: 
    radial-gradient(circle at 15% 25%, rgba(227, 47, 47, 0.05) 0%, transparent 50%),
    radial-gradient(circle at 85% 75%, rgba(255, 87, 69, 0.03) 0%, transparent 50%);
```

---

## Performance Optimization

### Best Practices Implemented

✅ **Image Optimization**
- JPG format used for smaller file sizes
- Recommended max size: 500KB
- Compression applied to maintain quality

✅ **CSS Layering**
- Gradients applied first (no image needed for fallback)
- Images load only when needed
- Fallback to solid gradients if image fails

✅ **Background Attachment: Fixed**
- Creates parallax effect on scroll
- Improves perceived depth
- Slight performance overhead (acceptable for modern browsers)

✅ **Backdrop Filter**
- 10px blur on cards creates glass-morphism effect
- Subtle enhancement without major performance impact

### Browser Compatibility
- ✅ Chrome 80+
- ✅ Firefox 75+
- ✅ Safari 13+
- ✅ Edge 80+
- ✅ Mobile browsers (iOS Safari 13+, Chrome Android)

---

## Visual Design Elements

### Color Scheme
- **Primary Red:** #e32f2f (rgba: 227, 47, 47)
- **Accent Red:** #ff5745 (rgba: 255, 87, 69)
- **Dark Background:** #0a0a0a to #1a1a1a
- **Text Color:** #ffffff (white)
- **Muted Text:** rgba(255, 255, 255, 0.65)

### Typography
- **Font Family:** Inter, system-ui, sans-serif
- **Menu Title:** 3.5rem, bold (800 weight)
- **Menu Subtitle:** 1.3rem, light (300 weight)
- **Card Titles:** 1.1rem, bold (700 weight)
- **Body Text:** 1rem, normal (400 weight)

### Shadows & Effects
- **Card Shadows:** 0 20px 60px rgba(0,0,0,0.4)
- **Hover Glow:** Red-tinted shadow with opacity variation
- **Text Shadow:** 0 4px 12px rgba(0,0,0,0.5-0.6)
- **Blur Effects:** 10px backdrop blur on cards

---

## Testing Checklist

- [ ] Menu page loads with hero background image
- [ ] Cart page displays with background image and gradient overlay
- [ ] Orders page displays with background image and gradient overlay
- [ ] All cards are readable over the background
- [ ] Hover effects work smoothly on all elements
- [ ] Status badges display correctly with gradient colors
- [ ] Search bar is visible and functional
- [ ] Category tabs are properly styled
- [ ] Mobile responsiveness works (background scales properly)
- [ ] Performance is acceptable (no excessive lag)
- [ ] Images load without breaking if missing

---

## Troubleshooting

### Background image not showing
1. Verify image file exists: `uploads/profiles/` folder
2. Check file permissions (readable)
3. Clear browser cache (Ctrl+Shift+Delete)
4. Verify CSS path is correct
5. Check browser console for 404 errors

### Text not readable over background
1. Adjust gradient overlay opacity (increase darkness)
2. Change from `rgba(..., 0.08)` to `rgba(..., 0.12)` or higher
3. Add additional text-shadow for better contrast

### Performance issues
1. Optimize image size (use web compression tools)
2. Disable parallax: change `background-attachment: fixed` to `scroll`
3. Reduce image quality slightly
4. Use WebP format for newer browsers

### Mobile display issues
1. Check responsive CSS (bottom of style.css)
2. Adjust padding/font-size for smaller screens
3. Test on actual mobile devices
4. Use browser developer tools (F12) to test viewport sizes

---

## File References

### Main CSS File
📄 [css/style.css](css/style.css) - Lines 578-810

### Page Files Using Backgrounds
- 📄 [user/menu.php](user/menu.php) - Lines 28-34 (.menu-hero)
- 📄 [user/cart.php](user/cart.php) - Line 22 (.page-background-cover)
- 📄 [user/orders.php](user/orders.php) - Line 30 (.page-background-cover)

### Image Folder
📁 [uploads/profiles/](uploads/profiles/) - Background image storage

---

## Summary

Three pages now have beautiful, professional background images with:
- Multi-layered gradient overlays for depth
- Red accent branding matching the food system theme
- Parallax scrolling effects for modern feel
- Glass-morphism card styling with blur effects
- Responsive design for all screen sizes
- Smooth hover animations and transitions

The system gracefully handles missing images by falling back to pure gradient backgrounds, ensuring functionality even if image files are unavailable.

---

**Last Updated:** June 1, 2026  
**Version:** 1.0
