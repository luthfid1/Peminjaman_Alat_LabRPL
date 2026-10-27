<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = isset($_GET['c']) ? strtolower(trim($_GET['c'])) : '';
$action     = isset($_GET['a']) ? strtolower(trim($_GET['a'])) : '';

if (empty($controller)) {
    $controller = 'home';
    $action     = 'index';
}

switch ($controller) {
    case 'home':
        require_once __DIR__ . '/Controllers/HomeController.php';
        $home = new HomeController();
        $home->index();
        break;

    case 'pengelola':
    case 'admin':
        require_once __DIR__ . '/Controllers/AdminController.php';
        $admin = new AdminController();

        switch ($action) {
            case 'dashboard':
                $admin->dashboard();
                break;

            case 'alat':
                $admin->alat();
                break;
            case 'tambah_alat':
                $admin->tambah_alat();
                break;
            case 'ubah_alat':
                $admin->ubah_alat();
                break;
            case 'hapus_alat':
                $admin->hapus_alat();
                break;

            case 'kategori':
                $admin->kategori();
                break;
            case 'tambah_kategori':
                $admin->tambah_kategori();
                break;
            case 'ubah_kategori':
                $admin->ubah_kategori();
                break;
            case 'hapus_kategori':
                $admin->hapus_kategori();
                break;

            case 'pengguna':
                $admin->pengguna();
                break;
            case 'tambah_pengguna':
                $admin->tambah_pengguna();
                break;
            case 'ubah_pengguna':
                $admin->ubah_pengguna();
                break;
            case 'hapus_pengguna':
                $admin->hapus_pengguna();
                break;

            case 'peminjaman':
                $admin->peminjaman();
                break;
            case 'tambah_peminjaman':
                $admin->tambah_peminjaman();
                break;
            case 'ubah_peminjaman':
                $admin->ubah_peminjaman();
                break;
            case 'hapus_peminjaman':
                $admin->hapus_peminjaman();
                break;

            case 'pengembalian':
                $admin->pengembalian();
                break;
            case 'tambah_pengembalian':
                $admin->tambah_pengembalian();
                break;
            case 'ubah_pengembalian':
                $admin->ubah_pengembalian();
                break;
            case 'hapus_pengembalian':
                $admin->hapus_pengembalian();
                break;

            case 'log':
                $admin->log();
                break;

            default:
                $admin->dashboard();
                break;
        }
        break;

    case 'kejur':
        require_once __DIR__ . '/Controllers/KejurController.php';
        $kejur = new KejurController();

        switch ($action) {
            case 'dashboard':
                $kejur->dashboard();
                break;
            case 'laporan_peminjam':
                $kejur->laporan_peminjam();
                break;
            case 'laporan_alat':
                $kejur->laporan_alat();
                break;
            case 'laporan_peminjaman':
                $kejur->laporan_peminjaman();
                break;
            case 'laporan_denda':
            case 'laporan_kas_denda':
                $kejur->laporan_denda();
                break;
            case 'laporan_pengembalian':
                $kejur->laporan_pengembalian();
                break;
            case 'log':
            case 'audit_trail':
                $kejur->log();
                break;
            case 'inventaris':
                $kejur->inventaris();
                break;
            default:
                $kejur->dashboard();
                break;
        }
        break;

    case 'petugas':
        require_once __DIR__ . '/Controllers/PetugasController.php';
        $petugas = new PetugasController();

        switch ($action) {
            case 'dashboard':
                $petugas->dashboard();
                break;
            case 'peminjaman':
                $petugas->peminjaman();
                break;
            case 'setujui_peminjaman':
                $petugas->setujui_peminjaman();
                break;
            case 'tolak_peminjaman':
                $petugas->tolak_peminjaman();
                break;
            case 'serahkan_alat':
                $petugas->serahkan_alat();
                break;
            case 'ingatkan_kembali':
                $petugas->ingatkan_kembali();
                break;
            case 'pengembalian':
                $petugas->pengembalian();
                break;
            case 'tambah_pengembalian':
                $petugas->tambah_pengembalian();
                break;
            case 'verifikasi_pengembalian':
                $petugas->verifikasi_pengembalian();
                break;
            case 'alat':
                $petugas->alat();
                break;
            case 'laporan':
                $petugas->laporan();
                break;
            default:
                $petugas->dashboard();
                break;
        }
        break;

    case 'peminjam':
        require_once __DIR__ . '/Controllers/PeminjamController.php';
        $peminjam = new PeminjamController();

        switch ($action) {
            case 'dashboard':
                $peminjam->dashboard();
                break;
            case 'daftar_alat':
            case 'katalog':
                $peminjam->daftar_alat();
                break;
            case 'peminjaman':
                $peminjam->peminjaman();
                break;
            case 'ajukan_peminjaman':
                $peminjam->ajukan_peminjaman();
                break;
            case 'batalkan_peminjaman':
                $peminjam->batalkan_peminjaman();
                break;
            case 'kembalikan_alat':
                $peminjam->kembalikan_alat();
                break;
            case 'pengembalian':
                $peminjam->pengembalian();
                break;
            default:
                $peminjam->dashboard();
                break;
        }
        break;

    case 'auth':
        require_once __DIR__ . '/Controllers/AuthController.php';
        $auth = new AuthController();

        switch ($action) {
            case 'login':
                $auth->login();
                break;
            case 'register':
                $auth->register();
                break;
            case 'logout':
                $auth->logout();
                break;
            default:
                $auth->login();
                break;
        }
        break;

    default:
        header('Location: index.php?c=home&a=index');
        exit;
}
