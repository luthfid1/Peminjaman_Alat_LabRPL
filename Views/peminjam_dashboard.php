<?php
$activePage = 'dashboard';
$pageTitle = 'Dashboard Siswa - Lab Komputer';
require_once __DIR__ . '/peminjam_header.php';
?>

<div class="admin-header-row">
    <div>
        <span class="admin-date-label"><?= htmlspecialchars($tglStr) ?></span>
        <h1 class="admin-title">Halo, <?= htmlspecialchars($profile['nama'] ?? $_SESSION['user']['nama'] ?? 'Siswa') ?></h1>
        <p class="admin-subtitle">Selamat datang di Ruang Siswa Lab Komputer. Pantau status peminjaman perangkat dan ajukan alat untuk praktikum sekolah.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="index.php?c=peminjam&a=daftar_alat" class="btn-add-instrument">
            + Pinjam Alat Lab
        </a>
    </div>
</div>

<!-- 4 METRIC CARDS -->
<div class="admin-metrics-grid">
    <div class="metric-card">
        <div class="metric-top-row">
            <span class="metric-label">Menunggu Persetujuan</span>
            <div class="metric-icon-wrap" style="background: rgba(245, 158, 11, 0.12); color: #d97706;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
        </div>
        <div class="metric-number"><?= (int)$totalMenunggu ?></div>
        <div class="metric-sub" style="color: #b45309;">Menunggu petugas lab</div>
    </div>

    <div class="metric-card">
        <div class="metric-top-row">
            <span class="metric-label">Sedang Digunakan</span>
            <div class="metric-icon-wrap">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                    <line x1="8" y1="21" x2="16" y2="21"></line>
                    <line x1="12" y1="17" x2="12" y2="21"></line>
                </svg>
            </div>
        </div>
        <div class="metric-number"><?= (int)$totalDipinjam ?></div>
        <div class="metric-sub">Peminjaman aktif</div>
    </div>

    <div class="metric-card">
        <div class="metric-top-row">
            <span class="metric-label">Selesai Dikembalikan</span>
            <div class="metric-icon-wrap">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="9 11 12 14 22 4"></polyline>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
            </div>
        </div>
        <div class="metric-number"><?= (int)$totalSelesai ?></div>
        <div class="metric-sub">Peminjaman selesai</div>
    </div>

    <div class="metric-card">
        <div class="metric-top-row">
            <span class="metric-label">Denda Tercatat</span>
            <div class="metric-icon-wrap" style="<?= $totalDenda > 0 ? 'background: #fee2e2; color: #b91c1c;' : '' ?>">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
        </div>
        <div class="metric-number" style="font-size: 24px; <?= $totalDenda > 0 ? 'color: #b91c1c;' : '' ?>">
            Rp<?= number_format($totalDenda, 0, ',', '.') ?>
        </div>
        <div class="metric-sub" style="<?= $totalDenda > 0 ? 'color: #b91c1c;' : 'color: var(--teal-dark);' ?>">
            <?= $totalDenda > 0 ? 'Keterlambatan/kerusakan' : 'Bebas denda lab' ?>
        </div>
    </div>
</div>

<!-- CTA LAB BANNER -->
<div style="background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%); border-radius: 12px; padding: 22px 28px; color: white; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 15px rgba(20, 184, 166, 0.2);">
    <div>
        <h3 style="margin: 0 0 6px 0; font-size: 17px; font-weight: 700;">Perangkat Praktikum Lab Komputer Tersedia</h3>
        <p style="margin: 0; font-size: 13px; opacity: 0.95; max-width: 600px;">Butuh kabel LAN, MikroTik router, PC workstation, atau modul IoT untuk jam pelajaran praktikum? Ajukan permohonan pinjam secara mandiri.</p>
    </div>
    <a href="index.php?c=peminjam&a=daftar_alat" style="background: white; color: #0f766e; padding: 10px 20px; border-radius: 8px; font-weight: 700; text-decoration: none; font-size: 13px; white-space: nowrap; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        Katalog Alat Lab &rarr;
    </a>
</div>

<!-- RIWAYAT PEMINJAMAN TERKINI -->
<section class="inventory-section-card">
    <div class="inventory-header">
        <div>
            <h3 class="inventory-title">Riwayat Peminjaman Anda</h3>
            <p class="inventory-sub">Daftar permohonan dan riwayat peminjaman alat lab terkini.</p>
        </div>
        <a href="index.php?c=peminjam&a=peminjaman" style="font-size: 13px; font-weight: 600; color: var(--teal-dark); text-decoration: none;">
            Lihat Semua &rarr;
        </a>
    </div>

    <table class="inventory-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode & Alat Lab</th>
                <th>Kategori</th>
                <th>Jumlah</th>
                <th>Waktu Pinjam</th>
                <th>Rencana Kembali</th>
                <th>Status</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($daftarPinjamTerbaru)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                        Anda belum memiliki riwayat peminjaman alat lab. Klik "+ Pinjam Alat Lab" untuk mengajukan alat praktikum.
                    </td>
                </tr>
            <?php else: ?>
                <?php 
                $no = 1; 
                $top5 = array_slice($daftarPinjamTerbaru, 0, 5);
                foreach ($top5 as $p): 
                    $status = strtolower($p['status']);
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <div style="font-family: monospace; font-size: 11px; font-weight: 700; color: var(--primary-teal); margin-bottom: 2px;"><?= htmlspecialchars($p['kode_peminjaman'] ?? '-') ?></div>
                            <?php if (!empty($p['items']) && count($p['items']) > 1): ?>
                                <span style="font-size: 11px; font-weight: 700; background: #e0f2fe; color: #0284c7; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-bottom: 3px;">
                                    📦 <?= count($p['items']) ?> Jenis Alat
                                </span>
                                <div style="display: flex; flex-direction: column; gap: 3px;">
                                    <?php foreach ($p['items'] as $item): ?>
                                        <div style="font-size: 12px; color: var(--primary-navy);">
                                            &bull; <strong><?= htmlspecialchars($item['nama_alat']) ?></strong> 
                                            <span style="font-size: 11px; color: var(--text-muted);">(<?= (int)$item['jumlah'] ?> unit)</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <strong style="color: var(--primary-navy);"><?= htmlspecialchars($p['nama_alat'] ?? 'Alat Lab') ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($p['kode_alat'] ?? '') ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= (!empty($p['items']) && count($p['items']) > 1) ? 'Multi-Alat' : htmlspecialchars($p['nama_kategori'] ?? '-') ?>
                        </td>
                        <td>
                            <strong><?= (int)($p['jumlah'] ?? 1) ?></strong> unit
                            <?php if (!empty($p['items']) && count($p['items']) > 1): ?>
                                <div style="font-size: 11px; color: var(--text-muted);">(<?= count($p['items']) ?> macam)</div>
                            <?php endif; ?>
                        </td>
                        <td><?= !empty($p['waktu_pinjam']) ? date('H:i', strtotime($p['waktu_pinjam'])) . ' WIB' : '-' ?></td>
                        <td>
                            <strong style="color: var(--teal-dark);"><?= !empty($p['waktu_rencana_kembali']) ? date('H:i', strtotime($p['waktu_rencana_kembali'])) . ' WIB' : '-' ?></strong>
                        </td>
                        <td>
                            <?php if ($status === 'menunggu'): ?>
                                <span class="stock-badge stock-badge-low" style="background:#fef3c7; color:#b45309;">Menunggu Persetujuan</span>
                            <?php elseif ($status === 'disetujui' || $status === 'dipinjam'): ?>
                                <span class="stock-badge stock-badge-available">Sedang Dipinjam</span>
                            <?php elseif ($status === 'menunggu_pengembalian' || $status === 'menunggu_validasi'): ?>
                                <span class="stock-badge" style="background:#fef3c7; color:#b45309; border: 1px solid #fde68a; font-weight:700;">⏳ Menunggu Validasi</span>
                            <?php elseif ($status === 'dikembalikan'): ?>
                                <span class="stock-badge" style="background:#e0f2fe; color:#0369a1; font-weight:700;">✅ Selesai</span>
                            <?php elseif ($status === 'ditolak'): ?>
                                <span class="stock-badge" style="background:#fee2e2; color:#b91c1c; font-weight:700;">Ditolak</span>
                            <?php elseif ($status === 'dibatalkan'): ?>
                                <span class="stock-badge" style="background:#f1f5f9; color:#64748b; font-weight:700;">Dibatalkan</span>
                            <?php endif; ?>
                        </td>
                        <td class="table-actions-cell">
                            <?php if ($status === 'menunggu'): ?>
                                <a href="index.php?c=peminjam&a=batalkan_peminjaman&id=<?= $p['id'] ?>" class="btn-action-delete" onclick="return confirm('Batalkan pengajuan peminjaman alat ini?')">
                                    Batalkan
                                </a>
                            <?php elseif ($status === 'disetujui' || $status === 'dipinjam'): ?>
                                <button type="button" class="btn-action-return" onclick='openModalKembalikan(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8") ?>)'>
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <polyline points="1 4 1 10 7 10"></polyline>
                                        <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                                    </svg>
                                    Kembalikan Alat
                                </button>
                            <?php elseif ($status === 'menunggu_pengembalian' || $status === 'menunggu_validasi'): ?>
                                <span style="font-size: 11px; color: #b45309; font-weight: 700; background: #fffbeb; padding: 4px 8px; border-radius: 4px; border: 1px dashed #f59e0b; display: inline-block;">
                                    Di Meja Lab
                                </span>
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

<!-- MODAL KONFIRMASI KEMBALIKAN ALAT -->
<div class="modal-overlay" id="modalKembalikanAlat" style="display: none;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 class="modal-title">Ajukan Pengembalian Alat Lab</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalKembalikanAlat')">&times;</button>
        </div>
        <form method="POST" action="index.php?c=peminjam&a=kembalikan_alat">
            <input type="hidden" name="id_peminjaman" id="kembali_id_peminjaman">
            <input type="hidden" name="redirect" value="dashboard">
            
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px; font-size: 12px; color: #92400e; line-height: 1.5;">
                <div style="font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    Wajib Validasi Fisik oleh Petugas Lab (Toolman)
                </div>
                Pengembalian ini akan berstatus <strong>Menunggu Validasi Petugas</strong>. Fisik perangkat <strong>wajib diserahkan langsung ke Petugas Lab</strong> untuk pemeriksaan kondisi (Quality Control). Waktu pengembalian, status akhir, dan pemulihan stok hanya akan berlaku setelah Petugas memvalidasi barang di meja lab.
            </div>

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
