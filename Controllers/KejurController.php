<?php

require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/alat.php';
require_once __DIR__ . '/../models/kategori.php';
require_once __DIR__ . '/../models/peminjam.php';
require_once __DIR__ . '/../models/peminjaman.php';
require_once __DIR__ . '/../models/pengembalian.php';

class KejurController {
    private $userModel;
    private $alatModel;
    private $kategoriModel;
    private $peminjamModel;
    private $peminjamanModel;
    private $pengembalianModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        $role = strtolower(trim($_SESSION['user']['role'] ?? ''));
        // Hak akses Ketua Jurusan (role: admin) dan Pengelola (role: admin pengelola)
        if (!in_array($role, ['admin', 'admin pengelola', 'pengelola'])) {
            if ($role === 'petugas') {
                header('Location: index.php?c=petugas&a=dashboard');
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
        $this->peminjamModel     = new Peminjam();
        $this->peminjamanModel   = new Peminjaman();
        $this->pengembalianModel = new Pengembalian();
    }

    // ==========================================
    // 1. DASHBOARD MONITORING KETUA JURUSAN (STATISTIK & GRAFIK REAL-TIME)
    // ==========================================
    public function dashboard() {
        $hariArr = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $bulanArr = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $tglStr = strtoupper($hariArr[date('w')] . ', ' . date('j') . ' ' . $bulanArr[(int)date('n')] . ' ' . date('Y'));

        $totalSiswa = count($this->peminjamModel->getAllPeminjam());
        $totalAlat = count($this->alatModel->getAllAlat());
        $totalPeminjaman = count($this->peminjamanModel->getAllPeminjaman());
        $totalPengembalian = count($this->pengembalianModel->getAllPengembalian());

        // Counter Ringkasan Real-Time
        $alatAktifDipinjam = $this->peminjamanModel->getActivePeminjaman();
        $totalAlatSedangDipinjam = 0;
        foreach ($alatAktifDipinjam as $ap) {
            $totalAlatSedangDipinjam += (int)($ap['jumlah'] ?? 1);
        }
        $totalTerlambat = $this->peminjamanModel->countLatePeminjaman();

        // Grafik 1: Tren Alat Paling Sering Dipinjam
        $topAlat = $this->alatModel->getTopBorrowedAlat(5);

        // Grafik 2: Status Aset Lab (Pie Chart Persentase Kondisi)
        $kondisiDist = $this->alatModel->getKondisiDistribution();

        // Rekapitulasi Kas Denda
        $statDenda = $this->pengembalianModel->getStatistikDenda();

        // Audit Trail Terbaru (5 log terakhir)
        $auditLogsTerbaru = array_slice($this->userModel->getAllLogs(), 0, 5);

        require_once __DIR__ . '/../views/kejur_dashboard.php';
    }

    // ==========================================
    // 2. LAPORAN EKSEKUTIF: RIWAYAT PEMINJAMAN
    // ==========================================
    public function laporan_peminjaman() {
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        $statusFilter = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : '';
        $kelas = isset($_GET['kelas']) ? trim($_GET['kelas']) : '';
        $idAlat = isset($_GET['id_alat']) ? (int)$_GET['id_alat'] : 0;

        $daftarPeminjaman = $this->peminjamanModel->getAllPeminjaman($keyword, $statusFilter);
        if (!empty($kelas)) {
            $daftarPeminjaman = array_values(array_filter($daftarPeminjaman, function($p) use ($kelas) {
                return ($p['kelas'] ?? '') === $kelas;
            }));
        }
        if ($idAlat > 0) {
            $daftarPeminjaman = array_values(array_filter($daftarPeminjaman, function($p) use ($idAlat) {
                return (int)($p['id_alat'] ?? 0) === $idAlat;
            }));
        }

        $daftarAlat = $this->alatModel->getAllAlat();
        require_once __DIR__ . '/../views/kejur_laporan_peminjaman.php';
    }

    // ==========================================
    // 2b. LAPORAN EKSEKUTIF: KAS DENDA & SANKSI
    // ==========================================
    public function laporan_denda() {
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        $daftarDenda = $this->pengembalianModel->getLaporanKasDenda(null, $keyword);
        $statDenda = $this->pengembalianModel->getStatistikDenda();

        require_once __DIR__ . '/../views/kejur_laporan_denda.php';
    }

    // ==========================================
    // 3. AUDIT TRAIL / LOG AKTIVITAS GLOBAL
    // ==========================================
    public function log() {
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        $daftarLog = $this->userModel->getAllLogs($keyword);

        require_once __DIR__ . '/../views/kejur_log.php';
    }

    // ==========================================
    // 4. DAFTAR INVENTARIS ALAT LAB (READ-ONLY)
    // ==========================================
    public function inventaris() {
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        $idKategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

        $daftarAlat = $this->alatModel->getAllAlat($keyword);
        if ($idKategori > 0) {
            $daftarAlat = array_values(array_filter($daftarAlat, function($a) use ($idKategori) {
                return (int)$a['id_kategori'] === $idKategori;
            }));
        }
        $daftarKategori = $this->kategoriModel->getAllKategori();

        require_once __DIR__ . '/../views/kejur_inventaris.php';
    }

    // Kompatibilitas rute lama laporan
    public function laporan_peminjam() {
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        $daftarPeminjam = $this->peminjamModel->getAllPeminjam($keyword);
        require_once __DIR__ . '/../views/kejur_laporan_peminjam.php';
    }

    public function laporan_alat() {
        $this->inventaris();
    }

    public function laporan_pengembalian() {
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        $daftarPengembalian = $this->pengembalianModel->getAllPengembalian($keyword);
        require_once __DIR__ . '/../views/kejur_laporan_pengembalian.php';
    }
}
