<?php
$activePage = 'peminjaman';
$pageTitle = 'Persetujuan Peminjaman - Petugas Laboran';
require_once __DIR__ . '/petugas_header.php';
?>

<div class="admin-header-row">
    <div>
        <span class="admin-date-label">VERIFIKASI & PERSETUJUAN ALAT</span>
        <h1 class="admin-title">Persetujuan Peminjaman</h1>
        <p class="admin-subtitle">Verifikasi permohonan pinjam siswa, berikan persetujuan, atau serahkan fisik perangkat lab komputer.</p>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="auth-alert auth-alert-success">
        <span><?= htmlspecialchars($message) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="auth-alert auth-alert-error">
        <span><?= htmlspecialchars($error) ?></span>
    </div>
<?php endif; ?>

<section class="inventory-section-card">
    <div class="inventory-header">
        <div>
            <h3 class="inventory-title">Daftar Pengajuan Peminjaman</h3>
            <p class="inventory-sub">Total <?= count($daftarPeminjaman) ?> data permohonan peminjaman tercatat.</p>
        </div>
        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <form method="GET" action="index.php" style="margin: 0; display: flex; gap: 8px;">
                <input type="hidden" name="c" value="petugas">
                <input type="hidden" name="a" value="peminjaman">
                <select name="status_filter" class="form-control" style="font-size: 13px; padding: 6px 12px; height: 38px;" onchange="this.form.submit()">
                    <option value="">-- Semua Status --</option>
                    <option value="menunggu" <?= (($_GET['status_filter'] ?? '') === 'menunggu') ? 'selected' : '' ?>>Menunggu</option>
                    <option value="disetujui" <?= (($_GET['status_filter'] ?? '') === 'disetujui') ? 'selected' : '' ?>>Disetujui</option>
                    <option value="dipinjam" <?= (($_GET['status_filter'] ?? '') === 'dipinjam') ? 'selected' : '' ?>>Dipinjam</option>
                    <option value="dikembalikan" <?= (($_GET['status_filter'] ?? '') === 'dikembalikan') ? 'selected' : '' ?>>Dikembalikan</option>
                    <option value="ditolak" <?= (($_GET['status_filter'] ?? '') === 'ditolak') ? 'selected' : '' ?>>Ditolak</option>
                </select>
                <?php if (!empty($_GET['search'])): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($_GET['search']) ?>">
                <?php endif; ?>
            </form>

            <form method="GET" action="index.php" class="search-box" style="margin: 0;">
                <input type="hidden" name="c" value="petugas">
                <input type="hidden" name="a" value="peminjaman">
                <?php if (!empty($_GET['status_filter'])): ?>
                    <input type="hidden" name="status_filter" value="<?= htmlspecialchars($_GET['status_filter']) ?>">
                <?php endif; ?>
                <span class="search-icon">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
                <input type="text" name="search" placeholder="Cari siswa, kode, alat..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </form>
        </div>
    </div>

    <table class="inventory-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode & Siswa</th>
                <th>Alat Lab Diminta</th>
                <th>Keperluan / Jenis</th>
                <th>Waktu Pinjam</th>
                <th>Rencana Kembali</th>
                <th>Status</th>
                <th style="text-align: right;">Aksi Petugas</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($daftarPeminjaman)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                        Tidak ada data peminjaman yang sesuai dengan filter atau pencarian.
                    </td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($daftarPeminjaman as $p): 
                    $status = strtolower($p['status']);
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <div style="font-family: monospace; font-size: 11px; font-weight: 700; color: var(--primary-teal);"><?= htmlspecialchars($p['kode_peminjaman'] ?? '-') ?></div>
                            <strong style="color: var(--primary-navy);"><?= htmlspecialchars($p['nama_peminjam'] ?? 'Siswa') ?></strong>
                            <div style="font-size: 11px; color: var(--text-muted);">NIS: <?= htmlspecialchars($p['nis'] ?? '-') ?> &bull; <?= htmlspecialchars($p['kelas'] ?? '') ?></div>
                        </td>
                        <td class="col-instrument-name">
                            <?= htmlspecialchars($p['nama_alat'] ?? 'Alat Lab') ?>
                            <div style="font-size: 11px; color: var(--text-muted);"><?= (int)($p['jumlah'] ?? 1) ?> unit &bull; <?= htmlspecialchars($p['nama_kategori'] ?? '') ?></div>
                        </td>
                        <td>
                            <strong style="font-size: 12px; color: var(--text-secondary);"><?= htmlspecialchars($p['jenis_peminjaman'] ?? 'Praktek Lab') ?></strong>
                            <div style="font-size: 11px; color: var(--text-muted); max-width: 200px;"><?= htmlspecialchars($p['keperluan'] ?? '-') ?></div>
                        </td>
                        <td style="white-space: nowrap;"><?= !empty($p['waktu_pinjam']) ? date('H:i', strtotime($p['waktu_pinjam'])) . ' WIB' : '-' ?></td>
                        <td style="white-space: nowrap;">
                            <strong style="color: var(--teal-dark);"><?= !empty($p['waktu_rencana_kembali']) ? date('H:i', strtotime($p['waktu_rencana_kembali'])) . ' WIB' : '-' ?></strong>
                        </td>
                        <td>
                            <?php if ($status === 'menunggu'): ?>
                                <span class="stock-badge stock-badge-low" style="background:#fef3c7; color:#b45309; padding: 4px 10px; font-size: 12px;">
                                    ⏳ Menunggu
                                </span>
                            <?php elseif ($status === 'disetujui'): ?>
                                <span class="stock-badge" style="background:#dcfce7; color:#15803d; padding: 4px 10px; font-size: 12px; font-weight:700;">
                                    ✓ Disetujui
                                </span>
                            <?php elseif ($status === 'dipinjam'): ?>
                                <span class="stock-badge stock-badge-available" style="padding: 4px 10px; font-size: 12px;">
                                    💻 Sedang Dipinjam
                                </span>
                            <?php elseif ($status === 'menunggu_pengembalian'): ?>
                                <span class="stock-badge" style="background:#fef3c7; color:#b45309; border:1px solid #fde68a; font-weight:700; padding: 4px 10px; font-size: 12px;">
                                    ⏳ Menunggu Validasi Kembali
                                </span>
                                <div style="font-size: 11px; color: #92400e; margin-top: 3px;">Fisik diserahkan siswa</div>
                            <?php elseif ($status === 'dikembalikan'): ?>
                                <span class="stock-badge" style="background:#e0f2fe; color:#0369a1; font-weight:700; padding: 4px 10px; font-size: 12px;">
                                    Dikembalikan
                                </span>
                            <?php elseif ($status === 'ditolak'): ?>
                                <span class="stock-badge" style="background:#fee2e2; color:#b91c1c; font-weight:700; padding: 4px 10px; font-size: 12px;">
                                    Ditolak
                                </span>
                            <?php elseif ($status === 'dibatalkan'): ?>
                                <span class="stock-badge" style="background:#f1f5f9; color:#64748b; font-weight:700; padding: 4px 10px; font-size: 12px;">
                                    Dibatalkan
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="table-actions-cell">
                            <?php if ($status === 'menunggu'): ?>
                                <a href="index.php?c=petugas&a=setujui_peminjaman&id=<?= $p['id'] ?>" class="btn-action-edit" style="background: #10b981; color: white;" onclick="return confirm('Setujui permohonan pinjam ini?')">
                                    Setujui
                                </a>
                                <a href="index.php?c=petugas&a=tolak_peminjaman&id=<?= $p['id'] ?>" class="btn-action-delete" onclick="return confirm('Tolak permohonan pinjam ini?')">
                                    Tolak
                                </a>
                            <?php elseif ($status === 'disetujui'): ?>
                                <a href="index.php?c=petugas&a=serahkan_alat&id=<?= $p['id'] ?>" class="btn-action-edit" style="background: #0284c7; color: white;" onclick="return confirm('Konfirmasi penyerahan fisik alat kepada siswa?')">
                                    Serahkan Fisik
                                </a>
                            <?php elseif ($status === 'menunggu_pengembalian'): ?>
                                <button type="button" class="btn-action-edit" style="background: #0d9488; color: white; border: none; font-weight: 700; cursor: pointer; padding: 6px 14px; border-radius: 6px;" onclick='openModalVerifikasi(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8") ?>)'>
                                    🔍 Validasi & QC
                                </button>
                            <?php elseif ($status === 'dipinjam'): 
                                $alreadyReminded = isset($notifikasiModel) && $notifikasiModel->hasUnreadReminder($p['id']);
                            ?>
                                <button type="button" class="btn-action-edit" style="background: #0d9488; color: white; border: none; font-weight: 600; cursor: pointer; padding: 5px 10px; border-radius: 4px; margin-bottom: 4px;" onclick='openModalVerifikasi(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8") ?>)'>
                                    ✓ Terima Kembali
                                </button>
                                <a href="index.php?c=petugas&a=ingatkan_kembali&id=<?= $p['id'] ?>" class="btn-action-edit" style="background: #f59e0b; color: white; margin-bottom: 4px;" onclick="return confirm('Kirim notifikasi pengingat pengembalian kepada siswa ini?')">
                                    🔔 Pengingat
                                </a>
                                <?php if ($alreadyReminded): ?>
                                    <div style="font-size: 11px; color: #b45309; font-weight: 600; margin-top: 3px;">✓ Notif Terkirim</div>
                                <?php endif; ?>
                                <?php 
                                    $phone = preg_replace('/[^0-9]/', '', $p['no_telp'] ?? '');
                                    if (substr($phone, 0, 1) === '0') $phone = '62' . substr($phone, 1);
                                    if (!empty($phone)):
                                        $waMsg = urlencode("Halo {$p['nama_peminjam']}, mohon segera mengembalikan perangkat {$p['nama_alat']} (Kode: {$p['kode_peminjaman']}) ke Laboratorium Komputer. Terima kasih.");
                                ?>
                                    <a href="https://wa.me/<?= $phone ?>?text=<?= $waMsg ?>" target="_blank" style="display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; background: #16a34a; color: white; text-decoration: none; font-weight: 600; margin-top: 4px;">
                                        WhatsApp Siswa
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="font-size: 12px; color: var(--text-muted);">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<div class="modal-overlay" id="modalVerifikasiPengembalian" style="display: none;">
    <div class="modal-content" style="max-width: 580px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h3 class="modal-title">Validasi Fisik & Quality Control Pengembalian</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalVerifikasiPengembalian')">&times;</button>
        </div>
        <form method="POST" action="index.php?c=petugas&a=verifikasi_pengembalian">
            <input type="hidden" name="id_peminjaman" id="verif_id_peminjaman">
            <input type="hidden" name="redirect" value="peminjaman">

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

            <div class="form-group">
                <label class="form-label">
                    Waktu Pengembalian Resmi (WIB)
                    <span style="font-size: 11px; color: var(--teal-dark); font-weight: 600;">(Waktu nyata dari Petugas Lab)</span>
                </label>
                <input type="time" name="waktu_kembali" id="verif_input_waktu" class="form-control" value="<?= date('H:i') ?>" required onchange="hitungDendaOtomatis()">
            </div>

            <div class="form-group">
                <label class="form-label">Hasil Quality Control (Cek Fisik Alat) <span style="color:#dc2626;">*</span></label>
                <select name="kondisi_kembali" id="verif_select_kondisi" class="form-control" required onchange="hitungDendaOtomatis()">
                    <option value="Baik & Lengkap" data-denda="0">Baik & Lengkap (Normal tanpa cacat)</option>
                    <option value="Rusak Ringan" data-denda="20000">Rusak Ringan (Lecet / Cacat minor - Denda Rp20.000)</option>
                    <option value="Rusak Berat" data-denda="50000">Rusak Berat (Patah / Korsleting - Denda Rp50.000)</option>
                    <option value="Komponen Hilang" data-denda="100000">Komponen Hilang (Kabel / adaptor / alat hilang - Denda Rp100.000)</option>
                </select>
            </div>

            <div style="background: #f1f5f9; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px; font-size: 12px;">
                <div style="font-weight: 700; color: var(--primary-navy); margin-bottom: 6px;">Kalkulator Denda Otomatis:</div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Denda Keterlambatan:</span>
                    <strong id="verif_rincian_terlambat">Rp0</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Denda Kondisi Fisik (QC):</span>
                    <strong id="verif_rincian_kondisi">Rp0</strong>
                </div>
                <div style="display: flex; justify-content: space-between; border-top: 1px solid #cbd5e1; padding-top: 4px; font-weight: 700; color: #dc2626;">
                    <span>Total Denda Disarankan:</span>
                    <span id="verif_total_disarankan">Rp0</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nominal Denda Akhir (Rp)</label>
                <input type="number" name="denda" id="verif_input_denda" class="form-control" min="0" value="0" required>
            </div>

            <div class="form-group">
                <label class="form-label">Catatan QC Petugas <span style="font-size: 11px; color: var(--text-muted); font-weight: 400;">(Opsional)</span></label>
                <textarea name="catatan_qc" class="form-control" rows="2" placeholder="Catatan hasil pemeriksaan fisik perangkat..."></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalVerifikasiPengembalian')">Batal</button>
                <button type="submit" class="btn-modal-submit" style="background: #0d9488;">✓ Validasi & Pulihkan Stok</button>
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
            dendaTerlambat = selisihJam * 5000;
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
</script>

<?php require_once __DIR__ . '/petugas_footer.php'; ?>
