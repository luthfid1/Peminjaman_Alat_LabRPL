document.addEventListener('DOMContentLoaded', function () {
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

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeSidebar();
            closeLandingMenu();
        }
    });

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

    window.addEventListener('resize', function () {
        if (window.innerWidth > 992) {
            closeSidebar();
        }
    });

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

        landingCollapse.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                closeLandingMenu();
            });
        });

        document.addEventListener('click', function (e) {
            if (!landingCollapse.contains(e.target) && !landingToggle.contains(e.target)) {
                closeLandingMenu();
            }
        });
    }

    const tables = document.querySelectorAll('table.inventory-table');
    tables.forEach(function (table) {
        if (!table.parentElement.classList.contains('table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);

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
