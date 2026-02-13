(function() {
    /**
     * Leave Management Module - JavaScript
     * Handles tab navigation, dropdown, and interactions
     */

    document.addEventListener('DOMContentLoaded', function () {

        // Tab navigation smooth transitions
        const navLinks = document.querySelectorAll('.leave-nav-tabs .nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                // Add loading indicator
                const icon = this.querySelector('i');
                const originalClass = icon.className;
                icon.className = 'fa fa-spinner fa-spin';

                // Restore icon after page loads (will be replaced by new page)
                setTimeout(() => {
                    icon.className = originalClass;
                }, 500);
            });
        });

        // Gear icon rotation on hover
        const gearIcon = document.querySelector('.config-quick-access .btn-icon i');
        const gearBtn = document.querySelector('.config-quick-access .btn-icon');

        if (gearBtn) {
            gearBtn.addEventListener('mouseenter', function () {
                if (gearIcon) {
                    gearIcon.style.animation = 'spin 0.5s linear';
                }
            });

            gearBtn.addEventListener('mouseleave', function () {
                if (gearIcon) {
                    gearIcon.style.animation = '';
                }
            });
        }

        // Dropdown close on click outside
        document.addEventListener('click', function (event) {
            const dropdown = document.querySelector('.config-quick-access .dropdown-menu');
            const button = document.querySelector('.config-quick-access .btn-icon');

            if (dropdown && button) {
                if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                    if (typeof bootstrap !== 'undefined') {
                        const bsDropdown = bootstrap.Dropdown.getInstance(button);
                        if (bsDropdown) {
                            bsDropdown.hide();
                        }
                    }
                }
            }
        });

        // Add keyboard navigation for tabs
        navLinks.forEach((link, index) => {
            link.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    const nextLink = navLinks[index + 1] || navLinks[0];
                    nextLink.focus();
                } else if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    const prevLink = navLinks[index - 1] || navLinks[navLinks.length - 1];
                    prevLink.focus();
                }
            });
        });
    });

    // Add CSS animation for gear icon
    if (!document.getElementById('leave-management-spin-style')) {
        const style = document.createElement('style');
        style.id = 'leave-management-spin-style';
        style.textContent = `
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(90deg); }
            }
        `;
        document.head.appendChild(style);
    }

    // Helper function for showing loading states
    function showLoadingState(element) {
        element.classList.add('loading');
        element.style.opacity = '0.6';
        element.style.pointerEvents = 'none';
    }

    function hideLoadingState(element) {
        element.classList.remove('loading');
        element.style.opacity = '1';
        element.style.pointerEvents = 'auto';
    }

    // Export for use in other modules
    window.LeaveManagement = window.LeaveManagement || {
        showLoadingState,
        hideLoadingState
    };
})();
