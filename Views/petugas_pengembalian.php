<?php
$activePage = 'pengembalian';
$pageTitle = 'Kelola Pengembalian Alat - Petugas Laboran';
require_once __DIR__ . '/petugas_header.php';
?>

<div class="admin-header-row">
    <div>
        <span class="admin-date-label">VALIDASI & QUALITY CONTROL LAB</span>
        <h1 class="admin-title">Pengembalian Alat Lab</h1>
        <p class="admin-subtitle">Verifikasi fisik barang secara langsung di meja lab, lakukan Quality Control (QC), hitung denda akurat, dan pulihkan stok inventaris.</p>
    </div>
    <button type="button" class="btn-add-instrument" onclick="openModal('modalTambahPengembalian')">
        + Catat Pengembalian Manual
    </button>
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

<!-- RIWAYAT PENGEMBALIAN SELESAI -->
<section class="inventory-section-card">
    <div class="inventory-header">
        <div>
            <h3 class="inventory-title">Riwayat Pengembalian Terverifikasi</h3>
            <p class="inventory-sub">Total <?= count($daftarPengembalian) ?> transaksi pengembalian selesai & stok dipulihkan.</p>
        </div>
        <form method="GET" action="index.php" class="search-box">
            <input type="hidden" name="c" value="petugas">
            <input type="hidden" name="a" value="pengembalian">
            <span class="search-icon">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </span>
            <input type="text" name="search" placeholder="Cari siswa, kode, alat..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </form>
    </div>

    <table class="inventory-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode & Siswa</th>
                <th>Alat Lab Dikembalikan</th>
                <th>Waktu Pinjam</th>
                <th>Waktu Diterima Petugas</th>
                <th>Kondisi Fisik (QC)</th>
                <th>Petugas Penerima</th>
                <th>Denda</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($daftarPengembalian)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                        Belum ada riwayat pengembalian alat.
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
                            <strong style="color: var(--primary-navy);"><?= htmlspecialchars($pg['nama_peminjam'] ?? 'Siswa') ?></strong>
                            <div style="font-size: 11px; color: var(--text-muted);">NIS: <?= htmlspecialchars($pg['nis'] ?? '-') ?> &bull; <?= htmlspecialchars($pg['kelas'] ?? '') ?></div>
                        </td>
                        <td class="col-instrument-name">
                            <?= htmlspecialchars($pg['nama_alat'] ?? 'Alat Lab') ?>
                            <div style="font-size: 11px; color: var(--text-muted);"><?= (int)($pg['jumlah'] ?? 1) ?> unit &bull; <?= htmlspecialchars($pg['nama_kategori'] ?? '') ?></div>
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
                            <strong style="color: #475569; font-size: 12px;"><?= htmlspecialchars($pg['nama_petugas'] ?? 'Petugas Lab') ?></strong>
                            <div style="font-size: 10.5px; color: #16a34a; font-weight: 600;">✓ Saksi Digital Sah</div>
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

<!-- MODAL TAMBAH PENGEMBALIAN MANUAL -->
<div class="modal-overlay" id="modalTambahPengembalian">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Catat Pengembalian Alat Lab Manual</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahPengembalian')">&times;</button>
        </div>
        <form method="POST" action="index.php?c=petugas&a=tambah_pengembalian">
            <div class="form-group">
                <label class="form-label">Pilih Peminjaman Aktif</label>
                <select name="id_peminjaman" class="form-control" required>
                    <option value="">-- Pilih Peminjaman yang Sedang Berjalan --</option>
                    <?php if (empty($peminjamanAktif)): ?>
                        <option value="" disabled>Tidak ada peminjaman aktif saat ini</option>
                    <?php else: ?>
                        <?php foreach ($peminjamanAktif as $pa): ?>
                            <option value="<?= $pa['id'] ?>">
                                [<?= htmlspecialchars($pa['kode_peminjaman']) ?>] <?= htmlspecialchars($pa['nama_peminjam']) ?> - <?= htmlspecialchars($pa['nama_alat']) ?> (<?= $pa['jumlah'] ?> unit)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-row" style="display: flex; gap: 16px;">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Waktu Pengembalian (WIB)</label>
                    <input type="time" name="waktu_kembali" id="manual_input_waktu" class="form-control" value="<?= date('H:i') ?>" required onchange="hitungDendaManual()">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Kondisi Alat Saat Kembali</label>
                    <select name="kondisi_kembali" id="manual_select_kondisi" class="form-control" required onchange="hitungDendaManual()">
                        <option value="Baik & Lengkap" data-denda="0">Baik & Lengkap (Denda Rp0)</option>
                        <option value="Rusak Ringan" data-denda="25000">Rusak Ringan (Denda Rp25.000)</option>
                        <option value="Rusak Sedang" data-denda="100000">Rusak Sedang (Denda Rp100.000)</option>
                        <option value="Rusak Berat" data-denda="150000">Rusak Berat (Denda Rp150.000)</option>
                        <option value="Komponen Hilang" data-denda="150000">Komponen Hilang (Denda Rp150.000)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Denda Otomatis Sistem (Rp) <span style="font-size: 11px; color: #dc2626; font-weight: 600;">(Terkunci)</span></label>
                <input type="number" name="denda" id="manual_input_denda" class="form-control" min="0" value="0" readonly style="background: #f8fafc; font-weight: 700; color: #dc2626; cursor: not-allowed;">
                <small style="color: var(--text-muted); font-size: 11px;">🔒 Denda dihitung otomatis oleh sistem.</small>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalTambahPengembalian')">Batal</button>
                <button type="submit" class="btn-modal-submit">Proses Pengembalian</button>
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

function hitungDendaManual() {
    var selectKondisi = document.getElementById('manual_select_kondisi');
    if (!selectKondisi) return;
    var selectedOpt = selectKondisi.options[selectKondisi.selectedIndex];
    var dendaKondisi = parseInt(selectedOpt.getAttribute('data-denda') || 0);
    document.getElementById('manual_input_denda').value = dendaKondisi;
}
</script>

<?php require_once __DIR__ . '/petugas_footer.php'; ?>
