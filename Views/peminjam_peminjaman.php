<?php
$activePage = 'peminjaman';
$pageTitle = 'Peminjaman Saya - Lab Komputer';
require_once __DIR__ . '/peminjam_header.php';
?>

<div class="admin-header-row">
    <div>
        <span class="admin-date-label">TRANSAKSI & PENGAJUAN LAB</span>
        <h1 class="admin-title">Peminjaman Saya</h1>
        <p class="admin-subtitle">Pantau status permohonan pinjam alat laboratorium komputer Anda dari tahap pengajuan hingga pengembalian. Anda dapat mengajukan banyak alat sekaligus dalam 1 transaksi.</p>
    </div>
    <button type="button" class="btn-add-instrument" onclick="openModal('modalAjukanPinjam')">
        + Ajukan Peminjaman
    </button>
</div>

<!-- ALERTS -->
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

<!-- NOTIFIKASI PENGINGAT DARI PETUGAS LAB -->
<?php if (!empty($notifikasiBelumDibaca)): ?>
    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 12px;">
        <span style="font-size: 20px;">🔔</span>
        <div>
            <strong style="color: #92400e; font-size: 14px; display: block; margin-bottom: 4px;">Pemberitahuan dari Petugas Lab Komputer:</strong>
            <?php foreach ($notifikasiBelumDibaca as $n): ?>
                <div style="font-size: 13px; color: #78350f; margin-bottom: 4px;">
                    &bull; <?= htmlspecialchars($n['pesan']) ?> <span style="font-size: 11px; color: #b45309;">(<?= date('H:i', strtotime($n['waktu'])) ?> WIB)</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- INVENTORY CARD & TABLE -->
<section class="inventory-section-card">
    <div class="inventory-header">
        <div>
            <h3 class="inventory-title">Daftar Peminjaman Alat Lab</h3>
            <p class="inventory-sub">Total <?= count($daftarPeminjaman) ?> riwayat transaksi peminjaman.</p>
        </div>
        <form method="GET" action="index.php" class="search-box">
            <input type="hidden" name="c" value="peminjam">
            <input type="hidden" name="a" value="peminjaman">
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
                <th>Keperluan / Jenis</th>
                <th>Jml</th>
                <th>Waktu Pinjam</th>
                <th>Rencana Kembali</th>
                <th>Status</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($daftarPeminjaman)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                        Belum ada riwayat peminjaman. Klik "+ Ajukan Peminjaman" atau telusuri <a href="index.php?c=peminjam&a=daftar_alat" style="color: var(--teal-dark); font-weight: 600;">Katalog Alat Lab</a>.
                    </td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($daftarPeminjaman as $p): 
                    $status = strtolower($p['status']);
                    $isReminded = in_array((int)$p['id'], $remindedLoanIds ?? []);
                    $items = $p['items'] ?? [];
                    $isMultiItem = count($items) > 1;
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <div style="font-family: monospace; font-size: 11px; font-weight: 700; color: var(--primary-teal); margin-bottom: 2px;">
                                <?= htmlspecialchars($p['kode_peminjaman'] ?? '-') ?>
                            </div>
                            
                            <?php if ($isMultiItem): ?>
                                <div style="margin-bottom: 4px;">
                                    <span style="display: inline-block; font-size: 11px; font-weight: 700; background: #e0f2fe; color: #0284c7; padding: 2px 7px; border-radius: 4px; margin-bottom: 4px;">
                                        📦 <?= count($items) ?> Jenis Alat dalam 1 Transaksi
                                    </span>
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <?php foreach ($items as $item): ?>
                                            <div style="font-size: 12.5px; color: var(--primary-navy); display: flex; align-items: baseline; gap: 5px;">
                                                <span style="color: var(--primary-teal); font-weight: 700;">&bull;</span>
                                                <div>
                                                    <strong><?= htmlspecialchars($item['nama_alat']) ?></strong>
                                                    <span style="font-size: 11px; color: var(--text-muted);">(<?= (int)$item['jumlah'] ?> unit &bull; <?= htmlspecialchars($item['kode_alat'] ?? '') ?>)</span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <strong style="color: var(--primary-navy);"><?= htmlspecialchars($p['nama_alat'] ?? 'Alat Lab') ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($p['kode_alat'] ?? '') ?> &bull; <?= htmlspecialchars($p['nama_kategori'] ?? '') ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="font-size: 12px; color: var(--text-secondary);"><?= htmlspecialchars($p['jenis_peminjaman'] ?? 'Praktek Lab') ?></strong>
                            <div style="font-size: 11px; color: var(--text-muted); max-width: 250px;"><?= htmlspecialchars($p['keperluan'] ?? '-') ?></div>
                        </td>
                        <td>
                            <strong><?= (int)($p['jumlah'] ?? 1) ?></strong> unit
                            <?php if ($isMultiItem): ?>
                                <div style="font-size: 11px; color: var(--text-muted);">(<?= count($items) ?> macam)</div>
                            <?php endif; ?>
                        </td>
                        <td style="white-space: nowrap;"><?= !empty($p['waktu_pinjam']) ? date('H:i', strtotime($p['waktu_pinjam'])) . ' WIB' : '-' ?></td>
                        <td style="white-space: nowrap;">
                            <strong style="color: var(--teal-dark);"><?= !empty($p['waktu_rencana_kembali']) ? date('H:i', strtotime($p['waktu_rencana_kembali'])) . ' WIB' : '-' ?></strong>
                        </td>
                        <td>
                            <?php if ($status === 'menunggu'): ?>
                                <span class="stock-badge stock-badge-low" style="background:#fef3c7; color:#b45309; padding: 4px 10px; font-size: 12px;">
                                    ⏳ Menunggu Petugas
                                </span>
                            <?php elseif ($status === 'disetujui' || $status === 'dipinjam'): ?>
                                <span class="stock-badge stock-badge-available" style="padding: 4px 10px; font-size: 12px;">
                                    💻 Sedang Dipinjam
                                </span>
                                <?php if ($isReminded): ?>
                                    <div style="font-size: 11px; color: #b45309; font-weight: 700; margin-top: 4px;">🔔 Diminta Segera Kembali</div>
                                <?php endif; ?>
                            <?php elseif ($status === 'menunggu_pengembalian' || $status === 'menunggu_validasi'): ?>
                                <span class="stock-badge" style="background:#fef3c7; color:#b45309; border:1px solid #fde68a; font-weight:700; padding: 4px 10px; font-size: 12px;">
                                    ⏳ Menunggu Validasi Petugas
                                </span>
                                <div style="font-size: 11px; color: #92400e; margin-top: 3px;">Fisik diserahkan ke meja lab</div>
                            <?php elseif ($status === 'dikembalikan'): ?>
                                <span class="stock-badge" style="background:#e0f2fe; color:#0369a1; font-weight:700; padding: 4px 10px; font-size: 12px;">
                                    ✅ Selesai Dikembalikan
                                </span>
                            <?php elseif ($status === 'ditolak'): ?>
                                <span class="stock-badge" style="background:#fee2e2; color:#b91c1c; font-weight:700; padding: 4px 10px; font-size: 12px;">
                                    ❌ Ditolak
                                </span>
                            <?php elseif ($status === 'dibatalkan'): ?>
                                <span class="stock-badge" style="background:#f1f5f9; color:#64748b; font-weight:700; padding: 4px 10px; font-size: 12px;">
                                    Dibatalkan
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="table-actions-cell">
                            <?php if ($status === 'menunggu'): ?>
                                <a href="index.php?c=peminjam&a=batalkan_peminjaman&id=<?= $p['id'] ?>" class="btn-action-delete" onclick="return confirm('Batalkan pengajuan permohonan peminjaman ini?')">
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
                                <span style="font-size: 12px; color: #b45309; font-weight: 700; background: #fffbeb; padding: 4px 10px; border-radius: 6px; border: 1px dashed #f59e0b; display: inline-block;">
                                    🔍 Menunggu QC Petugas
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

<!-- MODAL FORM AJUKAN PEMINJAMAN (MULTI-ALAT 1 TRANSAKSI) -->
<div class="modal-overlay" id="modalAjukanPinjam">
    <div class="modal-content" style="max-width: 680px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Ajukan Peminjaman Alat Lab</h3>
                <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0 0;">Anda dapat meminjam beberapa alat laboratorium sekaligus dalam 1 transaksi pengajuan.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalAjukanPinjam')">&times;</button>
        </div>

        <form method="POST" action="index.php?c=peminjam&a=ajukan_peminjaman" id="formAjukanPinjamMulti" onsubmit="return validateFormPinjamMulti(event)">
            
            <div style="background: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 8px; padding: 12px 14px; margin-bottom: 18px; font-size: 12.5px; color: #0f766e; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 18px;">💡</span>
                <div>
                    <strong>Peminjaman Multi-Alat:</strong> Klik tombol <strong>"+ Tambah Alat Lain"</strong> di bawah untuk memasukkan perangkat lab lainnya ke dalam formulir ini. Semua alat akan diproses dalam 1 kode transaksi peminjaman.
                </div>
            </div>

            <!-- CONTAINER ITEM ALAT MULTI-ROW -->
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <label class="form-label" style="margin: 0; font-size: 14px; font-weight: 700; color: var(--primary-navy);">
                        Daftar Alat yang Dipinjam <span style="color: #dc2626;">*</span>
                    </label>
                    <button type="button" onclick="addAlatRow()" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 12px; font-weight: 600; border-radius: 6px; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; cursor: pointer; transition: background 0.2s;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        + Tambah Alat Lain
                    </button>
                </div>

                <div id="container-alat-rows" style="display: flex; flex-direction: column; gap: 12px;">
                    <!-- Row Pertama (Default) -->
                    <div class="alat-row-item" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; position: relative;">
                        <div style="display: flex; gap: 12px; align-items: flex-start;">
                            <div style="flex: 3;">
                                <label style="font-size: 11.5px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Pilih Perangkat Lab</label>
                                <select name="id_alat[]" class="form-control select-alat-item" required onchange="handleAlatChange(this)">
                                    <option value="">-- Pilih Alat Laboratorium Komputer --</option>
                                    <?php foreach ($daftarAlat as $a): ?>
                                        <?php if ($a['jumlah'] > 0): ?>
                                            <option value="<?= $a['id'] ?>" data-stok="<?= $a['jumlah'] ?>" data-nama="<?= htmlspecialchars($a['nama_alat']) ?>">
                                                [<?= htmlspecialchars($a['kode']) ?>] <?= htmlspecialchars($a['nama_alat']) ?> (Sisa stok: <?= $a['jumlah'] ?>)
                                            </option>
                                        <?php else: ?>
                                            <option value="<?= $a['id'] ?>" disabled>
                                                [<?= htmlspecialchars($a['kode']) ?>] <?= htmlspecialchars($a['nama_alat']) ?> (Stok Habis)
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <div class="stok-hint-text" style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">Pilih alat untuk melihat batas stok tersedia.</div>
                            </div>

                            <div style="flex: 1; min-width: 90px;">
                                <label style="font-size: 11.5px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Jumlah Unit</label>
                                <input type="number" name="jumlah[]" class="form-control input-jumlah-item" min="1" max="1" value="1" required onchange="updateSummaryTotals()" onkeyup="updateSummaryTotals()">
                            </div>

                            <div style="padding-top: 22px;">
                                <button type="button" class="btn-remove-alat-row" onclick="removeAlatRow(this)" title="Hapus alat ini" style="background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 6px; width: 34px; height: 38px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; visibility: hidden;">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Ringkasan Unit -->
                <div id="badge-total-items" style="margin-top: 10px; background: #f1f5f9; border-radius: 6px; padding: 8px 12px; font-size: 12px; color: #334155; display: flex; justify-content: space-between; align-items: center;">
                    <div>Total diajukan: <strong id="lbl-total-jenis">1</strong> jenis alat (<strong id="lbl-total-unit">1</strong> unit)</div>
                    <span style="font-size: 11px; color: #0284c7; font-weight: 600;">1 Transaksi Tunggal</span>
                </div>
            </div>

            <!-- WAKTU PINJAM & KEMBALI -->
            <div class="form-row" style="display: flex; gap: 16px;">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Waktu Mulai Pinjam <span style="color: #dc2626;">*</span></label>
                    <select name="waktu_pinjam" id="waktu_pinjam" class="form-control" required>
                        <option value="06:30" selected>06.30 (Jam Ke-1)</option>
                        <option value="07:10">07.10 (Jam Ke-2)</option>
                        <option value="07:50">07.50 (Jam Ke-3)</option>
                        <option value="08:30">08.30 (Jam Ke-4)</option>
                        <option value="09:25">09.25 (Jam Ke-5)</option>
                        <option value="10:05">10.05 (Jam Ke-6)</option>
                        <option value="10:45">10.45 (Jam Ke-7)</option>
                        <option value="11:25">11.25 (Jam Ke-8)</option>
                        <option value="12:35">12.35 (Jam Ke-9)</option>
                        <option value="13:15">13.15 (Jam Ke-10)</option>
                        <option value="13:55">13.55 (Jam Ke-11)</option>
                        <option value="14:35">14.35</option>
                        <option value="15:15">15.15</option>
                        <option value="16:00">16.00</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Rencana Pengembalian <span style="color: #dc2626;">*</span></label>
                    <select name="waktu_rencana_kembali" id="waktu_rencana_kembali" class="form-control" required>
                        <option value="07:10" selected>07.10 (Jam Ke-2)</option>
                        <option value="07:50">07.50 (Jam Ke-3)</option>
                        <option value="08:30">08.30 (Jam Ke-4)</option>
                        <option value="09:10">09.10 (Istirahat)</option>
                        <option value="10:05">10.05 (Jam Ke-6)</option>
                        <option value="10:45">10.45 (Jam Ke-7)</option>
                        <option value="11:25">11.25 (Jam Ke-8)</option>
                        <option value="12:05">12.05 (Ishoma)</option>
                        <option value="13:15">13.15 (Jam Ke-10)</option>
                        <option value="13:55">13.55 (Jam Ke-11)</option>
                        <option value="14:35">14.35 (Selesai KBM)</option>
                        <option value="15:15">15.15</option>
                        <option value="16:00">16.00</option>
                        <option value="16:30">16.30</option>
                        <option value="17:00">17.00 (Batas Maksimal Sekolah)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Jenis Peminjaman</label>
                <select name="jenis_peminjaman" class="form-control" required>
                    <option value="Kebutuhan untuk Pembelajaran di Kelas">Kebutuhan untuk Pembelajaran di Kelas</option>
                    <option value="Kebutuhan Kegiatan">Kebutuhan Kegiatan</option>
                    <option value="Kebutuhan Rapat">Kebutuhan Rapat</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Keperluan / Keterangan <span style="color: #dc2626;">*</span></label>
                <textarea name="keperluan" class="form-control" rows="3" placeholder="Contoh: Praktikum konfigurasi router & crimping kabel LAN untuk tugas kelas XII RPL" required></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalAjukanPinjam')">Batal</button>
                <button type="submit" class="btn-modal-submit">Kirim Pengajuan (1 Transaksi)</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL KONFIRMASI KEMBALIKAN ALAT -->
<div class="modal-overlay" id="modalKembalikanAlat">
    <div class="modal-content" style="max-width: 520px;">
        <div class="modal-header">
            <h3 class="modal-title">Ajukan Pengembalian Alat Lab</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalKembalikanAlat')">&times;</button>
        </div>
        <form method="POST" action="index.php?c=peminjam&a=kembalikan_alat">
            <input type="hidden" name="id_peminjaman" id="kembali_id_peminjaman">
            <input type="hidden" name="redirect" value="peminjaman">
            
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px; font-size: 12px; color: #92400e; line-height: 1.5;">
                <div style="font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    Wajib Validasi Fisik oleh Petugas Lab (Toolman)
                </div>
                Pengembalian ini akan berstatus <strong>Menunggu Validasi Petugas</strong>. Fisik perangkat <strong>wajib diserahkan langsung ke Petugas Lab</strong> untuk pemeriksaan kondisi (Quality Control). Waktu pengembalian, status akhir, dan pemulihan stok hanya akan berlaku setelah Petugas memvalidasi barang di meja lab.
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 6px;">Perangkat yang Dikembalikan:</div>
                <div id="kembali_container_alat" style="margin-bottom: 6px;">
                    <div style="font-weight: 700; color: var(--navy-primary); font-size: 15px;" id="kembali_nama_alat">-</div>
                </div>
                <div style="font-size: 12px; color: var(--teal-primary); font-weight: 600; margin-top: 4px; border-top: 1px dashed #e2e8f0; padding-top: 6px;">
                    Kode Transaksi: <span id="kembali_kode">-</span> &bull; Total: <span id="kembali_jumlah">1</span> Unit
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
// Data master alat lab tersedia untuk dynamic rows
const DAFTAR_ALAT_DATA = <?= json_encode(array_values(array_filter(array_map(function($a) {
    return [
        'id'        => (int)$a['id'],
        'kode'      => $a['kode'],
        'nama_alat' => $a['nama_alat'],
        'jumlah'    => (int)$a['jumlah'],
        'kategori'  => $a['nama_kategori'] ?? ''
    ];
}, $daftarAlat), function($a) {
    return $a['jumlah'] > 0;
}))) ?>;

function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function handleAlatChange(selectElem) {
    var selectedOption = selectElem.options[selectElem.selectedIndex];
    var stok = parseInt(selectedOption.getAttribute('data-stok') || 0);
    var row = selectElem.closest('.alat-row-item');
    var inputJml = row.querySelector('.input-jumlah-item');
    var hint = row.querySelector('.stok-hint-text');

    if (stok > 0) {
        inputJml.max = stok;
        if (parseInt(inputJml.value) > stok) {
            inputJml.value = stok;
        }
        if (parseInt(inputJml.value) < 1) {
            inputJml.value = 1;
        }
        hint.textContent = 'Maksimal pinjam: ' + stok + ' unit (Sisa stok lab)';
        hint.style.color = '#059669';
    } else {
        inputJml.max = 1;
        hint.textContent = 'Pilih alat untuk melihat batas stok tersedia.';
        hint.style.color = 'var(--text-muted)';
    }

    // Peringatan jika alat yang sama dipilih dua kali di baris berbeda
    checkDuplicateAlat(selectElem);
    updateSummaryTotals();
}

function checkDuplicateAlat(currentSelect) {
    var allSelects = document.querySelectorAll('.select-alat-item');
    var currentVal = currentSelect.value;
    if (!currentVal) return;

    var count = 0;
    allSelects.forEach(function(sel) {
        if (sel.value === currentVal) count++;
    });

    if (count > 1) {
        alert('Perhatian: Anda sudah memilih alat ini di baris lain. Silakan ubah jumlah unit pada baris yang sudah ada, atau pilih alat yang berbeda.');
        currentSelect.value = '';
        var row = currentSelect.closest('.alat-row-item');
        row.querySelector('.stok-hint-text').textContent = 'Alat ini sudah dipilih di baris lain.';
        row.querySelector('.stok-hint-text').style.color = '#dc2626';
    }
}

function addAlatRow() {
    var container = document.getElementById('container-alat-rows');
    var newRow = document.createElement('div');
    newRow.className = 'alat-row-item';
    newRow.style.cssText = 'background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; position: relative;';

    var optionsHtml = '<option value="">-- Pilih Alat Laboratorium Komputer --</option>';
    DAFTAR_ALAT_DATA.forEach(function(a) {
        optionsHtml += '<option value="' + a.id + '" data-stok="' + a.jumlah + '" data-nama="' + escapeHtml(a.nama_alat) + '">[' + escapeHtml(a.kode) + '] ' + escapeHtml(a.nama_alat) + ' (Sisa stok: ' + a.jumlah + ')</option>';
    });

    newRow.innerHTML = `
        <div style="display: flex; gap: 12px; align-items: flex-start;">
            <div style="flex: 3;">
                <label style="font-size: 11.5px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Pilih Perangkat Lab Tambahan</label>
                <select name="id_alat[]" class="form-control select-alat-item" required onchange="handleAlatChange(this)">
                    ${optionsHtml}
                </select>
                <div class="stok-hint-text" style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">Pilih alat untuk melihat batas stok tersedia.</div>
            </div>

            <div style="flex: 1; min-width: 90px;">
                <label style="font-size: 11.5px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Jumlah Unit</label>
                <input type="number" name="jumlah[]" class="form-control input-jumlah-item" min="1" max="1" value="1" required onchange="updateSummaryTotals()" onkeyup="updateSummaryTotals()">
            </div>

            <div style="padding-top: 22px;">
                <button type="button" class="btn-remove-alat-row" onclick="removeAlatRow(this)" title="Hapus alat ini" style="background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 6px; width: 34px; height: 38px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                </button>
            </div>
        </div>
    `;

    container.appendChild(newRow);
    syncRemoveButtons();
    updateSummaryTotals();
}

function removeAlatRow(btn) {
    var rows = document.querySelectorAll('.alat-row-item');
    if (rows.length <= 1) {
        alert('Minimal harus ada 1 alat yang dipilih!');
        return;
    }
    var row = btn.closest('.alat-row-item');
    row.remove();
    syncRemoveButtons();
    updateSummaryTotals();
}

function syncRemoveButtons() {
    var rows = document.querySelectorAll('.alat-row-item');
    rows.forEach(function(r) {
        var btn = r.querySelector('.btn-remove-alat-row');
        if (btn) {
            btn.style.visibility = (rows.length > 1) ? 'visible' : 'hidden';
        }
    });
}

function updateSummaryTotals() {
    var rows = document.querySelectorAll('.alat-row-item');
    var totalUnit = 0;
    var validJenis = 0;

    rows.forEach(function(r) {
        var sel = r.querySelector('.select-alat-item');
        var inp = r.querySelector('.input-jumlah-item');
        if (sel && sel.value) {
            validJenis++;
            totalUnit += parseInt(inp.value || 1);
        }
    });

    document.getElementById('lbl-total-jenis').textContent = validJenis > 0 ? validJenis : rows.length;
    document.getElementById('lbl-total-unit').textContent = totalUnit > 0 ? totalUnit : rows.length;
}

function validateFormPinjamMulti(e) {
    var selects = document.querySelectorAll('.select-alat-item');
    var hasValidAlat = false;
    var seen = {};

    for (var i = 0; i < selects.length; i++) {
        var val = selects[i].value;
        if (!val) {
            alert('Silakan pilih alat pada setiap baris alat yang ditambahkan!');
            selects[i].focus();
            e.preventDefault();
            return false;
        }
        if (seen[val]) {
            alert('Ada alat yang dipilih lebih dari 1 kali! Mohon gabungkan jumlah unit atau pilih alat yang berbeda.');
            selects[i].focus();
            e.preventDefault();
            return false;
        }
        seen[val] = true;
        hasValidAlat = true;

        var row = selects[i].closest('.alat-row-item');
        var inputJml = row.querySelector('.input-jumlah-item');
        var maxStok = parseInt(inputJml.max || 0);
        var jmlVal = parseInt(inputJml.value || 0);

        if (jmlVal < 1) {
            alert('Jumlah unit minimal adalah 1!');
            inputJml.focus();
            e.preventDefault();
            return false;
        }
        if (maxStok > 0 && jmlVal > maxStok) {
            alert('Jumlah unit melebihi stok yang tersedia (' + maxStok + ' unit)!');
            inputJml.focus();
            e.preventDefault();
            return false;
        }
    }

    var waktuPinjam = document.getElementById('waktu_pinjam').value;
    var waktuKembali = document.getElementById('waktu_rencana_kembali').value;
    if (waktuKembali <= waktuPinjam) {
        alert('Waktu rencana pengembalian tidak boleh sama atau mendahului waktu mulai pinjam!');
        document.getElementById('waktu_rencana_kembali').focus();
        e.preventDefault();
        return false;
    }

    return true;
}

function openModalKembalikan(data) {
    document.getElementById('kembali_id_peminjaman').value = data.id;
    document.getElementById('kembali_kode').textContent = data.kode_peminjaman || data.kode_alat;
    document.getElementById('kembali_jumlah').textContent = data.jumlah;
    document.getElementById('kembali_rencana').textContent = (data.waktu_rencana_kembali ? data.waktu_rencana_kembali.substring(0, 5) + ' WIB' : '-');

    var container = document.getElementById('kembali_container_alat');
    if (data.items && data.items.length > 1) {
        var html = '<div style="font-weight: 700; color: var(--navy-primary); font-size: 14px; margin-bottom: 6px;">' + data.items.length + ' Macam Alat dalam Transaksi Ini:</div>';
        html += '<div style="display: flex; flex-direction: column; gap: 4px; padding-left: 6px;">';
        data.items.forEach(function(it) {
            html += '<div style="font-size: 13px; color: #1e293b; display: flex; align-items: center; gap: 6px;">' +
                    '<span style="color: var(--teal-primary); font-weight: 700;">&bull;</span> ' +
                    '<strong>' + escapeHtml(it.nama_alat) + '</strong> (' + it.jumlah + ' unit &bull; ' + escapeHtml(it.kode_alat || '') + ')' +
                    '</div>';
        });
        html += '</div>';
        container.innerHTML = html;
    } else {
        container.innerHTML = '<div style="font-weight: 700; color: var(--navy-primary); font-size: 15px;">' + escapeHtml(data.nama_alat || 'Alat Lab') + '</div>';
    }

    openModal('modalKembalikanAlat');
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString()
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>

<?php require_once __DIR__ . '/peminjam_footer.php'; ?>
