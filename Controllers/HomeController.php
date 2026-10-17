<?php

require_once __DIR__ . '/../Models/Alat.php';

class HomeController {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $alatModel = new Alat();
        $daftarAlat = $alatModel->getAllAlat();

        require_once __DIR__ . '/../Views/landing.php';
    }
}
