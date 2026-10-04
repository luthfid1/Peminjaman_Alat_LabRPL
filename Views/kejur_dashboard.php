<?php
$activePage = 'dashboard';
$pageTitle = 'Dashboard Eksekutif Monitoring - Ketua Jurusan';
require_once __DIR__ . '/kejur_header.php';

// Kalkulasi persentase kondisi alat untuk Pie Chart
$totalFisik = array_sum($kondisiDist);
$pctBaik = ($totalFisik > 0) ? round(($kondisiDist['Baik'] / $totalFisik) * 100, 1) : 0;
$pctRingan = ($totalFisik > 0) ? round(($kondisiDist['Rusak Ringan'] / $totalFisik) * 100, 1) : 0;
$pctBerat = ($totalFisik > 0) ? round(($kondisiDist['Rusak Berat'] / $totalFisik) * 100, 1) : 0;
?>

<!-- HEADER TITLE -->
<div class="admin-header-row">
    <div>
        <span class="admin-date-label"><?= htmlspecialchars($tglStr) ?></span>
        <h1 class="admin-title">Dashboard Eksekutif Ketua Jurusan</h1>
        <p class="admin-subtitle">Monitoring visual real-time kondisi aset, tren peminjaman alat, counter transaksi, dan audit trail operasional lab.</p>
    </div>
</div>

<!-- =============================================
     1. COUNTER RINGKASAN REAL-TIME
     ============================================= -->
<div class="admin-metrics-grid" style="margin-bottom: 24px;">
    <!-- COUNTER 1: ALAT SEDANG DIPINJAM -->
    <div class="metric-card" style="border-left: 4px solid var(--teal-primary);">
        <div class="metric-top-row">
            <span class="metric-label">Alat Sedang Dipinjam</span>
            <div class="metric-icon-wrap" style="background: rgba(20, 184, 166, 0.12); color: var(--teal-dark);">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                    <line x1="8" y1="21" x2="16" y2="21"></line>
                    <line x1="12" y1="17" x2="12" y2="21"></line>
                </svg>
            </div>
        </div>
        <div class="metric-number" style="color: var(--teal-dark);"><?= $totalAlatSedangDipinjam ?> <span style="font-size: 14px; font-weight: 500; color: var(--text-muted);">Unit</span></div>
        <div class="metric-sub"><?= count($alatAktifDipinjam) ?> transaksi pinjam aktif saat ini</div>
    </div>

    <!-- COUNTER 2: PENGEMBALIAN TERLAMBAT -->
    <div class="metric-card" style="border-left: 4px solid <?= ($totalTerlambat > 0) ? '#ef4444' : '#10b981' ?>;">
        <div class="metric-top-row">
            <span class="metric-label">Pengembalian Terlambat</span>
            <div class="metric-icon-wrap" style="background: <?= ($totalTerlambat > 0) ? 'rgba(239, 68, 68, 0.12)' : 'rgba(16, 185, 129, 0.12)' ?>; color: <?= ($totalTerlambat > 0) ? '#dc2626' : '#059669' ?>;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
        </div>
        <div class="metric-number" style="color: <?= ($totalTerlambat > 0) ? '#dc2626' : '#059669' ?>;"><?= $totalTerlambat ?></div>
        <div class="metric-sub"><?= ($totalTerlambat > 0) ? '⚠ Perlu perhatian khusus laboran' : 'Semua transaksi tepat waktu' ?></div>
    </div>

    <!-- COUNTER 3: KAS DENDA LABORATORIUM -->
    <div class="metric-card" style="border-left: 4px solid #f59e0b;">
        <div class="metric-top-row">
            <span class="metric-label">Kas Sanksi / Denda</span>
            <div class="metric-icon-wrap" style="background: rgba(245, 158, 11, 0.12); color: #d97706;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
        </div>
        <div class="metric-number" style="color: #b45309; font-size: 24px;">Rp<?= number_format((float)($statDenda['total_nominal'] ?? 0), 0, ',', '.') ?></div>
        <div class="metric-sub"><?= (int)($statDenda['total_kasus'] ?? 0) ?> kasus sanksi tercatat</div>
    </div>

    <!-- COUNTER 4: TOTAL INVENTARIS ASET -->
    <div class="metric-card" style="border-left: 4px solid var(--navy-primary);">
        <div class="metric-top-row">
            <span class="metric-label">Total Aset Terdata</span>
            <div class="metric-icon-wrap" style="background: rgba(15, 23, 42, 0.1); color: var(--navy-primary);">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                </svg>
            </div>
        </div>
        <div class="metric-number"><?= $totalAlat ?> <span style="font-size: 14px; font-weight: 500; color: var(--text-muted);">(<?= $totalFisik ?> unit fisik)</span></div>
        <div class="metric-sub"><?= $totalSiswa ?> siswa terdaftar di lab</div>
    </div>
</div>

<!-- =============================================
     GRAFIK & STATISTIK REAL-TIME (CHART VISUAL)
     ============================================= -->
<div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; margin-bottom: 24px;">
    
    <!-- GRAFIK 1: TREN ALAT PALING SERING DIPINJAM -->
    <div class="inventory-section-card" style="margin-bottom: 0;">
        <div class="inventory-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
            <div>
                <h3 class="inventory-title" style="display: flex; align-items: center; gap: 8px;">
                    📊 Grafik Tren Peminjaman Alat Terbanyak
                </h3>
                <p class="inventory-sub">Data acuan evaluasi bagi Kajur untuk merencanakan anggaran pengadaan alat baru.</p>
            </div>
        </div>

        <div style="padding: 16px 0;">
            <?php 
                $maxBorrow = 1;
                foreach ($topAlat as $t) {
                    if ((int)$t['total_pinjam'] > $maxBorrow) $maxBorrow = (int)$t['total_pinjam'];
                }
            ?>
            <?php if (empty($topAlat)): ?>
                <div style="text-align: center; color: var(--text-muted); padding: 40px;">Belum ada data transaksi peminjaman.</div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <?php foreach ($topAlat as $idx => $t): 
                        $pct = ($maxBorrow > 0) ? round(((int)$t['total_pinjam'] / $maxBorrow) * 100) : 0;
                        $pct = max(12, $pct); // Minimal lebar bar visual
                    ?>
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; font-size: 13px;">
                                <div>
                                    <strong style="color: var(--navy-primary);"><?= ($idx + 1) ?>. <?= htmlspecialchars($t['nama_alat']) ?></strong>
                                    <span style="font-size: 11px; color: var(--text-muted); margin-left: 6px;">[<?= htmlspecialchars($t['kode']) ?>]</span>
                                </div>
                                <span style="font-weight: 700; color: var(--teal-dark); font-size: 13px;">
                                    <?= (int)$t['total_pinjam'] ?> Kali Dipinjam
                                </span>
                            </div>
                            <div style="height: 14px; background: #f1f5f9; border-radius: 999px; overflow: hidden; position: relative;">
                                <div style="height: 100%; width: <?= $pct ?>%; background: linear-gradient(90deg, var(--teal-primary), var(--teal-dark)); border-radius: 999px; transition: width 0.6s ease;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- GRAFIK 2: STATUS ASET LAB (PIE CHART PERSENTASE KONDISI) -->
    <div class="inventory-section-card" style="margin-bottom: 0;">
        <div class="inventory-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
            <div>
                <h3 class="inventory-title" style="display: flex; align-items: center; gap: 8px;">
                    🥧 Status & Kondisi Aset Lab
                </h3>
                <p class="inventory-sub">Persentase kelayakan fisik perangkat laboratorium.</p>
            </div>
        </div>

        <div style="padding: 16px 0; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <!-- CSS PIE CHART (CONIC-GRADIENT) -->
            <?php
                // Hitung sudut conic gradient
                $degBaik = ($pctBaik / 100) * 360;
                $degRingan = $degBaik + (($pctRingan / 100) * 360);
            ?>
            <div style="
                width: 150px;
                height: 150px;
                border-radius: 50%;
                background: conic-gradient(
                    #10b981 0deg <?= $degBaik ?>deg,
                    #f59e0b <?= $degBaik ?>deg <?= $degRingan ?>deg,
                    #ef4444 <?= $degRingan ?>deg 360deg
                );
                box-shadow: 0 4px 12px rgba(0,0,0,0.08);
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 18px;
            ">
                <div style="
                    width: 86px;
                    height: 86px;
                    background: white;
                    border-radius: 50%;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
                ">
                    <span style="font-size: 11px; color: var(--text-muted);">Total Fisik</span>
                    <strong style="font-size: 16px; color: var(--navy-primary);"><?= $totalFisik ?></strong>
                </div>
            </div>

            <!-- LEGENDA PERSENTASE -->
            <div style="width: 100%; display: flex; flex-direction: column; gap: 8px; font-size: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 8px; border-radius: 6px; background: #ecfdf5;">
                    <span style="display: flex; align-items: center; gap: 6px; color: #065f46; font-weight: 600;">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                        Kondisi Baik (Siap Pakai)
                    </span>
                    <strong style="color: #065f46;"><?= $kondisiDist['Baik'] ?> unit (<?= $pctBaik ?>%)</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 8px; border-radius: 6px; background: #fffbeb;">
                    <span style="display: flex; align-items: center; gap: 6px; color: #92400e; font-weight: 600;">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
                        Rusak Ringan (Perlu Servis)
                    </span>
                    <strong style="color: #92400e;"><?= $kondisiDist['Rusak Ringan'] ?> unit (<?= $pctRingan ?>%)</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 8px; border-radius: 6px; background: #fef2f2;">
                    <span style="display: flex; align-items: center; gap: 6px; color: #991b1b; font-weight: 600;">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
                        Rusak Berat (Afkir/Ganti)
                    </span>
                    <strong style="color: #991b1b;"><?= $kondisiDist['Rusak Berat'] ?> unit (<?= $pctBerat ?>%)</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =============================================
     SHORTCUT 4 MENU UTAMA KETUA JURUSAN
     ============================================= -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <a href="index.php?c=kejur&a=laporan_peminjaman" style="background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 16px 18px; text-decoration: none; display: flex; align-items: center; gap: 14px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
        <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(14, 165, 233, 0.1); color: #0284c7; display: flex; align-items: center; justify-content: center;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
        </div>
        <div>
            <strong style="color: var(--navy-primary); font-size: 13px; display: block;">Laporan Riwayat Peminjaman</strong>
            <span style="font-size: 11px; color: var(--text-muted);">Filter kelas, tanggal & alat &rarr;</span>
        </div>
    </a>

    <a href="index.php?c=kejur&a=laporan_denda" style="background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 16px 18px; text-decoration: none; display: flex; align-items: center; gap: 14px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
        <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(245, 158, 11, 0.1); color: #d97706; display: flex; align-items: center; justify-content: center;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>
        <div>
            <strong style="color: var(--navy-primary); font-size: 13px; display: block;">Laporan Kas Denda (Poin 3c & 9d)</strong>
            <span style="font-size: 11px; color: var(--text-muted);">Daftar siswa & kas denda &rarr;</span>
        </div>
    </a>

    <a href="index.php?c=kejur&a=log" style="background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 16px 18px; text-decoration: none; display: flex; align-items: center; gap: 14px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
        <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(99, 102, 241, 0.1); color: #4f46e5; display: flex; align-items: center; justify-content: center;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <div>
            <strong style="color: var(--navy-primary); font-size: 13px; display: block;">Audit Trail / Log Aktivitas Global</strong>
            <span style="font-size: 11px; color: var(--text-muted);">Keamanan & kontrol admin &rarr;</span>
        </div>
    </a>

    <a href="index.php?c=kejur&a=inventaris" style="background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 16px 18px; text-decoration: none; display: flex; align-items: center; gap: 14px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
        <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(15, 118, 110, 0.1); color: var(--teal-dark); display: flex; align-items: center; justify-content: center;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                <line x1="8" y1="21" x2="16" y2="21"></line>
            </svg>
        </div>
        <div>
            <strong style="color: var(--navy-primary); font-size: 13px; display: block;">Daftar Inventaris (Read-Only)</strong>
            <span style="font-size: 11px; color: var(--text-muted);">Lihat spesifikasi & stok &rarr;</span>
        </div>
    </a>
</div>

<!-- =============================================
     TABEL DUA KOLOM: TRANSAKSI AKTIF & AUDIT TRAIL TERBARU
     ============================================= -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- TRANSAKSI AKTIF SEDANG DIPINJAM -->
    <section class="inventory-section-card" style="margin-bottom: 0;">
        <div class="inventory-header">
            <div>
                <h3 class="inventory-title">Perangkat Sedang Dipinjam</h3>
                <p class="inventory-sub"><?= count($alatAktifDipinjam) ?> permohonan dalam pemakaian.</p>
            </div>
            <a href="index.php?c=kejur&a=laporan_peminjaman" style="font-size: 12px; font-weight: 600; color: var(--teal-dark); text-decoration: none;">Semua &rarr;</a>
        </div>
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Siswa</th>
                    <th>Alat Dipinjam</th>
                    <th>Rencana Kembali</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($alatAktifDipinjam)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 24px;">
                            Tidak ada alat lab yang sedang dipinjam saat ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach (array_slice($alatAktifDipinjam, 0, 4) as $a): 
                        $isTerlewat = !empty($a['waktu_rencana_kembali']) && (strtotime($a['waktu_rencana_kembali']) < strtotime(date('H:i:s')));
                    ?>
                        <tr>
                            <td>
                                <strong style="color: var(--navy-primary); font-size: 13px;"><?= htmlspecialchars($a['nama_peminjam'] ?? '-') ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($a['kelas'] ?? '-') ?></div>
                            </td>
                            <td class="col-instrument-name">
                                <?= htmlspecialchars($a['nama_alat'] ?? '-') ?>
                                <div style="font-size: 11px; color: var(--text-muted);"><?= (int)($a['jumlah'] ?? 1) ?> unit</div>
                            </td>
                            <td style="white-space: nowrap; font-size: 12px;">
                                <?php if ($isTerlewat): ?>
                                    <span style="color: #dc2626; font-weight: 700;">
                                        <?= date('H:i', strtotime($a['waktu_rencana_kembali'])) ?> WIB
                                        <div style="font-size: 10px; color: #dc2626;">⚠ Terlambat</div>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--teal-dark); font-weight: 600;">
                                        <?= !empty($a['waktu_rencana_kembali']) ? date('H:i', strtotime($a['waktu_rencana_kembali'])) . ' WIB' : '-' ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="stock-badge stock-badge-available" style="padding: 2px 8px; font-size: 11px;">
                                    Sedang Dipinjam
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <!-- REKAM AUDIT TRAIL TERBARU -->
    <section class="inventory-section-card" style="margin-bottom: 0;">
        <div class="inventory-header">
            <div>
                <h3 class="inventory-title">Audit Trail Global Terbaru</h3>
                <p class="inventory-sub">Log aktivitas real-time lintas akun.</p>
            </div>
            <a href="index.php?c=kejur&a=log" style="font-size: 12px; font-weight: 600; color: var(--teal-dark); text-decoration: none;">Buka Log &rarr;</a>
        </div>
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>User & Role</th>
                    <th>Aksi & Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($auditLogsTerbaru)): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 24px;">Belum ada log sistem.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($auditLogsTerbaru as $lg): ?>
                        <tr>
                            <td style="white-space: nowrap; font-size: 11px; color: var(--text-muted);">
                                <?= date('d/m H:i', strtotime($lg['waktu'])) ?>
                            </td>
                            <td>
                                <strong style="color: var(--navy-primary); font-size: 12px;"><?= htmlspecialchars($lg['nama_user'] ?? 'Sistem') ?></strong>
                                <div style="font-size: 10px; color: var(--teal-dark); font-weight: 600;"><?= strtoupper(htmlspecialchars($lg['role'] ?? '-')) ?></div>
                            </td>
                            <td>
                                <strong style="font-size: 12px; color: #1e293b;"><?= htmlspecialchars($lg['aktivitas']) ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted); max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= htmlspecialchars($lg['deskripsi'] ?? '-') ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

<?php require_once __DIR__ . '/kejur_footer.php'; ?>
