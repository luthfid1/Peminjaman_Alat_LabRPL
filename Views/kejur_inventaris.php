<?php
$activePage = 'inventaris';
$pageTitle = 'Daftar Inventaris Alat Lab (Read-Only) - Ketua Jurusan';
require_once __DIR__ . '/kejur_header.php';
?>

<div class="print-kop">
    <h2 style="margin: 0; font-size: 16px; text-transform: uppercase;">DAFTAR INVENTARIS ALAT LABORATORIUM KOMPUTER (RPL)</h2>
    <p style="margin: 4px 0 0 0; font-size: 12px;">SMK NEGERI - JURUSAN REKAYASA PERANGKAT LUNAK (HAK AKSES KETUA JURUSAN: READ-ONLY)</p>
    <span style="font-size: 10px; color: #555;">Dicetak pada: <?= date('d F Y, H:i') ?> WIB | Oleh: <?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Ketua Jurusan') ?></span>
</div>

<div class="admin-header-row no-print">
    <div>
        <span class="admin-date-label">INVENTARIS LABORATORIUM (READ-ONLY)</span>
        <h1 class="admin-title">Daftar Inventaris Alat Lab</h1>
        <p class="admin-subtitle">Monitoring data seluruh perangkat dan spesifikasi alat milik jurusan RPL. Hak akses eksekusi CRUD adalah hak eksklusif milik Admin Pengelola Lab.</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <span style="font-size: 12px; background: #e0f2fe; color: #0369a1; padding: 6px 12px; border-radius: 6px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
            🔒 Akses Monitoring (Tanpa Tombol Tambah/Edit/Hapus)
        </span>
        <button type="button" class="btn-add-instrument" onclick="window.print()">
            🖨 Cetak Inventaris
        </button>
    </div>
</div>

<!-- INVENTORY CARD & TABLE -->
<section class="inventory-section-card">
    <div class="inventory-header no-print">
        <div>
            <h3 class="inventory-title">Katalog Aset & Perangkat RPL</h3>
            <p class="inventory-sub">Total <?= count($daftarAlat) ?> item alat terdaftar di database.</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <form method="GET" action="index.php" style="margin: 0;">
                <input type="hidden" name="c" value="kejur">
                <input type="hidden" name="a" value="inventaris">
                <select name="kategori" class="form-control" style="font-size: 13px; height: 38px; padding: 6px 12px;" onchange="this.form.submit()">
                    <option value="">-- Semua Kategori --</option>
                    <?php foreach ($daftarKategori as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= (isset($_GET['kategori']) && (int)$_GET['kategori'] === (int)$k['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($_GET['search'])): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($_GET['search']) ?>">
                <?php endif; ?>
            </form>

            <form method="GET" action="index.php" class="search-box" style="margin: 0;">
                <input type="hidden" name="c" value="kejur">
                <input type="hidden" name="a" value="inventaris">
                <?php if (!empty($_GET['kategori'])): ?>
                    <input type="hidden" name="kategori" value="<?= (int)$_GET['kategori'] ?>">
                <?php endif; ?>
                <span class="search-icon">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
                <input type="text" name="search" placeholder="Cari nama, kode..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </form>
        </div>
    </div>

    <table class="inventory-table">
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th>Kode Alat</th>
                <th>Nama Perangkat Laboratorium</th>
                <th>Kategori</th>
                <th>Sisa Stok Fisik</th>
                <th>Kondisi Fisik</th>
                <th>Status Ketersediaan</th>
                <th>Spesifikasi / Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($daftarAlat)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                        Tidak ada data inventaris alat yang ditemukan.
                    </td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($daftarAlat as $alat): 
                    $isAvailable = ($alat['jumlah'] > 0);
                    $kondisi = $alat['kondisi'] ?? 'Baik';
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <span style="font-family: monospace; font-size: 11px; font-weight: 700; background: #e2e8f0; color: #334155; padding: 2px 6px; border-radius: 4px;">
                                <?= htmlspecialchars($alat['kode'] ?? '-') ?>
                            </span>
                        </td>
                        <td class="col-instrument-name">
                            <strong style="color: var(--primary-navy);"><?= htmlspecialchars($alat['nama_alat']) ?></strong>
                        </td>
                        <td>
                            <span style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($alat['nama_kategori'] ?? 'Umum') ?></span>
                        </td>
                        <td>
                            <strong style="color: var(--teal-dark); font-size: 13px;"><?= (int)$alat['jumlah'] ?></strong> unit
                        </td>
                        <td>
                            <?php if ($kondisi === 'Baik'): ?>
                                <span class="stock-badge" style="background: #dcfce7; color: #15803d; font-weight: 700; padding: 2px 8px; font-size: 11px;">
                                    ✓ Baik
                                </span>
                            <?php elseif ($kondisi === 'Rusak Ringan'): ?>
                                <span class="stock-badge" style="background: #fef3c7; color: #b45309; font-weight: 700; padding: 2px 8px; font-size: 11px;">
                                    ⚠ Rusak Ringan
                                </span>
                            <?php else: ?>
                                <span class="stock-badge" style="background: #fee2e2; color: #b91c1c; font-weight: 700; padding: 2px 8px; font-size: 11px;">
                                    ✕ Rusak Berat
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($isAvailable): ?>
                                <span class="stock-badge stock-badge-available" style="padding: 2px 8px; font-size: 11px;">Tersedia</span>
                            <?php else: ?>
                                <span class="stock-badge stock-badge-low" style="padding: 2px 8px; font-size: 11px;">Kosong / Dipinjam</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size: 12px; color: #475569; max-width: 260px; line-height: 1.4;">
                            <?= htmlspecialchars($alat['deskripsi'] ?? 'Tidak ada keterangan spesifikasi.') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php require_once __DIR__ . '/kejur_footer.php'; ?>
