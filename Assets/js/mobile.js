/**
 * Peminjaman Alat Lab Komputer - Mobile UI/UX Enhancements
 * Provides mobile navigation, touch optimizations, table responsiveness, and drawer interactions.
 */
document.addEventListener('DOMContentLoaded', function () {
    // 1. Mobile Sidebar Drawer Logic
    const toggleBtn = document.getElementById('mobileSidebarToggle');
    const sidebar = document.getElementById('appSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const closeBtn = document.getElementById('sidebarCloseBtn');

    function openSidebar() {
        if (sidebar) sidebar.classList.add('active');
        if (backdrop) backdrop.classList.add('active');
        document.body.classList.add('sidebar-open');
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('active');
        if (backdrop) backdrop.classList.remove('active');
        document.body.classList.remove('sidebar-open');
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (sidebar && sidebar.classList.contains('active')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            closeSidebar();
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', function () {
            closeSidebar();
        });
    }

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeSidebar();
            closeLandingMenu();
        }
    });

    // Close when navigating in mobile drawer
    if (sidebar) {
        const menuLinks = sidebar.querySelectorAll('.admin-sidebar-menu a');
        menuLinks.forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 992) {
                    closeSidebar();
                }
            });
        });
    }

    // Auto close drawer when resizing past 992px
    window.addEventListener('resize', function () {
        if (window.innerWidth > 992) {
            closeSidebar();
        }
    });

    // 2. Landing Page Mobile Nav Logic
    const landingToggle = document.getElementById('landingMenuToggle');
    const landingCollapse = document.getElementById('landingNavCollapse');

    function closeLandingMenu() {
        if (landingCollapse) landingCollapse.classList.remove('active');
        if (landingToggle) landingToggle.classList.remove('active');
    }

    if (landingToggle && landingCollapse) {
        landingToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = landingCollapse.classList.toggle('active');
            landingToggle.classList.toggle('active', isOpen);
        });

        // Close on clicking links
        landingCollapse.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                closeLandingMenu();
            });
        });

        // Close on clicking outside
        document.addEventListener('click', function (e) {
            if (!landingCollapse.contains(e.target) && !landingToggle.contains(e.target)) {
                closeLandingMenu();
            }
        });
    }

    // 3. Responsive Table Wrapping & Horizontal Scroll Hint
    const tables = document.querySelectorAll('table.inventory-table');
    tables.forEach(function (table) {
        if (!table.parentElement.classList.contains('table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);

            // Add scroll hint if on small screen
            if (window.innerWidth <= 768) {
                const hint = document.createElement('div');
                hint.className = 'table-mobile-hint';
                hint.innerHTML = `
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    <span>Geser tabel untuk melihat seluruh data</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                `;
                wrapper.parentNode.insertBefore(hint, wrapper);
            }
        }
    });

    // 4. Modal Touch Friendly UX
    // Close modal when clicking outside of modal-content
    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                overlay.style.display = 'none';
                overlay.classList.remove('active');
                document.body.classList.remove('modal-open');
            }
        });
    });
});
