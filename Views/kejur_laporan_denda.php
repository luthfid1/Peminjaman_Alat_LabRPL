<?php
$activePage = 'laporan_denda';
$pageTitle = 'Laporan Kas Denda & Sanksi - Ketua Jurusan';
require_once __DIR__ . '/kejur_header.php';

// Fitur Export ke Excel (CSV)
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Laporan_Kas_Denda_Lab_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['No', 'Kode Pinjam', 'Nama Siswa', 'NIS', 'Kelas', 'Alat Lab', 'Jumlah', 'Waktu Pinjam', 'Waktu Kembali', 'Kondisi Fisik', 'Nominal Denda (Rp)', 'Status Bayar', 'Petugas Penerima']);
    $no = 1;
    foreach ($daftarDenda as $d) {
        fputcsv($output, [
            $no++,
            $d['kode_peminjaman'] ?? '-',
            $d['nama_peminjam'] ?? '-',
            $d['nis'] ?? '-',
            $d['kelas'] ?? '-',
            $d['nama_alat'] ?? '-',
            $d['jumlah'] ?? 1,
            $d['waktu_pinjam'] ?? '-',
            $d['waktu_kembali'] ?? '-',
            $d['kondisi_kembali'] ?? 'Baik',
            $d['denda'] ?? 0,
            $d['status_bayar'] ?? 'Lunas',
            $d['nama_petugas'] ?? '-'
        ]);
    }
    fclose($output);
    exit;
}
?>

<div class="print-kop">
    <h2 style="margin: 0; font-size: 16px; text-transform: uppercase;">LAPORAN KAS DENDA & SANKSI LABORATORIUM KOMPUTER</h2>
    <p style="margin: 4px 0 0 0; font-size: 12px;">SMK NEGERI - JURUSAN REKAYASA PERANGKAT LUNAK (SOAL UKK POIN 3c & 9d)</p>
    <span style="font-size: 10px; color: #555;">Dicetak pada: <?= date('d F Y, H:i') ?> WIB | Oleh: <?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Ketua Jurusan') ?></span>
</div>

<div class="admin-header-row no-print">
    <div>
        <span class="admin-date-label">FINANSIAL & SANKSI (POIN 3c & 9d)</span>
        <h1 class="admin-title">Laporan Kas Denda Laboratorium</h1>
        <p class="admin-subtitle">Rekapitulasi sanksi denda siswa akibat keterlambatan atau kerusakan alat sebagai dokumen pertanggungjawaban ke Kepala Sekolah.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="index.php?c=kejur&a=laporan_denda&export=excel" class="btn-add-instrument" style="background: #10b981; color: white;">
            📥 Export Excel (CSV)
        </a>
        <button type="button" class="btn-add-instrument" onclick="window.print()">
            🖨 Cetak PDF / Print
        </button>
    </div>
</div>

<!-- METRIC KAS DENDA -->
<div class="admin-metrics-grid no-print" style="margin-bottom: 24px;">
    <div class="metric-card" style="border-left: 4px solid #f59e0b;">
        <div class="metric-top-row">
            <span class="metric-label">Total Penerimaan Kas Denda</span>
            <div class="metric-icon-wrap" style="background: rgba(245, 158, 11, 0.12); color: #d97706;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
        </div>
        <div class="metric-number" style="color: #b45309;">Rp<?= number_format((float)($statDenda['total_nominal'] ?? 0), 0, ',', '.') ?></div>
        <div class="metric-sub">Saldo kas tertagih dari sanksi operasional</div>
    </div>

    <div class="metric-card" style="border-left: 4px solid #0284c7;">
        <div class="metric-top-row">
            <span class="metric-label">Total Pelanggaran / Sanksi</span>
            <div class="metric-icon-wrap" style="background: rgba(14, 165, 233, 0.12); color: #0284c7;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
            </div>
        </div>
        <div class="metric-number" style="color: #0284c7;"><?= (int)($statDenda['total_kasus'] ?? 0) ?></div>
        <div class="metric-sub">Siswa terkena denda keterlambatan/fisik</div>
    </div>

    <div class="metric-card" style="border-left: 4px solid #10b981;">
        <div class="metric-top-row">
            <span class="metric-label">Status Kas Denda</span>
            <div class="metric-icon-wrap" style="background: rgba(16, 185, 129, 0.12); color: #059669;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
        </div>
        <div class="metric-number" style="color: #059669;">100% Lunas</div>
        <div class="metric-sub">Terverifikasi langsung oleh petugas lab</div>
    </div>
</div>

<!-- INVENTORY CARD & TABLE -->
<section class="inventory-section-card">
    <div class="inventory-header no-print">
        <div>
            <h3 class="inventory-title">Daftar Transaksi Denda Siswa</h3>
            <p class="inventory-sub">Total <?= count($daftarDenda) ?> berkas pengenaan denda terdata.</p>
        </div>
        <form method="GET" action="index.php" class="search-box">
            <input type="hidden" name="c" value="kejur">
            <input type="hidden" name="a" value="laporan_denda">
            <span class="search-icon">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </span>
            <input type="text" name="search" placeholder="Cari siswa, NIS, kode..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </form>
    </div>

    <table class="inventory-table">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Kode Pinjam</th>
                <th>Siswa Pelanggar</th>
                <th>Kelas / NIS</th>
                <th>Alat Lab Terkait</th>
                <th>Waktu Pinjam</th>
                <th>Waktu Kembali</th>
                <th>Kondisi Fisik</th>
                <th>Nominal Denda</th>
                <th>Status Bayar</th>
                <th>Petugas Penerima</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($daftarDenda)): ?>
                <tr>
                    <td colspan="11" style="text-align: center; color: var(--text-muted); padding: 30px;">
                        Tidak ada riwayat pengenaan denda yang ditemukan.
                    </td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($daftarDenda as $d): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <span style="font-family: monospace; font-size: 11px; font-weight: 700; color: var(--primary-teal);"><?= htmlspecialchars($d['kode_peminjaman'] ?? '-') ?></span>
                        </td>
                        <td>
                            <strong style="color: var(--primary-navy);"><?= htmlspecialchars($d['nama_peminjam'] ?? '-') ?></strong>
                            <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($d['no_telp'] ?? '-') ?></div>
                        </td>
                        <td><?= htmlspecialchars($d['kelas'] ?? '-') ?> <span style="font-size: 11px; color: var(--text-muted);">(<?= htmlspecialchars($d['nis'] ?? '-') ?>)</span></td>
                        <td class="col-instrument-name">
                            <?= htmlspecialchars($d['nama_alat'] ?? '-') ?>
                            <div style="font-size: 11px; color: var(--text-muted);"><?= (int)($d['jumlah'] ?? 1) ?> unit &bull; <?= htmlspecialchars($d['kode_alat'] ?? '') ?></div>
                        </td>
                        <td style="white-space: nowrap;"><?= !empty($d['waktu_pinjam']) ? date('H:i', strtotime($d['waktu_pinjam'])) . ' WIB' : '-' ?></td>
                        <td style="white-space: nowrap; font-weight: 600; color: var(--teal-dark);"><?= !empty($d['waktu_kembali']) ? date('H:i', strtotime($d['waktu_kembali'])) . ' WIB' : '-' ?></td>
                        <td>
                            <span style="padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #fee2e2; color: #b91c1c;">
                                <?= htmlspecialchars($d['kondisi_kembali'] ?? 'Baik') ?>
                            </span>
                        </td>
                        <td>
                            <strong style="color: #dc2626; font-size: 13px;">Rp<?= number_format((float)$d['denda'], 0, ',', '.') ?></strong>
                        </td>
                        <td>
                            <span class="stock-badge" style="background: #dcfce7; color: #15803d; font-weight: 700; padding: 2px 8px; font-size: 11px;">
                                ✓ <?= htmlspecialchars($d['status_bayar'] ?? 'Lunas') ?>
                            </span>
                        </td>
                        <td style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($d['nama_petugas'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php require_once __DIR__ . '/kejur_footer.php'; ?>
