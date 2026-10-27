<?php

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Alat.php';
require_once __DIR__ . '/../Models/Kategori.php';
require_once __DIR__ . '/../Models/Peminjam.php';
require_once __DIR__ . '/../Models/Peminjaman.php';
require_once __DIR__ . '/../Models/Pengembalian.php';

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

    public function dashboard() {
        $hariArr = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $bulanArr = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $tglStr = strtoupper($hariArr[date('w')] . ', ' . date('j') . ' ' . $bulanArr[(int)date('n')] . ' ' . date('Y'));

        $totalSiswa = count($this->peminjamModel->getAllPeminjam());
        $totalAlat = count($this->alatModel->getAllAlat());
        $totalPeminjaman = count($this->peminjamanModel->getAllPeminjaman());
        $totalPengembalian = count($this->pengembalianModel->getAllPengembalian());

        $alatAktifDipinjam = $this->peminjamanModel->getActivePeminjaman();
        $totalAlatSedangDipinjam = 0;
        foreach ($alatAktifDipinjam as $ap) {
            $totalAlatSedangDipinjam += (int)($ap['jumlah'] ?? 1);
        }
        $totalTerlambat = $this->peminjamanModel->countLatePeminjaman();

        $topAlat = $this->alatModel->getTopBorrowedAlat(5);

        $kondisiDist = $this->alatModel->getKondisiDistribution();

        $statDenda = $this->pengembalianModel->getStatistikDenda();

        $auditLogsTerbaru = array_slice($this->userModel->getAllLogs(), 0, 5);

        require_once __DIR__ . '/../Views/kejur_dashboard.php';
    }

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
        require_once __DIR__ . '/../Views/kejur_laporan_peminjaman.php';
    }

    public function laporan_denda() {
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        $daftarDenda = $this->pengembalianModel->getLaporanKasDenda(null, $keyword);
        $statDenda = $this->pengembalianModel->getStatistikDenda();

        require_once __DIR__ . '/../Views/kejur_laporan_denda.php';
    }

    public function log() {
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        $daftarLog = $this->userModel->getAllLogs($keyword);

        require_once __DIR__ . '/../Views/kejur_log.php';
    }

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

        require_once __DIR__ . '/../Views/kejur_inventaris.php';
    }

    public function laporan_peminjam() {
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        $daftarPeminjam = $this->peminjamModel->getAllPeminjam($keyword);
        require_once __DIR__ . '/../Views/kejur_laporan_peminjam.php';
    }

    public function laporan_alat() {
        $this->inventaris();
    }

    public function laporan_pengembalian() {
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        $daftarPengembalian = $this->pengembalianModel->getAllPengembalian($keyword);
        require_once __DIR__ . '/../Views/kejur_laporan_pengembalian.php';
    }
}
