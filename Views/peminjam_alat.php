<?php
$activePage = 'daftar_alat';
$pageTitle = 'Katalog Alat Lab Komputer - Ruang Siswa';
require_once __DIR__ . '/peminjam_header.php';
?>

<!-- HEADER ROW -->
<div class="admin-header-row">
    <div>
        <span class="admin-date-label">KATALOG LABORATORIUM KOMPUTER</span>
        <h1 class="admin-title">Katalog Alat Lab</h1>
        <p class="admin-subtitle">Pilih perangkat jaringan, workstation PC, atau peralatan komputer untuk praktikum sekolah dan ajukan peminjaman secara langsung dalam 1 formulir transaksi.</p>
    </div>
    <div style="display: flex; gap: 12px;">
        <button type="button" class="btn-add-instrument" onclick="openMultiPinjamModal()">
            + Ajukan Peminjaman
        </button>
        <a href="index.php?c=peminjam&a=peminjaman" class="btn-action-edit" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; font-size: 13px; font-weight: 600; text-decoration: none; border-radius: 8px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
            Peminjaman Saya
        </a>
    </div>
</div>

<!-- SEARCH & CATEGORY FILTER -->
<div style="background: white; border: 1px solid var(--border-color); border-radius: var(--radius-xl); padding: 18px 24px; margin-bottom: 28px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
    <!-- SEARCH FORM -->
    <form method="GET" action="index.php" class="search-box" style="margin: 0; min-width: 280px; flex: 1; max-width: 450px;">
        <input type="hidden" name="c" value="peminjam">
        <input type="hidden" name="a" value="daftar_alat">
        <?php if (!empty($_GET['kategori'])): ?>
            <input type="hidden" name="kategori" value="<?= (int)$_GET['kategori'] ?>">
        <?php endif; ?>
        <span class="search-icon">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </span>
        <input type="text" name="search" placeholder="Cari nama alat lab atau kode..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    </form>

    <!-- CATEGORY PILLS -->
    <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
        <a href="index.php?c=peminjam&a=daftar_alat<?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" 
           style="padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; text-decoration: none; <?= empty($_GET['kategori']) ? 'background: var(--teal-primary); color: white;' : 'background: #f1f5f9; color: #475569;' ?>">
            Semua (<?= count($daftarAlat) ?>)
        </a>
        <?php foreach ($daftarKategori as $kat): ?>
            <?php $isActive = (isset($_GET['kategori']) && (int)$_GET['kategori'] === (int)$kat['id']); ?>
            <a href="index.php?c=peminjam&a=daftar_alat&kategori=<?= $kat['id'] ?><?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" 
               style="padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; text-decoration: none; <?= $isActive ? 'background: var(--teal-primary); color: white;' : 'background: #f1f5f9; color: #475569;' ?>">
                <?= htmlspecialchars($kat['nama_kategori']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- INSTRUMENTS GRID -->
<?php if (empty($daftarAlat)): ?>
    <div style="background: white; border: 1px solid var(--border-color); border-radius: var(--radius-xl); padding: 60px 20px; text-align: center;">
        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin: 0 auto 12px auto; color: #94a3b8;">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Tidak Ada Alat Lab Ditemukan</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin: 0 0 16px 0;">Coba gunakan kata kunci pencarian lain atau pilih kategori yang berbeda.</p>
        <a href="index.php?c=peminjam&a=daftar_alat" class="btn-action-edit" style="display: inline-block; text-decoration: none;">Reset Filter</a>
    </div>
<?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; margin-bottom: 40px;">
        <?php foreach ($daftarAlat as $alat): 
            $stok = (int)($alat['jumlah'] ?? 0);
            $tersedia = ($stok > 0);
        ?>
            <div style="background: white; border: 1px solid var(--border-color); border-radius: var(--radius-xl); overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 4px 16px -2px rgba(15, 42, 63, 0.03);">
                <!-- HEADER CARD / ICON -->
                <div style="height: 110px; background: linear-gradient(135deg, rgba(20, 184, 166, 0.08), rgba(15, 118, 110, 0.03)); display: flex; align-items: center; justify-content: center; position: relative; border-bottom: 1px solid #f1f5f9;">
                    <div style="width: 52px; height: 52px; border-radius: 50%; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: center; color: var(--teal-dark);">
                        <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </div>
                    <span style="position: absolute; top: 12px; right: 12px; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; <?= $tersedia ? 'background: #dcfce7; color: #15803d;' : 'background: #fee2e2; color: #b91c1c;' ?>">
                        <?= $tersedia ? 'Tersedia ' . $stok . ' Unit' : 'Stok Habis' ?>
                    </span>
                    <span style="position: absolute; top: 12px; left: 12px; font-family: monospace; font-size: 11px; font-weight: 700; background: #e2e8f0; color: #334155; padding: 2px 6px; border-radius: 4px;">
                        <?= htmlspecialchars($alat['kode'] ?? '-') ?>
                    </span>
                </div>

                <!-- BODY CARD -->
                <div style="padding: 20px; display: flex; flex-direction: column; flex-grow: 1;">
                    <span style="font-size: 11px; font-weight: 700; color: var(--teal-dark); text-transform: uppercase; letter-spacing: 0.5px;">
                        <?= htmlspecialchars($alat['nama_kategori'] ?? 'Umum') ?>
                    </span>
                    <h3 style="margin: 6px 0 8px 0; font-size: 16px; font-weight: 700; color: #0f172a; line-height: 1.4;">
                        <?= htmlspecialchars($alat['nama_alat']) ?>
                    </h3>
                    <p style="font-size: 12px; color: var(--text-muted); line-height: 1.5; margin: 0 0 16px 0; flex-grow: 1;">
                        <?= htmlspecialchars($alat['deskripsi'] ?? 'Perangkat laboratorium komputer sekolah.') ?>
                    </p>

                    <div style="background: #f8fafc; border-radius: 8px; padding: 10px 12px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
                        <span style="color: var(--text-muted);">Kondisi:</span>
                        <strong style="color: #0369a1;"><?= htmlspecialchars($alat['kondisi'] ?? 'Baik') ?></strong>
                    </div>

                    <?php if ($tersedia): ?>
                        <button type="button" class="btn-add-instrument" style="width: 100%; text-align: center; justify-content: center; padding: 10px;" 
                                onclick='openPinjamModal(<?= json_encode($alat) ?>)'>
                            Pinjam Alat Lab
                        </button>
                    <?php else: ?>
                        <button type="button" disabled style="width: 100%; padding: 10px; background: #e2e8f0; color: #94a3b8; border: none; border-radius: 8px; font-weight: 600; cursor: not-allowed;">
                            Tidak Tersedia
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- MODAL FORM PINJAM ALAT (MULTI-ALAT 1 TRANSAKSI) -->
<div class="modal-overlay" id="modalPinjamAlat">
    <div class="modal-content" style="max-width: 680px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Formulir Peminjaman Alat Lab</h3>
                <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0 0 0;">Anda dapat meminjam beberapa alat sekaligus dalam 1 formulir transaksi pengajuan.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalPinjamAlat')">&times;</button>
        </div>
        <form method="POST" action="index.php?c=peminjam&a=ajukan_peminjaman" onsubmit="return validateFormPinjamMulti(event)">
            
            <!-- CONTAINER ITEM ALAT -->
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <label class="form-label" style="margin: 0; font-size: 14px; font-weight: 700; color: var(--primary-navy);">
                        Daftar Alat yang Dipinjam <span style="color: #dc2626;">*</span>
                    </label>
                    <button type="button" onclick="addAlatRow()" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 12px; font-weight: 600; border-radius: 6px; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; cursor: pointer;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        + Tambah Alat Lain
                    </button>
                </div>

                <div id="container-alat-rows" style="display: flex; flex-direction: column; gap: 12px;">
                    <!-- Row Pertama -->
                    <div class="alat-row-item" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; position: relative;">
                        <div style="display: flex; gap: 12px; align-items: flex-start;">
                            <div style="flex: 3;">
                                <label style="font-size: 11.5px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Pilih Perangkat Lab</label>
                                <select name="id_alat[]" id="first_select_alat" class="form-control select-alat-item" required onchange="handleAlatChange(this)">
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

                <!-- Ringkasan Item -->
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
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalPinjamAlat')">Batal</button>
                <button type="submit" class="btn-modal-submit">Kirim Pengajuan (1 Transaksi)</button>
            </div>
        </form>
    </div>
</div>

<script>
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

function openMultiPinjamModal() {
    resetToSingleRow();
    openModal('modalPinjamAlat');
}

function openPinjamModal(alat) {
    resetToSingleRow();
    var firstSelect = document.getElementById('first_select_alat');
    if (firstSelect) {
        firstSelect.value = alat.id;
        handleAlatChange(firstSelect);
    }
    openModal('modalPinjamAlat');
}

function resetToSingleRow() {
    var container = document.getElementById('container-alat-rows');
    var rows = container.querySelectorAll('.alat-row-item');
    for (var i = 1; i < rows.length; i++) {
        rows[i].remove();
    }
    var firstRow = rows[0];
    if (firstRow) {
        var sel = firstRow.querySelector('.select-alat-item');
        var inp = firstRow.querySelector('.input-jumlah-item');
        var hint = firstRow.querySelector('.stok-hint-text');
        sel.value = '';
        inp.value = 1;
        inp.max = 1;
        hint.textContent = 'Pilih alat untuk melihat batas stok tersedia.';
        hint.style.color = 'var(--text-muted)';
    }
    syncRemoveButtons();
    updateSummaryTotals();
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
