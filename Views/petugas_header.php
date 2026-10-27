<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Petugas Laboran - Lab Komputer') ?></title>
    <link rel="stylesheet" href="Assets/css/style.css">
    <style>
        @media print {
            .navbar, .sidebar, .btn-add-instrument, .search-box, .table-actions-cell, .no-print, button {
                display: none !important;
            }
            .app-container {
                display: block !important;
                padding: 0 !important;
            }
            .main-content {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }
            body {
                background: white !important;
                color: black !important;
            }
            .inventory-section-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>

    <header class="navbar no-print">
        <div class="admin-navbar-brand-group">
            <button class="mobile-sidebar-toggle" id="mobileSidebarToggle" aria-label="Buka Menu" type="button">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <a href="index.php?c=petugas&a=dashboard" class="nav-brand">
                <div class="brand-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                </div>
                <span class="brand-name">LAB KOMPUTER</span>
            </a>
            <span class="admin-nav-separator">|</span>
            <span class="admin-nav-subtext">Petugas Laboran</span>
        </div>

        <div class="nav-user">
            <div class="user-profile">
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Petugas') ?></span>
                    <span class="user-role">Petugas Lab</span>
                </div>
                <div class="user-avatar" style="background: var(--teal-primary);">
                    <?php
                        $nama = $_SESSION['user']['nama'] ?? 'Petugas';
                        $parts = explode(' ', trim($nama));
                        $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                        echo htmlspecialchars($initials);
                    ?>
                </div>
            </div>
        </div>
    </header>

    <div class="app-container">

        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <aside class="sidebar no-print" id="appSidebar">
            <div class="sidebar-mobile-header">
                <div class="brand-group" style="gap: 8px;">
                    <div class="brand-icon" style="width: 28px; height: 28px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </div>
                    <span class="brand-name" style="font-size: 13px;">MENU LAB</span>
                </div>
                <button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Tutup Menu" type="button">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="sidebar-mode-card" style="background: linear-gradient(135deg, rgba(20, 184, 166, 0.12), rgba(15, 118, 110, 0.05)); border: 1px solid rgba(20, 184, 166, 0.2);">
                <span class="mode-tag" style="background: var(--teal-primary);">PETUGAS</span>
                <h2 class="mode-title" style="color: var(--teal-dark);">Laboran Lab</h2>
                <p class="mode-desc">Verifikasi pengajuan peminjaman, catat pengembalian alat, & pantau stok lab.</p>
            </div>

            <ul class="admin-sidebar-menu">
                <li>
                    <a href="index.php?c=petugas&a=dashboard" class="<?= ($activePage === 'dashboard') ? 'active' : '' ?>">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="index.php?c=petugas&a=peminjaman" class="<?= ($activePage === 'peminjaman') ? 'active' : '' ?>">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        Menyetujui Peminjaman
                    </a>
                </li>
                <li>
                    <a href="index.php?c=petugas&a=pengembalian" class="<?= ($activePage === 'pengembalian') ? 'active' : '' ?>" style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="display: flex; align-items: center; gap: 8px;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="1 4 1 10 7 10"></polyline>
                                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                            </svg>
                            Memantau Pengembalian
                        </span>
                        <?php if (!empty($totalMenungguKembali) && $totalMenungguKembali > 0): ?>
                            <span style="background: #ef4444; color: white; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 999px; line-height: 1;">
                                <?= $totalMenungguKembali ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="index.php?c=petugas&a=laporan" class="<?= ($activePage === 'laporan') ? 'active' : '' ?>">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                        Mencetak Laporan
                    </a>
                </li>
                <li>
                    <a href="index.php?c=auth&a=logout">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Keluar
                    </a>
                </li>
            </ul>
        </aside>

        <main class="main-content">
