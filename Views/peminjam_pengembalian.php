<?php
$activePage = 'pengembalian';
$pageTitle = 'Pengembalian Saya - Lab Komputer';
require_once __DIR__ . '/peminjam_header.php';
?>

<div class="admin-header-row">
    <div>
        <span class="admin-date-label">RIWAYAT & ARSIP PENGEMBALIAN</span>
        <h1 class="admin-title">Pengembalian Saya</h1>
        <p class="admin-subtitle">Daftar perangkat laboratorium komputer yang telah dikembalikan, status kondisi fisik alat, serta informasi denda (jika ada).</p>
    </div>
</div>

<!-- ALERTS -->
<?php if (!empty($message)): ?>
    <div class="auth-alert auth-alert-success" style="margin-bottom: 20px;">
        <span><?= htmlspecialchars($message) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="auth-alert auth-alert-error" style="margin-bottom: 20px;">
        <span><?= htmlspecialchars($error) ?></span>
    </div>
<?php endif; ?>

<!-- PERANGKAT YANG SEDANG DIPINJAM (MENUNGGU DIKEMBALIKAN) -->
<?php if (!empty($daftarPeminjamanAktif)): ?>
    <section class="inventory-section-card" style="border: 2px solid #14b8a6; margin-bottom: 24px; background: #f0fdfa;">
        <div class="inventory-header">
            <div>
                <h3 class="inventory-title" style="color: #0f766e;">⚡ Alat Sedang Anda Pinjam & Proses Pengembalian</h3>
                <p class="inventory-sub" style="color: #115e59;">Terdapat <?= count($daftarPeminjamanAktif) ?> perangkat yang sedang Anda pinjam / dalam proses pengembalian.</p>
            </div>
        </div>
        <table class="inventory-table">
            <thead>
                <tr style="background: rgba(20, 184, 166, 0.1);">
                    <th>No</th>
                    <th>Kode & Alat Lab</th>
                    <th>Jumlah</th>
                    <th>Waktu Pinjam</th>
                    <th>Batas Rencana Kembali</th>
                    <th>Status Validasi</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $noAktif = 1; foreach ($daftarPeminjamanAktif as $pa): 
                    $stAktif = strtolower($pa['status']);
                ?>
                    <tr>
                        <td><?= $noAktif++ ?></td>
                        <td>
                            <div style="font-family: monospace; font-size: 11px; font-weight: 700; color: var(--primary-teal);"><?= htmlspecialchars($pa['kode_peminjaman'] ?? '-') ?></div>
                            <strong style="color: var(--primary-navy);"><?= htmlspecialchars($pa['nama_alat'] ?? 'Alat Lab') ?></strong>
                            <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($pa['kode_alat'] ?? '') ?></div>
                        </td>
                        <td><strong><?= (int)($pa['jumlah'] ?? 1) ?></strong> unit</td>
                        <td><?= !empty($pa['waktu_pinjam']) ? date('H:i', strtotime($pa['waktu_pinjam'])) . ' WIB' : '-' ?></td>
                        <td>
                            <strong style="color: #0f766e;"><?= !empty($pa['waktu_rencana_kembali']) ? date('H:i', strtotime($pa['waktu_rencana_kembali'])) . ' WIB' : '-' ?></strong>
                        </td>
                        <td>
                            <?php if ($stAktif === 'menunggu_pengembalian' || $stAktif === 'menunggu_validasi'): ?>
                                <span class="stock-badge" style="background:#fef3c7; color:#b45309; border:1px solid #fde68a; font-weight:700; padding: 4px 10px; font-size: 12px;">
                                    ⏳ Menunggu Validasi Petugas
                                </span>
                                <div style="font-size: 11px; color: #92400e; margin-top: 3px;">Fisik diserahkan di meja lab</div>
                            <?php else: ?>
                                <span class="stock-badge stock-badge-available" style="padding: 4px 10px; font-size: 12px;">
                                    💻 Sedang Dipinjam
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="table-actions-cell">
                            <?php if ($stAktif === 'menunggu_pengembalian' || $stAktif === 'menunggu_validasi'): ?>
                                <span style="font-size: 12px; color: #b45309; font-weight: 700; background: #fffbeb; padding: 6px 12px; border-radius: 6px; border: 1px dashed #f59e0b; display: inline-block;">
                                    🔍 Menunggu QC Petugas
                                </span>
                            <?php else: ?>
                                <button type="button" class="btn-action-return" onclick='openModalKembalikan(<?= htmlspecialchars(json_encode($pa), ENT_QUOTES, "UTF-8") ?>)'>
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <polyline points="1 4 1 10 7 10"></polyline>
                                        <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                                    </svg>
                                    Kembalikan Sekarang
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
<?php endif; ?>

<!-- INFO BOX -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(20, 184, 166, 0.1); color: var(--teal-dark); display: flex; align-items: center; justify-content: center;">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <div>
            <span style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Alat Dikembalikan</span>
            <div style="font-size: 20px; font-weight: 700; color: #0f172a;"><?= count($daftarPengembalian) ?> Transaksi Selesai</div>
        </div>
    </div>

    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: <?= $totalDenda > 0 ? '#fee2e2' : '#f1f5f9' ?>; color: <?= $totalDenda > 0 ? '#b91c1c' : '#64748b' ?>; display: flex; align-items: center; justify-content: center;">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>
        <div>
            <span style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Akumulasi Denda</span>
            <div style="font-size: 20px; font-weight: 700; color: <?= $totalDenda > 0 ? '#b91c1c' : '#0f172a' ?>;">
                Rp<?= number_format($totalDenda, 0, ',', '.') ?>
            </div>
        </div>
    </div>
</div>

<!-- INVENTORY CARD & TABLE -->
<section class="inventory-section-card">
    <div class="inventory-header">
        <div>
            <h3 class="inventory-title">Catatan Pengembalian Alat Lab</h3>
            <p class="inventory-sub">Total <?= count($daftarPengembalian) ?> riwayat pengembalian alat.</p>
        </div>
        <form method="GET" action="index.php" class="search-box">
            <input type="hidden" name="c" value="peminjam">
            <input type="hidden" name="a" value="pengembalian">
            <span class="search-icon">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </span>
            <input type="text" name="search" placeholder="Cari kode atau alat..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </form>
    </div>

    <table class="inventory-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode & Alat Lab</th>
                <th>Kategori & Jml</th>
                <th>Waktu Pinjam</th>
                <th>Waktu Dikembalikan</th>
                <th>Kondisi Kembali</th>
                <th>Denda</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($daftarPengembalian)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                        Belum ada riwayat pengembalian alat lab.
                    </td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($daftarPengembalian as $pg): 
                    $hasDenda = ($pg['denda'] > 0);
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <div style="font-family: monospace; font-size: 11px; font-weight: 700; color: var(--primary-teal);"><?= htmlspecialchars($pg['kode_peminjaman'] ?? '-') ?></div>
                            <strong style="color: var(--primary-navy);"><?= htmlspecialchars($pg['nama_alat'] ?? 'Alat Lab') ?></strong>
                            <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($pg['kode_alat'] ?? '') ?></div>
                        </td>
                        <td>
                            <?= htmlspecialchars($pg['nama_kategori'] ?? 'Umum') ?>
                            <div style="font-size: 11px; color: var(--text-muted);"><?= (int)($pg['jumlah'] ?? 1) ?> unit</div>
                        </td>
                        <td style="white-space: nowrap;"><?= !empty($pg['waktu_pinjam']) ? date('H:i', strtotime($pg['waktu_pinjam'])) . ' WIB' : '-' ?></td>
                        <td style="white-space: nowrap;">
                            <strong style="color: var(--teal-dark);"><?= !empty($pg['waktu_kembali']) ? date('H:i', strtotime($pg['waktu_kembali'])) . ' WIB' : '-' ?></strong>
                            <?php if (!empty($pg['waktu_rencana_kembali'])): ?>
                                <div style="font-size: 11px; color: var(--text-muted);">Rencana: <?= date('H:i', strtotime($pg['waktu_rencana_kembali'])) ?> WIB</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background: #e0f2fe; color: #0369a1;">
                                <?= htmlspecialchars($pg['kondisi_kembali'] ?? 'Baik') ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($hasDenda): ?>
                                <strong style="color: #dc2626;">Rp<?= number_format($pg['denda'], 0, ',', '.') ?></strong>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 12px;">Rp0 (Nihil)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<!-- MODAL KONFIRMASI KEMBALIKAN ALAT -->
<div class="modal-overlay" id="modalKembalikanAlat" style="display: none;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 class="modal-title">Ajukan Pengembalian Alat Lab</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalKembalikanAlat')">&times;</button>
        </div>
        <form method="POST" action="index.php?c=peminjam&a=kembalikan_alat">
            <input type="hidden" name="id_peminjaman" id="kembali_id_peminjaman">
            <input type="hidden" name="redirect" value="pengembalian">
            
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px;">
                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Perangkat yang Diajukan Kembali:</div>
                <div style="font-weight: 700; color: var(--navy-primary); font-size: 15px;" id="kembali_nama_alat">-</div>
                <div style="font-size: 12px; color: var(--teal-primary); font-weight: 600; margin-top: 2px;">
                    Kode: <span id="kembali_kode">-</span> &bull; Jumlah: <span id="kembali_jumlah">1</span> Unit
                </div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                    Tenggat Rencana: <span id="kembali_rencana" style="font-weight: 600; color: #0f766e;">-</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Catatan Penyerahan Siswa <span style="font-size: 11px; color: var(--text-muted); font-weight: 400;">(Opsional)</span></label>
                <textarea name="catatan_siswa" class="form-control" rows="2" placeholder="Contoh: Alat sudah diserahkan di meja lab komputer / laboran"></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalKembalikanAlat')">Batal</button>
                <button type="submit" class="btn-modal-submit" style="background: var(--teal-primary);">Ajukan Pengembalian ke Petugas</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
function openModalKembalikan(data) {
    document.getElementById('kembali_id_peminjaman').value = data.id;
    document.getElementById('kembali_nama_alat').textContent = data.nama_alat;
    document.getElementById('kembali_kode').textContent = data.kode_peminjaman || data.kode_alat;
    document.getElementById('kembali_jumlah').textContent = data.jumlah;
    document.getElementById('kembali_rencana').textContent = (data.waktu_rencana_kembali ? data.waktu_rencana_kembali.substring(0, 5) + ' WIB' : '-');
    openModal('modalKembalikanAlat');
}
</script>

<?php require_once __DIR__ . '/peminjam_footer.php'; ?>
