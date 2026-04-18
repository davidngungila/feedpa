/**
 * ClickPesa Menu Navigation
 * Handles dropdown menus and sidebar functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Handle dropdown menu toggles
    const menuToggles = document.querySelectorAll('.menu-toggle');
    
    menuToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            const menuItem = this.closest('.menu-item');
            const submenu = menuItem.querySelector('.menu-sub');
            
            if (submenu) {
                // Close other open submenus
                const allMenuItems = document.querySelectorAll('.menu-item');
                allMenuItems.forEach(function(item) {
                    if (item !== menuItem && item.classList.contains('open')) {
                        item.classList.remove('open');
                    }
                });
                
                // Toggle current submenu
                menuItem.classList.toggle('open');
            }
        });
    });
    
    // Handle mobile menu toggle
    const mobileMenuToggle = document.querySelector('.layout-menu-toggle');
    const layoutMenu = document.querySelector('.layout-menu');
    const layoutOverlay = document.querySelector('.layout-overlay');
    
    if (mobileMenuToggle && layoutMenu) {
        mobileMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            layoutMenu.classList.toggle('show');
            
            // Toggle overlay for mobile
            if (layoutOverlay) {
                layoutOverlay.classList.toggle('show');
            }
        });
    }
    
    // Close menu when clicking overlay
    if (layoutOverlay) {
        layoutOverlay.addEventListener('click', function() {
            layoutMenu.classList.remove('show');
            layoutOverlay.classList.remove('show');
        });
    }
    
    // Set active menu items based on current route
    setActiveMenuItems();
    
    // Handle window resize
    window.addEventListener('resize', handleResize);
    handleResize();
});

/**
 * Set active menu items based on current URL
 */
function setActiveMenuItems() {
    const currentPath = window.location.pathname;
    const menuItems = document.querySelectorAll('.menu-item');
    
    menuItems.forEach(function(item) {
        const link = item.querySelector('.menu-link');
        if (!link) return;
        
        const href = link.getAttribute('href');
        
        // Remove active class from all items
        item.classList.remove('active');
        
        // Check if current path matches menu item
        if (href && href !== 'javascript:void(0);') {
            if (currentPath === href || currentPath.startsWith(href + '/')) {
                item.classList.add('active');
                
                // Open parent submenu if this is a submenu item
                const parentSubmenu = item.closest('.menu-sub');
                if (parentSubmenu) {
                    const parentMenuItem = parentSubmenu.closest('.menu-item');
                    if (parentMenuItem) {
                        parentMenuItem.classList.add('open');
                    }
                }
            }
        }
    });
}

/**
 * Handle window resize for responsive menu
 */
function handleResize() {
    const layoutMenu = document.querySelector('.layout-menu');
    const layoutOverlay = document.querySelector('.layout-overlay');
    const isMobile = window.innerWidth <= 768;
    
    if (layoutMenu) {
        // Reset menu state on resize
        if (!isMobile) {
            layoutMenu.classList.remove('show');
            if (layoutOverlay) {
                layoutOverlay.classList.remove('show');
            }
        }
    }
}

/**
 * Close all submenus
 */
function closeAllSubmenus() {
    const menuItems = document.querySelectorAll('.menu-item.open');
    menuItems.forEach(function(item) {
        item.classList.remove('open');
    });
}

/**
 * Open specific menu item
 */
function openMenuItem(menuId) {
    const menuItem = document.querySelector(`[data-menu-id="${menuId}"]`);
    if (menuItem) {
        // Close other menus
        closeAllSubmenus();
        
        // Open this menu
        menuItem.classList.add('open');
        
        // Open parent if it's a submenu item
        const parentSubmenu = menuItem.closest('.menu-sub');
        if (parentSubmenu) {
            const parentMenuItem = parentSubmenu.closest('.menu-item');
            if (parentMenuItem) {
                parentMenuItem.classList.add('open');
            }
        }
    }
}

/**
 * Initialize menu tooltips
 */
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    
    tooltipElements.forEach(function(element) {
        // Simple tooltip implementation
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'custom-tooltip';
            tooltip.textContent = this.getAttribute('title') || this.getAttribute('data-bs-original-title');
            tooltip.style.cssText = `
                position: absolute;
                background: #333;
                color: white;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 12px;
                z-index: 9999;
                white-space: nowrap;
            `;
            
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
        });
        
        element.addEventListener('mouseleave', function() {
            const tooltip = document.querySelector('.custom-tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
}

// Export functions for global access
window.ClickPesaMenu = {
    openMenuItem: openMenuItem,
    closeAllSubmenus: closeAllSubmenus,
    setActiveMenuItems: setActiveMenuItems
};
