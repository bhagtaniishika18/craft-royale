<?php
// Get current page name to set active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <div class="logo-container">
            <div class="logo-icon">✦</div>
            <h2>Craft <span>Royale</span></h2>
        </div>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        
        <!-- Manage Products with Dropdown -->
        <div class="nav-group">
            <a href="manage_products.php" class="nav-item nav-parent <?= ($current_page == 'manage_products.php' || $current_page == 'view-products.php' || $current_page == 'add_product.php' || $current_page == 'verify_product_subcategories.php') ? 'active' : '' ?>">
                <i class="fas fa-box"></i>
                <span>Manage Products</span>
                <i class="fas fa-chevron-down nav-arrow"></i>
            </a>
            <div class="nav-submenu <?= ($current_page == 'view-products.php' || $current_page == 'add_product.php' || $current_page == 'verify_product_subcategories.php') ? 'show' : '' ?>">
                <a href="view-products.php" class="nav-item sub-item <?= $current_page == 'view-products.php' ? 'active' : '' ?>">
                    <i class="fas fa-list"></i>
                    <span>View Products</span>
                </a>
                <a href="add_product.php" class="nav-item sub-item <?= $current_page == 'add_product.php' ? 'active' : '' ?>">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Product</span>
                </a>
                <a href="verify_product_subcategories.php" class="nav-item sub-item <?= $current_page == 'verify_product_subcategories.php' ? 'active' : '' ?>">
                    <i class="fas fa-check-double"></i>
                    <span>Verify Subcategories</span>
                </a>
            </div>
        </div>
        
        <!-- Manage Categories with Dropdown -->
        <div class="nav-group">
            <a href="manage_categories.php" class="nav-item nav-parent <?= ($current_page == 'manage_categories.php' || $current_page == 'view_categories.php' || $current_page == 'view_subcategories.php' || $current_page == 'add_category.php' || $current_page == 'add_subcategory.php') ? 'active' : '' ?>">
                <i class="fas fa-tags"></i>
                <span>Manage Categories</span>
                <i class="fas fa-chevron-down nav-arrow"></i>
            </a>
            <div class="nav-submenu <?= ($current_page == 'view_categories.php' || $current_page == 'view_subcategories.php' || $current_page == 'add_category.php' || $current_page == 'add_subcategory.php') ? 'show' : '' ?>">
                <a href="view_categories.php" class="nav-item sub-item <?= $current_page == 'view_categories.php' ? 'active' : '' ?>">
                    <i class="fas fa-list"></i>
                    <span>View Categories</span>
                </a>
                <a href="view_subcategories.php" class="nav-item sub-item <?= $current_page == 'view_subcategories.php' ? 'active' : '' ?>">
                    <i class="fas fa-list"></i>
                    <span>View Subcategories</span>
                </a>
                <a href="add_category.php" class="nav-item sub-item <?= $current_page == 'add_category.php' ? 'active' : '' ?>">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Category</span>
                </a>
                <a href="add_subcategory.php" class="nav-item sub-item <?= $current_page == 'add_subcategory.php' ? 'active' : '' ?>">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Subcategory</span>
                </a>
            </div>
        </div>
        
        <a href="subscribers.php" class="nav-item <?= $current_page == 'subscribers.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i>
            <span>Subscribers</span>
        </a>
        <a href="manage_orders.php" class="nav-item <?= $current_page == 'manage_orders.php' ? 'active' : '' ?>">
            <i class="fas fa-shopping-cart"></i>
            <span>Orders</span>
        </a>
        <a href="manage_returns.php" class="nav-item <?= $current_page == 'manage_returns.php' ? 'active' : '' ?>">
            <i class="fas fa-undo"></i>
            <span>Returns/Refunds</span>
        </a>
        <a href="customers.php" class="nav-item <?= $current_page == 'customers.php' ? 'active' : '' ?>">
            <i class="fas fa-user-friends"></i>
            <span>Customers</span>
        </a>
        <a href="reviews.php" class="nav-item <?= $current_page == 'reviews.php' ? 'active' : '' ?>">
            <i class="fas fa-star"></i>
            <span>Reviews</span>
        </a>
        <a href="manage_contacts.php" class="nav-item <?= $current_page == 'manage_contacts.php' ? 'active' : '' ?>">
            <i class="fas fa-envelope-open-text"></i>
            <span>Contact Messages</span>
        </a>
        <a href="manage_callbacks.php" class="nav-item <?= $current_page == 'manage_callbacks.php' ? 'active' : '' ?>">
            <i class="fas fa-phone-alt"></i>
            <span>Callback Requests</span>
        </a>
        
        <!-- Manage Blogs with Dropdown -->
        <div class="nav-group">
            <a href="manage_blogs.php" class="nav-item nav-parent <?= ($current_page == 'manage_blogs.php' || $current_page == 'view_blogs.php' || $current_page == 'add_blog.php') ? 'active' : '' ?>">
                <i class="fas fa-blog"></i>
                <span>Manage Blogs</span>
                <i class="fas fa-chevron-down nav-arrow"></i>
            </a>
            <div class="nav-submenu <?= ($current_page == 'view_blogs.php' || $current_page == 'add_blog.php') ? 'show' : '' ?>">
                <a href="view_blogs.php" class="nav-item sub-item <?= $current_page == 'view_blogs.php' ? 'active' : '' ?>">
                    <i class="fas fa-list"></i>
                    <span>View Blogs</span>
                </a>
                <a href="add_blog.php" class="nav-item sub-item <?= $current_page == 'add_blog.php' ? 'active' : '' ?>">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Blog Post</span>
                </a>
            </div>
        </div>
        
        <!-- Manage Tutorials with Dropdown -->
        <div class="nav-group">
            <a href="manage_tutorials.php" class="nav-item nav-parent <?= ($current_page == 'manage_tutorials.php' || $current_page == 'view_tutorials.php' || $current_page == 'add_tutorial.php') ? 'active' : '' ?>">
                <i class="fas fa-video"></i>
                <span>Manage Tutorials</span>
                <i class="fas fa-chevron-down nav-arrow"></i>
            </a>
            <div class="nav-submenu <?= ($current_page == 'view_tutorials.php' || $current_page == 'add_tutorial.php') ? 'show' : '' ?>">
                <a href="view_tutorials.php" class="nav-item sub-item <?= $current_page == 'view_tutorials.php' ? 'active' : '' ?>">
                    <i class="fas fa-list"></i>
                    <span>View Tutorials</span>
                </a>
                <a href="add_tutorial.php" class="nav-item sub-item <?= $current_page == 'add_tutorial.php' ? 'active' : '' ?>">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Tutorial</span>
                </a>
            </div>
        </div>
        
        <!-- Royale Reels -->
        <a href="manage_reels.php" class="nav-item <?= $current_page == 'manage_reels.php' ? 'active' : '' ?>">
            <i class="fas fa-play-circle"></i>
            <span>Royale Reels</span>
        </a>
        
        <!-- Gift Cards with Dropdown -->
        <div class="nav-group">
            <a href="manage_gift_cards.php" class="nav-item nav-parent <?= ($current_page == 'manage_gift_cards.php' || $current_page == 'add_gift_card.php' || $current_page == 'view_gift_card.php' || $current_page == 'edit_gift_card.php' || $current_page == 'delete_gift_card.php' || $current_page == 'manage_gift_card_designs.php' || $current_page == 'add_gift_card_design.php' || $current_page == 'edit_gift_card_design.php' || $current_page == 'delete_gift_card_design.php') ? 'active' : '' ?>">
                <i class="fas fa-gift"></i>
                <span>Gift Cards</span>
                <i class="fas fa-chevron-down nav-arrow"></i>
            </a>
            <div class="nav-submenu <?= ($current_page == 'add_gift_card.php' || $current_page == 'view_gift_card.php' || $current_page == 'edit_gift_card.php' || $current_page == 'delete_gift_card.php' || $current_page == 'manage_gift_card_designs.php' || $current_page == 'add_gift_card_design.php' || $current_page == 'edit_gift_card_design.php' || $current_page == 'delete_gift_card_design.php') ? 'show' : '' ?>">
                <a href="manage_gift_cards.php" class="nav-item sub-item <?= $current_page == 'manage_gift_cards.php' ? 'active' : '' ?>">
                    <i class="fas fa-list"></i>
                    <span>Manage Gift Cards</span>
                </a>
                <a href="manage_gift_card_designs.php" class="nav-item sub-item <?= ($current_page == 'manage_gift_card_designs.php' || $current_page == 'add_gift_card_design.php' || $current_page == 'edit_gift_card_design.php' || $current_page == 'delete_gift_card_design.php') ? 'active' : '' ?>">
                    <i class="fas fa-palette"></i>
                    <span>Manage Designs</span>
                </a>
            </div>
        </div>
        
        <a href="manage_discount_codes.php" class="nav-item <?= $current_page == 'manage_discount_codes.php' ? 'active' : '' ?>">
            <i class="fas fa-ticket-alt"></i>
            <span>Discount Codes</span>
        </a>
        
        <a href="profile.php" class="nav-item <?= $current_page == 'profile.php' ? 'active' : '' ?>">
            <i class="fas fa-user-circle"></i>
            <span>Profile</span>
        </a>
        <a href="logout.php" class="nav-item logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>
</aside>

<script>
// Sidebar Toggle & Global Header Sync
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.admin-sidebar');
    const adminEmail = '<?= $_SESSION['admin'] ?>';
    
    // 1. Create the Toggle Switch HTML
    const toggleContainer = document.createElement('div');
    toggleContainer.className = 'sidebar-control';
    toggleContainer.innerHTML = `
        <span class="control-label">Sidebar</span>
        <label class="switch">
            <input type="checkbox" id="sidebarTrigger" checked>
            <span class="slider"></span>
        </label>
    `;

    // 2. Locate or Create Header Right
    let headerRight = document.querySelector('.header-right');
    const adminHeader = document.querySelector('.admin-header');
    
    if (!headerRight && adminHeader) {
        headerRight = document.createElement('div');
        headerRight.className = 'header-right';
        adminHeader.appendChild(headerRight);
    }

    if (headerRight) {
        // A. Ensure Admin Profile exists (Inject if missing)
        if (!headerRight.querySelector('.admin-profile')) {
            const profileContainer = document.createElement('div');
            profileContainer.className = 'admin-profile';
            profileContainer.innerHTML = `
                <i class="fas fa-user-circle"></i>
                <span>${adminEmail}</span>
            `;
            headerRight.appendChild(profileContainer);
        }

        // B. Inject Toggle Switch
        headerRight.appendChild(toggleContainer);
    }

    // 3. Logic for Toggle
    const trigger = document.getElementById('sidebarTrigger');
    if (trigger) {
        if (sidebar.classList.contains('collapsed')) {
            trigger.checked = false;
        }

        trigger.addEventListener('change', function() {
            if (this.checked) {
                sidebar.classList.remove('collapsed');
            } else {
                sidebar.classList.add('collapsed');
            }
        });
    }

    // --- Sidebar Dropdown Functionality ---
    const navParents = document.querySelectorAll('.nav-parent');
    
    navParents.forEach(parent => {
        parent.addEventListener('click', function(e) {
            if (e.target.closest('.nav-item.sub-item')) return;
            
            const isArrowClick = e.target.classList.contains('nav-arrow') || e.target.closest('.nav-arrow');
            const isParentClick = e.target === this || e.target.closest('.nav-parent') === this;
            
            if (isParentClick || isArrowClick) {
                e.preventDefault();
                const navGroup = this.closest('.nav-group');
                const submenu = navGroup.querySelector('.nav-submenu');
                
                if (submenu.classList.contains('show')) {
                    submenu.classList.remove('show');
                    navGroup.classList.remove('active');
                } else {
                    document.querySelectorAll('.nav-submenu.show').forEach(openSubmenu => {
                        openSubmenu.classList.remove('show');
                        openSubmenu.closest('.nav-group').classList.remove('active');
                    });
                    submenu.classList.add('show');
                    navGroup.classList.add('active');
                }
            }
        });
    });
    
    // Auto-expand active groups
    const activeSubItem = document.querySelector('.nav-item.sub-item.active');
    if (activeSubItem) {
        const parentGroup = activeSubItem.closest('.nav-group');
        if (parentGroup) {
            parentGroup.querySelector('.nav-submenu').classList.add('show');
            parentGroup.classList.add('active');
        }
    }
});
</script>

