<?php

require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/alat.php';
require_once __DIR__ . '/../models/kategori.php';
require_once __DIR__ . '/../models/peminjaman.php';
require_once __DIR__ . '/../models/pengembalian.php';
require_once __DIR__ . '/../models/peminjam.php';
require_once __DIR__ . '/../models/notifikasi.php';

class PeminjamController {
    private $userModel;
    private $alatModel;
    private $kategoriModel;
    private $peminjamanModel;
    private $pengembalianModel;
    private $peminjamProfileModel;
    private $notifikasiModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        $role = strtolower(trim($_SESSION['user']['role'] ?? ''));
        if ($role !== 'peminjam') {
            if ($role === 'pengelola' || $role === 'admin') {
                header('Location: index.php?c=admin&a=dashboard');
                exit;
            } elseif ($role === 'petugas') {
                header('Location: index.php?c=admin&a=peminjaman');
                exit;
            } else {
                header('Location: index.php');
                exit;
            }
        }

        $this->userModel            = new User();
        $this->alatModel            = new Alat();
        $this->kategoriModel        = new Kategori();
        $this->peminjamanModel      = new Peminjaman();
        $this->pengembalianModel    = new Pengembalian();
        $this->peminjamProfileModel = new Peminjam();
        $this->notifikasiModel      = new Notifikasi();
    }

    private function getUserId() {
        return (int)($_SESSION['user']['id_user'] ?? $_SESSION['user']['id_users'] ?? 0);
    }

    private function getPeminjamProfile() {
        $userId = $this->getUserId();
        $nama   = $_SESSION['user']['nama'] ?? $_SESSION['user']['nama_lengkap'] ?? 'Siswa';

        $profile = $this->peminjamProfileModel->getPeminjamByUserId($userId);
        if (!$profile) {
            $profile = $this->peminjamProfileModel->getPeminjamByNama($nama);
        }

        // Jika siswa belum ada di tabel peminjam (misal akun demo bawaan), buatkan profil otomatis
        if (!$profile) {
            $newId = $this->peminjamProfileModel->createPeminjam(
                $nama,
                '1023' . rand(1000, 9999),
                'XII RPL 1',
                'Rekayasa Perangkat Lunak',
                '08123456789',
                '',
                $userId
            );
            $profile = $this->peminjamProfileModel->getPeminjamById($newId);
        }

        return $profile;
    }

    // ==========================================
    // 1. DASHBOARD SISWA
    // ==========================================
    public function dashboard() {
        $profile = $this->getPeminjamProfile();
        $idPeminjam = (int)$profile['id'];

        $totalMenunggu      = $this->peminjamanModel->countPeminjamPeminjamanByStatus($idPeminjam, 'menunggu');
        $totalDipinjam      = $this->peminjamanModel->countPeminjamPeminjamanByStatus($idPeminjam, ['disetujui', 'dipinjam']);
        $totalMenungguVerif = $this->peminjamanModel->countPeminjamPeminjamanByStatus($idPeminjam, 'menunggu_pengembalian');
        $totalSelesai       = $this->peminjamanModel->countPeminjamPeminjamanByStatus($idPeminjam, 'dikembalikan');
        $totalDenda         = $this->pengembalianModel->getTotalDendaByPeminjamId($idPeminjam);

        $daftarPinjamTerbaru = $this->peminjamanModel->getPeminjamanByPeminjamId($idPeminjam);
        $alatTersedia = $this->alatModel->getAllAlat();

        $tglStr = date('l, d F Y');

        require_once __DIR__ . '/../views/peminjam_dashboard.php';
    }

    // ==========================================
    // 2. KATALOG ALAT LAB KOMPUTER
    // ==========================================
    public function daftar_alat() {
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        $idKategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

        $daftarAlat = $this->alatModel->getAllAlat($keyword);
        if ($idKategori > 0) {
            $daftarAlat = array_values(array_filter($daftarAlat, function($a) use ($idKategori) {
                return (int)$a['id_kategori'] === $idKategori;
            }));
        }
        $daftarKategori = $this->kategoriModel->getAllKategori();
        $profile = $this->getPeminjamProfile();

        require_once __DIR__ . '/../views/peminjam_alat.php';
    }

    // ==========================================
    // 3. DAFTAR PEMINJAMAN SAYA
    // ==========================================
    public function peminjaman() {
        $profile = $this->getPeminjamProfile();
        $idPeminjam = (int)$profile['id'];
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        $statusFilter = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : '';

        $message = $_SESSION['flash_message'] ?? '';
        $error = $_SESSION['flash_error'] ?? '';
        unset($_SESSION['flash_message'], $_SESSION['flash_error']);

        if (isset($_GET['status'])) {
            if ($_GET['status'] === 'requested' && empty($message)) $message = 'Pengajuan peminjaman alat berhasil diajukan dalam 1 transaksi! Menunggu persetujuan petugas lab.';
            if ($_GET['status'] === 'cancelled' && empty($message)) $message = 'Permohonan peminjaman berhasil dibatalkan.';
            if ($_GET['status'] === 'return_requested' && empty($message)) $message = 'Permohonan pengembalian berhasil dikirim! Silakan bawa & serahkan fisik alat ke meja Petugas Lab untuk dicek kondisinya.';
            if ($_GET['status'] === 'returned' && empty($message)) $message = 'Alat laboratorium telah diverifikasi & selesai dikembalikan! Terima kasih telah menjaga fasilitas lab dengan baik.';
            if ($_GET['status'] === 'stok_kurang' && empty($error)) $error = 'Stok alat yang diminta tidak mencukupi atau sedang kosong!';
            if ($_GET['status'] === 'waktu_invalid' && empty($error)) $error = 'Waktu rencana kembali tidak boleh sama atau mendahului waktu pinjam!';
            if ($_GET['status'] === 'error' && empty($error)) $error = 'Terjadi kesalahan sistem saat memproses transaksi.';
        }

        $daftarPeminjaman = $this->peminjamanModel->getPeminjamanByPeminjamId($idPeminjam, $keyword, $statusFilter);
        $daftarAlat = $this->alatModel->getAllAlat();
        $notifikasiBelumDibaca = $this->notifikasiModel->getNotifikasiByPeminjam($idPeminjam, true);
        $remindedLoanIds = $this->notifikasiModel->getActiveReminderLoanIds($idPeminjam);

        require_once __DIR__ . '/../views/peminjam_peminjaman.php';
    }

    // ==========================================
    // 4. PROSES AJUKAN PEMINJAMAN (MULTI-ALAT 1 TRANSAKSI)
    // ==========================================
    public function ajukan_peminjaman() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $profile               = $this->getPeminjamProfile();
            $idPeminjam            = (int)$profile['id'];
            $waktu_pinjam          = trim($_POST['waktu_pinjam'] ?? date('H:i'));
            $waktu_rencana_kembali = trim($_POST['waktu_rencana_kembali'] ?? date('H:i', strtotime('+2 hours')));
            $jenis_peminjaman      = trim($_POST['jenis_peminjaman'] ?? 'Kebutuhan untuk Pembelajaran di Kelas');
            $keperluan             = trim($_POST['keperluan'] ?? '');

            // Mengumpulkan seluruh item alat yang diajukan dalam 1 transaksi
            $rawItems = [];
            if (isset($_POST['items']) && is_array($_POST['items'])) {
                foreach ($_POST['items'] as $it) {
                    $idA = (int)($it['id_alat'] ?? 0);
                    $jml = max(1, (int)($it['jumlah'] ?? 1));
                    if ($idA > 0) {
                        $rawItems[] = ['id_alat' => $idA, 'jumlah' => $jml];
                    }
                }
            } elseif (isset($_POST['id_alat'])) {
                if (is_array($_POST['id_alat'])) {
                    $idAlatList = $_POST['id_alat'];
                    $jumlahList = is_array($_POST['jumlah'] ?? null) ? $_POST['jumlah'] : [];
                    foreach ($idAlatList as $idx => $idA) {
                        $idA = (int)$idA;
                        $jml = isset($jumlahList[$idx]) ? max(1, (int)$jumlahList[$idx]) : 1;
                        if ($idA > 0) {
                            $rawItems[] = ['id_alat' => $idA, 'jumlah' => $jml];
                        }
                    }
                } else {
                    $idA = (int)$_POST['id_alat'];
                    $jml = max(1, (int)($_POST['jumlah'] ?? 1));
                    if ($idA > 0) {
                        $rawItems[] = ['id_alat' => $idA, 'jumlah' => $jml];
                    }
                }
            }

            // Gabungkan jika siswa memilih alat yang sama di baris berbeda
            $consolidated = [];
            foreach ($rawItems as $it) {
                $idA = $it['id_alat'];
                if (isset($consolidated[$idA])) {
                    $consolidated[$idA]['jumlah'] += $it['jumlah'];
                } else {
                    $consolidated[$idA] = $it;
                }
            }
            $items = array_values($consolidated);

            if (empty($items)) {
                $_SESSION['flash_error'] = 'Silakan pilih minimal 1 alat laboratorium yang ingin dipinjam!';
                header('Location: index.php?c=peminjam&a=peminjaman');
                exit;
            }

            // Validasi keberadaan alat & kecukupan stok untuk setiap alat
            $summaryNames = [];
            foreach ($items as $it) {
                $alat = $this->alatModel->getAlatById($it['id_alat']);
                if (!$alat) {
                    $_SESSION['flash_error'] = 'Salah satu alat yang dipilih tidak valid atau tidak ditemukan di laboratorium!';
                    header('Location: index.php?c=peminjam&a=peminjaman');
                    exit;
                }
                if ($alat['jumlah'] < $it['jumlah']) {
                    $_SESSION['flash_error'] = "Stok alat '{$alat['nama_alat']}' tidak mencukupi (Tersedia: {$alat['jumlah']} unit, diminta: {$it['jumlah']} unit)!";
                    header('Location: index.php?c=peminjam&a=peminjaman&status=stok_kurang');
                    exit;
                }
                $summaryNames[] = "{$alat['nama_alat']} ({$it['jumlah']} unit)";
            }

            // Format waktu ke format TIME MySQL: HH:MM:SS
            if (strlen($waktu_pinjam) === 5) $waktu_pinjam .= ':00';
            if (strlen($waktu_rencana_kembali) === 5) $waktu_rencana_kembali .= ':00';

            if ($waktu_rencana_kembali <= $waktu_pinjam) {
                header('Location: index.php?c=peminjam&a=peminjaman&status=waktu_invalid');
                exit;
            }

            try {
                // Simpan transaksi peminjaman (1 kode transaksi untuk seluruh alat)
                $newId = $this->peminjamanModel->createPeminjaman(
                    $idPeminjam,
                    null, // Belum disetujui petugas (id_user petugas null)
                    $waktu_pinjam,
                    $waktu_rencana_kembali,
                    $jenis_peminjaman,
                    $keperluan,
                    $items[0]['id_alat'],
                    $items[0]['jumlah'],
                    'menunggu',
                    $items
                );

                $textSummary = implode(', ', $summaryNames);
                $this->userModel->recordLog(
                    $this->getUserId(),
                    'Pengajuan Peminjaman Alat',
                    "Siswa {$profile['nama']} mengajukan peminjaman " . count($items) . " jenis alat dalam 1 transaksi: {$textSummary}"
                );

                $_SESSION['flash_message'] = "Pengajuan peminjaman untuk " . count($items) . " jenis alat (" . array_sum(array_column($items, 'jumlah')) . " unit) berhasil diajukan dalam 1 transaksi! Menunggu verifikasi petugas lab.";
                header('Location: index.php?c=peminjam&a=peminjaman&status=requested');
                exit;
            } catch (Exception $e) {
                $_SESSION['flash_error'] = 'Terjadi kesalahan sistem saat memproses transaksi: ' . $e->getMessage();
                header('Location: index.php?c=peminjam&a=peminjaman&status=error');
                exit;
            }
        }
        header('Location: index.php?c=peminjam&a=peminjaman');
        exit;
    }

    // ==========================================
    // 5. BATALKAN PEMINJAMAN
    // ==========================================
    public function batalkan_peminjaman() {
        $id = (int)($_GET['id'] ?? 0);
        $profile = $this->getPeminjamProfile();
        $idPeminjam = (int)$profile['id'];

        if ($id > 0) {
            try {
                $pmj = $this->peminjamanModel->getPeminjamanById($id);
                if ($pmj && (int)$pmj['id_peminjam'] === $idPeminjam && $pmj['status'] === 'menunggu') {
                    $this->peminjamanModel->batalkanPeminjamanByPeminjam($id, $idPeminjam);
                    $this->userModel->recordLog(
                        $this->getUserId(),
                        'Batalkan Peminjaman',
                        "Siswa membatalkan permohonan #{$pmj['kode_peminjaman']}"
                    );
                    header('Location: index.php?c=peminjam&a=peminjaman&status=cancelled');
                    exit;
                }
            } catch (Exception $e) {
                header('Location: index.php?c=peminjam&a=peminjaman&status=error');
                exit;
            }
        }
        header('Location: index.php?c=peminjam&a=peminjaman');
        exit;
    }

    // ==========================================
    // 6. AJUKAN PENGEMBALIAN ALAT LAB (MENUNGGU VERIFIKASI PETUGAS)
    // ==========================================
    public function kembalikan_alat() {
        $id = (int)($_POST['id_peminjaman'] ?? $_GET['id'] ?? 0);
        $catatan_siswa = trim($_POST['catatan_siswa'] ?? $_POST['kondisi_kembali'] ?? '');
        $profile = $this->getPeminjamProfile();
        $idPeminjam = (int)$profile['id'];

        if ($id > 0) {
            try {
                $pmj = $this->peminjamanModel->getPeminjamanById($id);
                // Pastikan peminjaman milik peminjam yang sedang login dan statusnya aktif (disetujui / dipinjam)
                if ($pmj && ((int)$pmj['id_peminjam'] === $idPeminjam || (!empty($pmj['id_user']) && (int)$pmj['id_user'] === $this->getUserId())) && in_array(strtolower($pmj['status']), ['dipinjam', 'disetujui'])) {
                    // Update status peminjaman menjadi 'menunggu_validasi' (TIDAK langsung selesai dan stok TIDAK bertambah sebelum diverifikasi Petugas)
                    $this->peminjamanModel->updateStatus($id, 'menunggu_validasi');

                    // Catat notifikasi konfirmasi pengajuan pengembalian
                    $this->notifikasiModel->createNotifikasi(
                        $idPeminjam,
                        $id,
                        'Pengembalian Diajukan (Menunggu Validasi)',
                        "Permohonan pengembalian alat {$pmj['nama_alat']} (Kode: {$pmj['kode_peminjaman']}) berhasil diajukan. Status: Menunggu Validasi. Silakan serahkan fisik alat ke meja Petugas Lab."
                    );

                    $this->userModel->recordLog(
                        $this->getUserId(),
                        'Pengajuan Pengembalian Alat',
                        "Siswa {$profile['nama']} mengajukan pengembalian alat #{$pmj['kode_peminjaman']} ({$pmj['nama_alat']}) - Status diubah menjadi Menunggu Validasi Petugas"
                    );

                    $redirect = isset($_POST['redirect']) && $_POST['redirect'] === 'pengembalian' ? 'pengembalian' : 'peminjaman';
                    header("Location: index.php?c=peminjam&a={$redirect}&status=return_requested");
                    exit;
                }
            } catch (Exception $e) {
                $redirect = isset($_POST['redirect']) && $_POST['redirect'] === 'pengembalian' ? 'pengembalian' : 'peminjaman';
                header("Location: index.php?c=peminjam&a={$redirect}&status=error");
                exit;
            }
        }
        header('Location: index.php?c=peminjam&a=peminjaman');
        exit;
    }

    // ==========================================
    // 7. RIWAYAT PENGEMBALIAN SAYA
    // ==========================================
    public function pengembalian() {
        $profile = $this->getPeminjamProfile();
        $idPeminjam = (int)$profile['id'];
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';

        $message = '';
        $error = '';
        if (isset($_GET['status'])) {
            if ($_GET['status'] === 'return_requested') $message = 'Pengajuan pengembalian berhasil dikirim! Silakan bawa & serahkan fisik alat ke meja Petugas Lab untuk dicek kondisinya dan diverifikasi.';
            if ($_GET['status'] === 'returned') $message = 'Alat laboratorium telah diverifikasi & selesai dikembalikan! Terima kasih telah menjaga fasilitas lab dengan baik.';
            if ($_GET['status'] === 'error') $error = 'Terjadi kesalahan sistem saat memproses transaksi pengembalian.';
        }

        $daftarPengembalian = $this->pengembalianModel->getPengembalianByPeminjamId($idPeminjam, $keyword);
        $totalDenda = $this->pengembalianModel->getTotalDendaByPeminjamId($idPeminjam);

        // Ambil peminjaman aktif & menunggu pengembalian agar siswa dapat memantau status validasi
        $semuaPinjaman = $this->peminjamanModel->getPeminjamanByPeminjamId($idPeminjam);
        $daftarPeminjamanAktif = array_values(array_filter($semuaPinjaman, function($item) {
            return in_array(strtolower($item['status']), ['disetujui', 'dipinjam', 'menunggu_pengembalian']);
        }));

        require_once __DIR__ . '/../views/peminjam_pengembalian.php';
    }
}
