/* Food Ordering System - JavaScript */

// Run initialization when DOM is ready (works even if script loaded after DOMContentLoaded)
function onReady(fn) {
    if (document.readyState !== 'loading') {
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}

onReady(function() {
    // Initialize tooltips
    initializeTooltips();

    // Add form validation
    initializeFormValidation();

    // Handle dynamic content
    initializeDynamicContent();

    // Initialize Select2 on selects (if available)
    try {
        if (typeof $ !== 'undefined' && $.fn && $.fn.select2) {
            $('select, .form-select').not('.no-select2').each(function() {
                // Avoid double-initializing
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        width: '100%',
                        dropdownAutoWidth: true,
                        theme: 'default'
                    });
                }
            });

            // Ensure the Category select on Manage Food uses Select2 with proper dropdown parent
            try {
                var $cat = $('#category_id');
                if ($cat.length && !$cat.hasClass('select2-hidden-accessible')) {
                    var dropdownParent = $('.manage-food-dropdown-scope').length ? $('.manage-food-dropdown-scope') : $('body');
                    $cat.select2({
                        width: '100%',
                        dropdownAutoWidth: true,
                        theme: 'default',
                        dropdownParent: dropdownParent
                    });
                }
            } catch (eCat) {
                // Fail silently if Select2 isn't available or init errors
            }
        }
    } catch (e) {
        console.warn('Select2 init failed', e);
    }
});

/**
 * Initialize Bootstrap tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}

/**
 * Initialize dynamic content
 */
function initializeDynamicContent() {
    // Add quantity input handlers
    const quantityInputs = document.querySelectorAll('input[type="number"][name="quantity"]');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value < 1) {
                this.value = 1;
            }
            if (this.value > 10) {
                this.value = 10;
            }
        });
    });
}

/**
 * Add to cart with animation
 */
function addToCart(foodId, foodName, event) {
    // Add visual feedback; event may be undefined if called programmatically
    try {
        const btn = event && event.target ? event.target : document.activeElement;
        const originalText = btn && btn.innerHTML ? btn.innerHTML : null;
        if (btn && originalText !== null) {
            btn.innerHTML = '<i class="fas fa-check"></i> Added!';
            btn.disabled = true;
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);
        }
    } catch (e) {
        // fallback: no visual feedback
    }
}

/**
 * Remove item from cart with confirmation
 */
function removeFromCart(foodId) {
    if (confirm('Are you sure you want to remove this item?')) {
        // The form will be submitted
        return true;
    }
    return false;
}

/**
 * Update order status (admin)
 */
function updateOrderStatus(orderId, newStatus) {
    if (confirm('Update order status to ' + newStatus + '?')) {
        document.getElementById('order-form-' + orderId).submit();
    }
}

/**
 * Format currency display
 */
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

/**
 * Format date display
 */
function formatDate(dateString) {
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

/**
 * Show loading state
 */
function showLoading(message = 'Loading...') {
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'loading-indicator';
    loadingDiv.className = 'fixed-top d-flex align-items-center justify-content-center';
    loadingDiv.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
    loadingDiv.style.zIndex = '9999';
    loadingDiv.style.height = '100vh';
    
    loadingDiv.innerHTML = `
        <div class="text-center text-white">
            <div class="spinner mb-3"></div>
            <p>${message}</p>
        </div>
    `;
    
    document.body.appendChild(loadingDiv);
}

/**
 * Hide loading state
 */
function hideLoading() {
    const loadingDiv = document.getElementById('loading-indicator');
    if (loadingDiv) {
        loadingDiv.remove();
    }
}

/**
 * Show toast notification
 */
function showToast(message, type = 'success') {
    const toastDiv = document.createElement('div');
    toastDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    toastDiv.style.top = '20px';
    toastDiv.style.right = '20px';
    toastDiv.style.minWidth = '300px';
    toastDiv.style.zIndex = '9999';
    
    const icon = type === 'success' ? 'fa-check-circle' : 
                 type === 'error' ? 'fa-exclamation-circle' :
                 type === 'warning' ? 'fa-exclamation-triangle' :
                 'fa-info-circle';
    
    toastDiv.innerHTML = `
        <i class="fas ${icon}"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toastDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toastDiv.remove();
    }, 5000);
}

/**
 * Debounce function for search
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Get cart count via AJAX
 */
function updateCartCount() {
    fetch('get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            const cartBadge = document.querySelector('.cart-count');
            if (cartBadge) {
                if (data.count > 0) {
                    cartBadge.textContent = data.count;
                    cartBadge.style.display = 'inline-block';
                } else {
                    cartBadge.style.display = 'none';
                }
            }
        })
        .catch(error => console.error('Error:', error));
}

/**
 * Handle search with debouncing
 */
const handleSearch = debounce(function(searchTerm) {
    if (searchTerm.length > 2) {
        // Submit search form
        document.querySelector('.search-form').submit();
    }
}, 300);

/**
 * Initialize search functionality
 */
function initializeSearch() {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            handleSearch(e.target.value);
        });
    }
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}

/**
 * Filter table rows
 */
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');
    
    input.addEventListener('keyup', function() {
        const filter = this.value.toUpperCase();
        for (let i = 1; i < rows.length; i++) {
            const text = rows[i].textContent || rows[i].innerText;
            rows[i].style.display = text.toUpperCase().indexOf(filter) > -1 ? '' : 'none';
        }
    });
}

/**
 * Export table to CSV
 */
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    let csv = [];
    
    // Get headers
    const headers = [];
    table.querySelectorAll('thead th').forEach(th => {
        headers.push(th.textContent);
    });
    csv.push(headers.join(','));
    
    // Get rows
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => {
            row.push('"' + td.textContent.replace(/"/g, '""') + '"');
        });
        csv.push(row.join(','));
    });
    
    // Create download link
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename || 'export.csv';
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
}

/**
 * Print page
 */
function printPage(elementId = null) {
    if (elementId) {
        const printContent = document.getElementById(elementId).innerHTML;
        const originalContent = document.body.innerHTML;
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
    } else {
        window.print();
    }
}

/**
 * Confirm action with custom message
 */
function confirmAction(message = 'Are you sure?') {
    return confirm(message);
}

/**
 * Initialize page on load
 */
window.addEventListener('load', function() {
    initializeSearch();
});
