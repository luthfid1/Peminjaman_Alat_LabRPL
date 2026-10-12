<?php

require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/alat.php';
require_once __DIR__ . '/../models/kategori.php';
require_once __DIR__ . '/../models/peminjaman.php';
require_once __DIR__ . '/../models/pengembalian.php';
require_once __DIR__ . '/../models/notifikasi.php';

class PetugasController {
    private $userModel;
    private $alatModel;
    private $kategoriModel;
    private $peminjamanModel;
    private $pengembalianModel;
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
        if ($role !== 'petugas') {
            if ($role === 'pengelola' || $role === 'admin') {
                header('Location: index.php?c=admin&a=dashboard');
                exit;
            } elseif ($role === 'peminjam') {
                header('Location: index.php?c=peminjam&a=dashboard');
                exit;
            } else {
                header('Location: index.php');
                exit;
            }
        }

        $this->userModel         = new User();
        $this->alatModel         = new Alat();
        $this->kategoriModel     = new Kategori();
        $this->peminjamanModel   = new Peminjaman();
        $this->pengembalianModel = new Pengembalian();
        $this->notifikasiModel   = new Notifikasi();
    }

    private function getUserId() {
        return (int)($_SESSION['user']['id_user'] ?? $_SESSION['user']['id_users'] ?? 0);
    }

    // ==========================================
    // 1. DASHBOARD PETUGAS
    // ==========================================
    public function dashboard() {
        $hariArr = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $bulanArr = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $tglStr = strtoupper($hariArr[date('w')] . ', ' . date('j') . ' ' . $bulanArr[(int)date('n')] . ' ' . date('Y'));

        $totalMenunggu = count($this->peminjamanModel->getAllPeminjaman(null, 'menunggu'));
        $peminjamanAktif = $this->peminjamanModel->getActivePeminjaman();
        $totalAktif = count($peminjamanAktif);
        $menungguPengembalian = $this->peminjamanModel->getMenungguPengembalian();
        $totalMenungguKembali = count($menungguPengembalian);
        $daftarPengembalian = $this->pengembalianModel->getAllPengembalian();
        $totalPengembalian = count($daftarPengembalian);
        $totalAlat = count($this->alatModel->getAllAlat());

        $permohonanMenunggu = $this->peminjamanModel->getAllPeminjaman(null, 'menunggu');

        require_once __DIR__ . '/../views/petugas_dashboard.php';
    }

    // ==========================================
    // 2. KELOLA PERSETUJUAN PEMINJAMAN
    // ==========================================
    public function peminjaman() {
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        $statusFilter = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : '';

        $message = '';
        $error = '';
        if (isset($_GET['status'])) {
            if ($_GET['status'] === 'approved') $message = 'Permohonan peminjaman berhasil disetujui.';
            if ($_GET['status'] === 'rejected') $message = 'Permohonan peminjaman telah ditolak.';
            if ($_GET['status'] === 'handed') $message = 'Alat laboratorium telah diserahkan kepada siswa.';
            if ($_GET['status'] === 'verified') $message = 'Pengembalian alat lab berhasil diverifikasi & stok telah dipulihkan!';
            if ($_GET['status'] === 'reminded') $message = 'Notifikasi pengingat pengembalian berhasil dikirimkan kepada siswa.';
            if ($_GET['status'] === 'error') $error = 'Terjadi kesalahan sistem saat memproses permohonan.';
        }

        $daftarPeminjaman = $this->peminjamanModel->getAllPeminjaman($keyword, $statusFilter);
        $notifikasiModel = $this->notifikasiModel;

        require_once __DIR__ . '/../views/petugas_peminjaman.php';
    }

    public function setujui_peminjaman() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            try {
                $pmj = $this->peminjamanModel->getPeminjamanById($id);
                if ($pmj && $pmj['status'] === 'menunggu') {
                    $this->peminjamanModel->updateStatus($id, 'disetujui', $this->getUserId());
                    $this->userModel->recordLog(
                        $this->getUserId(),
                        'Menyetujui Peminjaman',
                        "Petugas menyetujui peminjaman #{$pmj['kode_peminjaman']} ({$pmj['nama_peminjam']})"
                    );
                    header('Location: index.php?c=petugas&a=peminjaman&status=approved');
                    exit;
                }
            } catch (Exception $e) {
                header('Location: index.php?c=petugas&a=peminjaman&status=error');
                exit;
            }
        }
        header('Location: index.php?c=petugas&a=peminjaman');
        exit;
    }

    public function tolak_peminjaman() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            try {
                $pmj = $this->peminjamanModel->getPeminjamanById($id);
                if ($pmj && $pmj['status'] === 'menunggu') {
                    $this->peminjamanModel->updateStatus($id, 'ditolak', $this->getUserId());
                    $this->userModel->recordLog(
                        $this->getUserId(),
                        'Menolak Peminjaman',
                        "Petugas menolak peminjaman #{$pmj['kode_peminjaman']} ({$pmj['nama_peminjam']})"
                    );
                    header('Location: index.php?c=petugas&a=peminjaman&status=rejected');
                    exit;
                }
            } catch (Exception $e) {
                header('Location: index.php?c=petugas&a=peminjaman&status=error');
                exit;
            }
        }
        header('Location: index.php?c=petugas&a=peminjaman');
        exit;
    }

    public function serahkan_alat() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            try {
                $pmj = $this->peminjamanModel->getPeminjamanById($id);
                if ($pmj && $pmj['status'] === 'disetujui') {
                    $this->peminjamanModel->updateStatus($id, 'dipinjam', $this->getUserId());
                    $this->userModel->recordLog(
                        $this->getUserId(),
                        'Menyerahkan Alat Lab',
                        "Alat untuk peminjaman #{$pmj['kode_peminjaman']} telah diserahkan fisik kepada siswa"
                    );
                    header('Location: index.php?c=petugas&a=peminjaman&status=handed');
                    exit;
                }
            } catch (Exception $e) {
                header('Location: index.php?c=petugas&a=peminjaman&status=error');
                exit;
            }
        }
        header('Location: index.php?c=petugas&a=peminjaman');
        exit;
    }

    public function ingatkan_kembali() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            try {
                $pmj = $this->peminjamanModel->getPeminjamanById($id);
                if ($pmj && in_array(strtolower($pmj['status']), ['dipinjam', 'disetujui'])) {
                    $idPeminjam = (int)$pmj['id_peminjam'];
                    $namaAlat = $pmj['nama_alat'] ?? 'Perangkat Lab';
                    $kodePmj = $pmj['kode_peminjaman'] ?? "-";
                    $judul = "Peringatan Pengembalian Alat Lab";
                    $pesan = "Petugas Lab meminta Anda untuk segera mengembalikan perangkat {$namaAlat} (Kode: {$kodePmj}) ke ruang laboratorium komputer.";

                    $this->notifikasiModel->createNotifikasi($idPeminjam, $id, $judul, $pesan);
                    $this->userModel->recordLog(
                        $this->getUserId(),
                        'Kirim Pengingat Pengembalian',
                        "Petugas mengirim notifikasi pengembalian alat #{$kodePmj} kepada siswa {$pmj['nama_peminjam']}"
                    );
                    header('Location: index.php?c=petugas&a=peminjaman&status=reminded');
                    exit;
                }
            } catch (Exception $e) {
                header('Location: index.php?c=petugas&a=peminjaman&status=error');
                exit;
            }
        }
        header('Location: index.php?c=petugas&a=peminjaman');
        exit;
    }

    // ==========================================
    // 3. KELOLA PENGEMBALIAN & VALIDASI FISIK ALAT
    // ==========================================
    public function pengembalian() {
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';

        $message = '';
        $error = '';
        if (isset($_GET['status'])) {
            if ($_GET['status'] === 'verified') $message = 'Verifikasi pengembalian alat berhasil! Kondisi fisik telah divalidasi dan stok alat resmi dipulihkan ke inventaris lab.';
            if ($_GET['status'] === 'added') $message = 'Pengembalian alat lab berhasil dicatat & stok telah dipulihkan.';
            if ($_GET['status'] === 'error') $error = 'Terjadi kesalahan saat memproses pengembalian alat.';
        }

        $daftarPengembalian   = $this->pengembalianModel->getAllPengembalian($keyword);
        $peminjamanAktif      = $this->peminjamanModel->getActivePeminjaman();
        $menungguPengembalian = $this->peminjamanModel->getMenungguPengembalian($keyword);

        require_once __DIR__ . '/../views/petugas_pengembalian.php';
    }

    public function verifikasi_pengembalian() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_peminjaman   = (int)($_POST['id_peminjaman'] ?? 0);
            $waktu_kembali   = trim($_POST['waktu_kembali'] ?? date('H:i'));
            $kondisi_kembali = trim($_POST['kondisi_kembali'] ?? 'Baik & Lengkap');
            $catatan_qc      = trim($_POST['catatan_qc'] ?? '');
            $namaPetugas     = $_SESSION['user']['nama'] ?? 'Petugas Lab';

            if (strlen($waktu_kembali) === 5) $waktu_kembali .= ':00';

            if ($id_peminjaman > 0) {
                try {
                    $pmj = $this->peminjamanModel->getPeminjamanById($id_peminjaman);
                    if ($pmj) {
                        // HITUNG DENDA OTOMATIS OLEH SISTEM (Petugas tidak berhak mengubah / memanipulasi denda)
                        // 1. Denda Keterlambatan: Rp5.000 / jam
                        $dendaTerlambat = 0;
                        if (!empty($pmj['waktu_rencana_kembali'])) {
                            $tRencana = strtotime($pmj['waktu_rencana_kembali']);
                            $tKembali = strtotime($waktu_kembali);
                            if ($tKembali > $tRencana) {
                                $selisihJam = (int)ceil(($tKembali - $tRencana) / 3600);
                                if ($selisihJam > 0) {
                                    $dendaTerlambat = $selisihJam * 5000;
                                }
                            }
                        }

                        // 2. Denda Kondisi Fisik (QC):
                        // - Rusak Ringan (< 50 rb) -> Rp25.000
                        // - Rusak Sedang           -> Rp100.000
                        // - Rusak Berat / Hilang   -> Rp150.000
                        $dendaKondisi = 0;
                        if ($kondisi_kembali === 'Rusak Ringan') {
                            $dendaKondisi = 25000; // di bawah 50 rb
                        } elseif ($kondisi_kembali === 'Rusak Sedang') {
                            $dendaKondisi = 100000; // 100 rb
                        } elseif ($kondisi_kembali === 'Rusak Berat' || $kondisi_kembali === 'Komponen Hilang') {
                            $dendaKondisi = 150000; // 150 rb
                        }

                        $denda = (float)($dendaTerlambat + $dendaKondisi);

                        // 1. Simpan data pengembalian resmi & pulihkan stok alat
                        $newId = $this->pengembalianModel->createPengembalian(
                            $id_peminjaman,
                            $waktu_kembali,
                            $kondisi_kembali,
                            $denda,
                            $this->getUserId()
                        );

                        // 2. Tandai notifikasi pengingat lama sebagai telah dibaca
                        $this->notifikasiModel->markAsReadByPeminjaman($id_peminjaman);

                        // 3. Kirim notifikasi konfirmasi ke akun siswa
                        $this->notifikasiModel->createNotifikasi(
                            $pmj['id_peminjam'],
                            $id_peminjaman,
                            'Pengembalian Alat Telah Divalidasi',
                            "Perangkat {$pmj['nama_alat']} (Kode: {$pmj['kode_peminjaman']}) telah diterima & diverifikasi fisik oleh {$namaPetugas}. Kondisi: {$kondisi_kembali}, Denda: Rp" . number_format($denda, 0, ',', '.') . ". Terima kasih telah menjaga fasilitas lab!"
                        );

                        // 4. Catat Log Aktivitas sebagai Saksi Digital penanggung jawab
                        $descLog = "Petugas {$namaPetugas} telah memvalidasi fisik & menerima pengembalian {$pmj['nama_alat']} ({$pmj['jumlah']} unit) dari siswa {$pmj['nama_peminjam']} (Kode: {$pmj['kode_peminjaman']}). Kondisi: {$kondisi_kembali}, Denda: Rp" . number_format($denda, 0, ',', '.');
                        if (!empty($catatan_qc)) {
                            $descLog .= " | Catatan QC: {$catatan_qc}";
                        }
                        $descLog .= " | Stok alat laboratorium berhasil dipulihkan.";

                        $this->userModel->recordLog(
                            $this->getUserId(),
                            'Verifikasi Pengembalian Alat',
                            $descLog
                        );

                        $redirect = isset($_POST['redirect']) && $_POST['redirect'] === 'peminjaman' ? 'peminjaman' : 'pengembalian';
                        header("Location: index.php?c=petugas&a={$redirect}&status=verified");
                        exit;
                    }
                } catch (Exception $e) {
                    $redirect = isset($_POST['redirect']) && $_POST['redirect'] === 'peminjaman' ? 'peminjaman' : 'pengembalian';
                    header("Location: index.php?c=petugas&a={$redirect}&status=error");
                    exit;
                }
            }
        }
        header('Location: index.php?c=petugas&a=pengembalian');
        exit;
    }

    public function tambah_pengembalian() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_peminjaman   = (int)($_POST['id_peminjaman'] ?? 0);
            $waktu_kembali   = trim($_POST['waktu_kembali'] ?? date('H:i'));
            $kondisi_kembali = trim($_POST['kondisi_kembali'] ?? 'Baik & Lengkap');
            $catatan_qc      = trim($_POST['catatan_qc'] ?? '');
            $namaPetugas     = $_SESSION['user']['nama'] ?? 'Petugas Lab';

            if (strlen($waktu_kembali) === 5) $waktu_kembali .= ':00';

            if ($id_peminjaman > 0) {
                try {
                    $pmj = $this->peminjamanModel->getPeminjamanById($id_peminjaman);

                    // HITUNG DENDA OTOMATIS OLEH SISTEM
                    $dendaTerlambat = 0;
                    if ($pmj && !empty($pmj['waktu_rencana_kembali'])) {
                        $tRencana = strtotime($pmj['waktu_rencana_kembali']);
                        $tKembali = strtotime($waktu_kembali);
                        if ($tKembali > $tRencana) {
                            $selisihJam = (int)ceil(($tKembali - $tRencana) / 3600);
                            if ($selisihJam > 0) {
                                $dendaTerlambat = $selisihJam * 5000;
                            }
                        }
                    }

                    $dendaKondisi = 0;
                    if ($kondisi_kembali === 'Rusak Ringan') {
                        $dendaKondisi = 25000; // < 50 rb
                    } elseif ($kondisi_kembali === 'Rusak Sedang') {
                        $dendaKondisi = 100000; // 100 rb
                    } elseif ($kondisi_kembali === 'Rusak Berat' || $kondisi_kembali === 'Komponen Hilang') {
                        $dendaKondisi = 150000; // 150 rb
                    }

                    $denda = (float)($dendaTerlambat + $dendaKondisi);

                    $newId = $this->pengembalianModel->createPengembalian(
                        $id_peminjaman,
                        $waktu_kembali,
                        $kondisi_kembali,
                        $denda,
                        $this->getUserId()
                    );

                    $this->notifikasiModel->markAsReadByPeminjaman($id_peminjaman);

                    if ($pmj) {
                        $this->notifikasiModel->createNotifikasi(
                            $pmj['id_peminjam'],
                            $id_peminjaman,
                            'Pengembalian Alat Selesai',
                            "Perangkat {$pmj['nama_alat']} (Kode: {$pmj['kode_peminjaman']}) telah diterima oleh {$namaPetugas}. Kondisi: {$kondisi_kembali}, Denda: Rp" . number_format($denda, 0, ',', '.') . "."
                        );
                    }

                    $namaSiswa = $pmj['nama_peminjam'] ?? "Peminjaman #{$id_peminjaman}";
                    $namaAlat  = $pmj['nama_alat'] ?? 'Alat Lab';
                    $this->userModel->recordLog(
                        $this->getUserId(),
                        'Mencatat Pengembalian',
                        "Petugas {$namaPetugas} mencatat pengembalian {$namaAlat} dari {$namaSiswa}. Kondisi: {$kondisi_kembali}, Denda: Rp" . number_format($denda, 0, ',', '.') . ". Stok telah dipulihkan."
                    );
                    header('Location: index.php?c=petugas&a=pengembalian&status=added');
                    exit;
                } catch (Exception $e) {
                    header('Location: index.php?c=petugas&a=pengembalian&status=error');
                    exit;
                }
            }
        }
        header('Location: index.php?c=petugas&a=pengembalian');
        exit;
    }

    // ==========================================
    // 4. MONITORING INVENTARIS ALAT LAB
    // ==========================================
    public function alat() {
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        $idKategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

        $daftarAlat = $this->alatModel->getAllAlat($keyword);
        if ($idKategori > 0) {
            $daftarAlat = array_values(array_filter($daftarAlat, function($a) use ($idKategori) {
                return (int)$a['id_kategori'] === $idKategori;
            }));
        }
        $daftarKategori = $this->kategoriModel->getAllKategori();

        require_once __DIR__ . '/../views/petugas_alat.php';
    }

    // ==========================================
    // 5. LAPORAN TRANSAKSI LAB
    // ==========================================
    public function laporan() {
        $filter = isset($_GET['tipe']) ? trim($_GET['tipe']) : 'peminjaman';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';

        $daftarPeminjaman = $this->peminjamanModel->getAllPeminjaman(null, $status ?: null);
        $daftarPengembalian = $this->pengembalianModel->getAllPengembalian();

        require_once __DIR__ . '/../views/petugas_laporan.php';
    }
}
