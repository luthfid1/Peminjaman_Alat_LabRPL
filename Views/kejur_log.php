<?php
$activePage = 'log';
$pageTitle = 'Audit Trail & Log Aktivitas Global - Ketua Jurusan';
require_once __DIR__ . '/kejur_header.php';
?>

<div class="print-kop">
    <h2 style="margin: 0; font-size: 16px; text-transform: uppercase;">AUDIT TRAIL & LOG AKTIVITAS GLOBAL LABORATORIUM KOMPUTER</h2>
    <p style="margin: 4px 0 0 0; font-size: 12px;">SMK NEGERI - JURUSAN REKAYASA PERANGKAT LUNAK (SOAL UKK POIN 2b & 3)</p>
    <span style="font-size: 10px; color: #555;">Dicetak pada: <?= date('d F Y, H:i') ?> WIB | Oleh: <?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Ketua Jurusan') ?></span>
</div>

<div class="admin-header-row no-print">
    <div>
        <span class="admin-date-label">PENGAWASAN & KEAMANAN SISTEM (POIN 2b & 3)</span>
        <h1 class="admin-title">Audit Trail & Log Aktivitas Global</h1>
        <p class="admin-subtitle">Catatan kronologis setiap aksi di dalam aplikasi untuk memantau perubahan data, approval petugas, dan mengunci keamanan dari manipulasi data.</p>
    </div>
    <button type="button" class="btn-add-instrument" onclick="window.print()">
        🖨 Cetak Catatan Audit
    </button>
</div>

<section class="inventory-section-card">
    <div class="inventory-header no-print">
        <div>
            <h3 class="inventory-title">Rekam Kronologis Aktivitas Sistem</h3>
            <p class="inventory-sub">Total <?= count($daftarLog) ?> rekaman jejak audit tersimpan di database.</p>
        </div>
        <form method="GET" action="index.php" class="search-box">
            <input type="hidden" name="c" value="kejur">
            <input type="hidden" name="a" value="log">
            <span class="search-icon">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </span>
            <input type="text" name="search" placeholder="Cari aktivitas, user, role..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </form>
    </div>

    <table class="inventory-table">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Waktu / Timestamp</th>
                <th>Nama Pengguna</th>
                <th>Hak Akses (Role)</th>
                <th>Aksi / Aktivitas</th>
                <th>Detail & Rekam Jejak</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($daftarLog)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                        Belum ada aktivitas yang tercatat dalam log sistem.
                    </td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($daftarLog as $log): 
                    $roleClass = 'badge-role-pengelola';
                    $role = strtolower($log['role'] ?? '');
                    if ($role === 'admin' || $role === 'kejur') $roleClass = 'badge-role-admin';
                    if ($role === 'petugas') $roleClass = 'badge-role-petugas';
                    if ($role === 'peminjam') $roleClass = 'badge-role-peminjam';
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td style="white-space: nowrap; font-size: 12px; color: var(--text-muted);">
                            <strong><?= date('d M Y', strtotime($log['waktu'])) ?></strong>
                            <div style="color: var(--teal-dark); font-weight: 600;"><?= date('H:i:s', strtotime($log['waktu'])) ?> WIB</div>
                        </td>
                        <td class="col-instrument-name">
                            <strong style="color: var(--navy-primary);"><?= htmlspecialchars($log['nama_user'] ?? 'Sistem') ?></strong>
                            <div style="font-size: 11px; color: var(--text-muted);">@<?= htmlspecialchars($log['username'] ?? '-') ?></div>
                        </td>
                        <td>
                            <span class="badge-role <?= $roleClass ?>">
                                <?= ucfirst(htmlspecialchars($log['role'] ?? 'User')) ?>
                            </span>
                        </td>
                        <td>
                            <strong style="color: var(--navy-primary); font-size: 13px;"><?= htmlspecialchars($log['aktivitas']) ?></strong>
                        </td>
                        <td style="font-size: 12px; color: #334155; line-height: 1.5;">
                            <?= htmlspecialchars($log['deskripsi'] ?? '-') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php require_once __DIR__ . '/kejur_footer.php'; ?>
