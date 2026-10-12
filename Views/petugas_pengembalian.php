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

<!-- 1. PERMOHONAN PENGEMBALIAN MENUNGGU VERIFIKASI FISIK (QUALITY CONTROL) -->
<section class="inventory-section-card" style="border: 2px solid #f59e0b; margin-bottom: 28px; background: #fffdf5;">
    <div class="inventory-header" style="border-bottom: 1px solid #fde68a;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: #f59e0b; color: white; font-size: 13px; font-weight: bold;">
                    <?= count($menungguPengembalian ?? []) ?>
                </span>
                <h3 class="inventory-title" style="color: #92400e;">Permohonan Pengembalian Menunggu Validasi & QC Fisik</h3>
            </div>
            <p class="inventory-sub" style="color: #b45309; margin-top: 4px;">
                Wajib diperiksa: Petugas harus melihat dan memegang fisik barang sebelum validasi untuk mencegah "peminjaman gaib", manipulasi denda, dan kerusakan tersembunyi.
            </p>
        </div>
    </div>

    <table class="inventory-table">
        <thead>
            <tr style="background: rgba(245, 158, 11, 0.08);">
                <th>No</th>
                <th>Kode & Siswa</th>
                <th>Alat Lab Diajukan</th>
                <th>Waktu Pinjam</th>
                <th>Tenggat Rencana</th>
                <th>Status Validasi</th>
                <th style="text-align: right;">Tindakan Petugas</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($menungguPengembalian)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 26px;">
                        <div style="font-size: 13px; color: #78716c;">Tidak ada pengembalian yang sedang menunggu verifikasi saat ini. Semua alat di lab terpantau tertib.</div>
                    </td>
                </tr>
            <?php else: ?>
                <?php $noMenunggu = 1; foreach ($menungguPengembalian as $mp): 
                    $tKembali = strtotime($mp['waktu_rencana_kembali'] ?? '00:00:00');
                    $tSekarang = strtotime(date('H:i:s'));
                    $isLate = ($tSekarang > $tKembali);
                ?>
                    <tr style="background: white;">
                        <td><?= $noMenunggu++ ?></td>
                        <td>
                            <div style="font-family: monospace; font-size: 11px; font-weight: 700; color: var(--primary-teal);"><?= htmlspecialchars($mp['kode_peminjaman'] ?? '-') ?></div>
                            <strong style="color: var(--primary-navy);"><?= htmlspecialchars($mp['nama_peminjam'] ?? 'Siswa') ?></strong>
                            <div style="font-size: 11px; color: var(--text-muted);">NIS: <?= htmlspecialchars($mp['nis'] ?? '-') ?> &bull; <?= htmlspecialchars($mp['kelas'] ?? '') ?></div>
                            <?php if (!empty($mp['no_telp'])): ?>
                                <div style="font-size: 11px; color: var(--text-muted);">HP: <?= htmlspecialchars($mp['no_telp']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="col-instrument-name">
                            <strong style="color: var(--primary-navy);"><?= htmlspecialchars($mp['nama_alat'] ?? 'Alat Lab') ?></strong>
                            <div style="font-size: 11px; color: var(--text-muted);">
                                <?= (int)($mp['jumlah'] ?? 1) ?> unit &bull; <?= htmlspecialchars($mp['nama_kategori'] ?? 'Lab') ?>
                            </div>
                        </td>
                        <td style="white-space: nowrap;"><?= !empty($mp['waktu_pinjam']) ? date('H:i', strtotime($mp['waktu_pinjam'])) . ' WIB' : '-' ?></td>
                        <td style="white-space: nowrap;">
                            <strong style="color: <?= $isLate ? '#dc2626' : 'var(--teal-dark)' ?>;">
                                <?= !empty($mp['waktu_rencana_kembali']) ? date('H:i', strtotime($mp['waktu_rencana_kembali'])) . ' WIB' : '-' ?>
                            </strong>
                            <?php if ($isLate): ?>
                                <div style="font-size: 11px; color: #dc2626; font-weight: 700;">⚠ Terlambat</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="stock-badge" style="background:#fef3c7; color:#b45309; border:1px solid #fde68a; font-weight:700; padding: 4px 10px; font-size: 12px;">
                                ⏳ Menunggu QC Fisik
                            </span>
                        </td>
                        <td class="table-actions-cell">
                            <button type="button" class="btn-action-edit" style="background: #0d9488; color: white; border: none; font-weight: 700; cursor: pointer; padding: 7px 16px; border-radius: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onclick='openModalVerifikasi(<?= htmlspecialchars(json_encode($mp), ENT_QUOTES, "UTF-8") ?>)'>
                                🔍 Validasi & QC Fisik
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<!-- 2. RIWAYAT PENGEMBALIAN SELESAI -->
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

<!-- MODAL VERIFIKASI & QC PENGEMBALIAN (SOLUSI 5 POIN) -->
<div class="modal-overlay" id="modalVerifikasiPengembalian" style="display: none;">
    <div class="modal-content" style="max-width: 580px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h3 class="modal-title">Validasi Fisik & Quality Control Pengembalian</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalVerifikasiPengembalian')">&times;</button>
        </div>
        <form method="POST" action="index.php?c=petugas&a=verifikasi_pengembalian">
            <input type="hidden" name="id_peminjaman" id="verif_id_peminjaman">
            <input type="hidden" name="redirect" value="pengembalian">

            <!-- Data Ringkasan Peminjaman -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                    <div>
                        <span style="font-size: 11px; font-weight: 700; color: var(--primary-teal);" id="verif_kode">-</span>
                        <h4 style="margin: 2px 0 0 0; font-size: 16px; color: var(--primary-navy);" id="verif_alat">-</h4>
                        <div style="font-size: 12px; color: var(--text-muted);">Jumlah: <strong id="verif_jumlah">1</strong> unit</div>
                    </div>
                    <span class="stock-badge" style="background: #dcfce7; color: #166534; font-size: 11px;">
                        Fisik di Meja Lab
                    </span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
                    <div>Peminjam: <strong id="verif_peminjam" style="color: var(--primary-navy);">-</strong></div>
                    <div>NIS / Kelas: <span id="verif_nis_kelas">-</span></div>
                    <div>Waktu Pinjam: <span id="verif_waktu_pinjam">-</span></div>
                    <div>Tenggat Kembali: <strong id="verif_waktu_rencana" style="color: #0369a1;">-</strong></div>
                </div>
            </div>

            <!-- 1. Waktu Pengembalian Resmi (Sistem Petugas, Anti Manipulasi Waktu) -->
            <div class="form-group">
                <label class="form-label">
                    Waktu Pengembalian Resmi (WIB)
                    <span style="font-size: 11px; color: var(--teal-dark); font-weight: 600;">(Dihitung saat Petugas menekan verifikasi)</span>
                </label>
                <input type="time" name="waktu_kembali" id="verif_input_waktu" class="form-control" value="<?= date('H:i') ?>" required onchange="hitungDendaOtomatis()">
                <small style="color: var(--text-muted); font-size: 11px;">* Mengunci waktu nyata untuk mencegah manipulasi batas denda keterlambatan.</small>
            </div>

            <!-- 2. Pemeriksaan Kondisi Fisik Alat (Quality Control Anti Kerusakan Sembunyi) -->
            <div class="form-group">
                <label class="form-label">Hasil Quality Control (Cek Kondisi Fisik Alat) <span style="color:#dc2626;">*</span></label>
                <select name="kondisi_kembali" id="verif_select_kondisi" class="form-control" required onchange="hitungDendaOtomatis()">
                    <option value="Baik & Lengkap" data-denda="0">Baik & Lengkap (Normal tanpa cacat / kerusakan - Denda Rp0)</option>
                    <option value="Rusak Ringan" data-denda="25000">Rusak Ringan (Lecet / Cacat minor - Denda Rp25.000)</option>
                    <option value="Rusak Sedang" data-denda="100000">Rusak Sedang (Tombol patah / soket goyang / retak - Denda Rp100.000)</option>
                    <option value="Rusak Berat" data-denda="150000">Rusak Berat (Mati total / patah parah / layar hancur - Denda Rp150.000)</option>
                    <option value="Komponen Hilang" data-denda="150000">Komponen Hilang (Kabel / adaptor / alat hilang - Denda Rp150.000)</option>
                </select>
                <small style="color: var(--text-muted); font-size: 11px;">* Petugas wajib memeriksa kondisi fisik alat di meja lab sebelum memilih status QC.</small>
            </div>

            <!-- 3. Rincian Kalkulasi Denda Otomatis -->
            <div style="background: #f1f5f9; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px; font-size: 12px; border-left: 4px solid var(--teal-dark);">
                <div style="font-weight: 700; color: var(--primary-navy); margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                    <span>Kalkulator Denda Otomatis Sistem:</span>
                    <span style="font-size: 10px; background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-weight: 700;">TERKUNCI SISTEM</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Denda Keterlambatan (Rp5.000/jam):</span>
                    <strong id="verif_rincian_terlambat">Rp0</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Denda Kondisi Fisik (QC):</span>
                    <strong id="verif_rincian_kondisi">Rp0</strong>
                </div>
                <div style="display: flex; justify-content: space-between; border-top: 1px solid #cbd5e1; padding-top: 6px; font-weight: 800; color: #dc2626; font-size: 13px;">
                    <span>Total Denda Otomatis:</span>
                    <span id="verif_total_disarankan">Rp0</span>
                </div>
            </div>

            <!-- Input Denda Akhir (DIKUNCI / READONLY - SISTEM RESMI) -->
            <div class="form-group">
                <label class="form-label">
                    Total Denda yang Dikenakan (Rp)
                    <span style="font-size: 11px; color: #dc2626; font-weight: 600;">(Dihitung Otomatis - Tidak Dapat Diubah Petugas)</span>
                </label>
                <input type="number" name="denda" id="verif_input_denda" class="form-control" min="0" value="0" readonly style="background: #f8fafc; font-weight: 700; color: #dc2626; cursor: not-allowed;">
                <small style="color: var(--text-muted); font-size: 11px;">🔒 Sistem mengunci nominal denda secara otomatis untuk menjamin keadilan & integritas.</small>
            </div>

            <!-- Catatan Pemeriksaan QC Petugas -->
            <div class="form-group">
                <label class="form-label">Catatan QC Pemeriksaan Petugas <span style="font-size: 11px; color: var(--text-muted); font-weight: 400;">(Opsional)</span></label>
                <textarea name="catatan_qc" class="form-control" rows="2" placeholder="Contoh: Kabel lengkap, casing sedikit berdebu, fungsi normal."></textarea>
            </div>

            <!-- Info Saksi Digital & Stok -->
            <div style="background: #e0f2fe; border: 1px solid #bae6fd; border-radius: 6px; padding: 10px 12px; margin-bottom: 16px; font-size: 11.5px; color: #0369a1; line-height: 1.4;">
                <div><strong>Saksi Digital:</strong> Nama Petugas (<?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Petugas Lab') ?>) akan tercatat permanen di Log Audit sebagai penerima barang yang bertanggung jawab.</div>
                <div style="margin-top: 3px;"><strong>Stok Database:</strong> Stok alat akan otomatis bertambah (+1) setelah tombol konfirmasi ditekan.</div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalVerifikasiPengembalian')">Batal</button>
                <button type="submit" class="btn-modal-submit" style="background: #0d9488;">✓ Validasi & Pulihkan Stok Lab</button>
            </div>
        </form>
    </div>
</div>

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
var currentVerifData = null;

function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function openModalVerifikasi(data) {
    currentVerifData = data;
    document.getElementById('verif_id_peminjaman').value = data.id;
    document.getElementById('verif_kode').textContent = data.kode_peminjaman || '-';
    document.getElementById('verif_alat').textContent = data.nama_alat || 'Alat Lab';
    document.getElementById('verif_jumlah').textContent = data.jumlah || '1';
    document.getElementById('verif_peminjam').textContent = data.nama_peminjam || 'Siswa';
    document.getElementById('verif_nis_kelas').textContent = (data.nis || '-') + ' / ' + (data.kelas || '-');
    document.getElementById('verif_waktu_pinjam').textContent = data.waktu_pinjam ? data.waktu_pinjam.substring(0, 5) + ' WIB' : '-';
    document.getElementById('verif_waktu_rencana').textContent = data.waktu_rencana_kembali ? data.waktu_rencana_kembali.substring(0, 5) + ' WIB' : '-';
    
    // Set default waktu kembali saat ini (jam:menit)
    var now = new Date();
    var hh = String(now.getHours()).padStart(2, '0');
    var mm = String(now.getMinutes()).padStart(2, '0');
    document.getElementById('verif_input_waktu').value = hh + ':' + mm;

    document.getElementById('verif_select_kondisi').value = 'Baik & Lengkap';
    hitungDendaOtomatis();

    openModal('modalVerifikasiPengembalian');
}

function hitungDendaOtomatis() {
    if (!currentVerifData) return;

    var wktRencanaStr = currentVerifData.waktu_rencana_kembali;
    var wktKembaliStr = document.getElementById('verif_input_waktu').value;
    var dendaTerlambat = 0;

    if (wktRencanaStr && wktKembaliStr) {
        var partsR = wktRencanaStr.split(':');
        var partsK = wktKembaliStr.split(':');
        var minR = parseInt(partsR[0]) * 60 + parseInt(partsR[1]);
        var minK = parseInt(partsK[0]) * 60 + parseInt(partsK[1]);

        if (minK > minR) {
            var selisihJam = Math.ceil((minK - minR) / 60);
            dendaTerlambat = selisihJam * 5000; // Rp5.000 / jam
        }
    }

    var selectKondisi = document.getElementById('verif_select_kondisi');
    var selectedOpt = selectKondisi.options[selectKondisi.selectedIndex];
    var dendaKondisi = parseInt(selectedOpt.getAttribute('data-denda') || 0);

    var totalDenda = dendaTerlambat + dendaKondisi;

    document.getElementById('verif_rincian_terlambat').textContent = 'Rp' + dendaTerlambat.toLocaleString('id-ID');
    document.getElementById('verif_rincian_kondisi').textContent = 'Rp' + dendaKondisi.toLocaleString('id-ID');
    document.getElementById('verif_total_disarankan').textContent = 'Rp' + totalDenda.toLocaleString('id-ID');
    document.getElementById('verif_input_denda').value = totalDenda;
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
