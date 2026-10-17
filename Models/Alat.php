<?php

require_once __DIR__ . '/Database.php';

class Alat {
    private $conn;
    private $table_name = "alat";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    private $pkCol = null;

    private function getPkCol() {
        if ($this->pkCol === null) {
            $this->pkCol = 'id_alat';
            try {
                $chk = $this->conn->query("SHOW COLUMNS FROM " . $this->table_name . " LIKE 'id_alat'");
                if (!$chk || $chk->rowCount() === 0) {
                    $chkId = $this->conn->query("SHOW COLUMNS FROM " . $this->table_name . " LIKE 'id'");
                    if ($chkId && $chkId->rowCount() > 0) {
                        $this->pkCol = 'id';
                    }
                }
            } catch (Exception $e) {}
        }
        return $this->pkCol;
    }

    public function getAllAlat($keyword = null) {
        $pk = $this->getPkCol();
        $query = "SELECT a.*, a.{$pk} as id, k.nama_kategori 
                  FROM " . $this->table_name . " a 
                  LEFT JOIN kategori k ON a.id_kategori = k.id_kategori";

        if (!empty($keyword)) {
            $query .= " WHERE a.nama_alat LIKE :keyword OR a.kode LIKE :keyword OR k.nama_kategori LIKE :keyword OR a.kondisi LIKE :keyword";
        }

        $query .= " ORDER BY a.{$pk} DESC";
        $stmt = $this->conn->prepare($query);

        if (!empty($keyword)) {
            $searchTerm = "%" . $keyword . "%";
            $stmt->bindParam(':keyword', $searchTerm);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAlatById($id) {
        $pk = $this->getPkCol();
        $query = "SELECT a.*, a.{$pk} as id, k.nama_kategori 
                  FROM " . $this->table_name . " a 
                  LEFT JOIN kategori k ON a.id_kategori = k.id_kategori 
                  WHERE a.{$pk} = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createAlat($kode, $id_kategori, $nama_alat, $jumlah, $kondisi, $deskripsi) {
        $query = "INSERT INTO " . $this->table_name . " (kode, id_kategori, nama_alat, jumlah, kondisi, deskripsi) 
                  VALUES (:kode, :id_kategori, :nama_alat, :jumlah, :kondisi, :deskripsi)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':kode', $kode);
        $stmt->bindParam(':id_kategori', $id_kategori, PDO::PARAM_INT);
        $stmt->bindParam(':nama_alat', $nama_alat);
        $stmt->bindParam(':jumlah', $jumlah, PDO::PARAM_INT);
        $stmt->bindParam(':kondisi', $kondisi);
        $stmt->bindParam(':deskripsi', $deskripsi);
        return $stmt->execute();
    }

    public function updateAlat($id, $kode, $id_kategori, $nama_alat, $jumlah, $kondisi, $deskripsi) {
        $pk = $this->getPkCol();
        $query = "UPDATE " . $this->table_name . " 
                  SET kode = :kode, id_kategori = :id_kategori, nama_alat = :nama_alat, 
                      jumlah = :jumlah, kondisi = :kondisi, deskripsi = :deskripsi 
                  WHERE {$pk} = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':kode', $kode);
        $stmt->bindParam(':id_kategori', $id_kategori, PDO::PARAM_INT);
        $stmt->bindParam(':nama_alat', $nama_alat);
        $stmt->bindParam(':jumlah', $jumlah, PDO::PARAM_INT);
        $stmt->bindParam(':kondisi', $kondisi);
        $stmt->bindParam(':deskripsi', $deskripsi);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteAlat($id) {
        $pk = $this->getPkCol();
        try {
            // Hapus detail peminjaman yang memakai alat ini agar tidak melanggar foreign key
            $this->conn->exec("DELETE FROM detail_peminjaman WHERE id_alat = " . (int)$id);
        } catch (Exception $e) {}

        $query = "DELETE FROM " . $this->table_name . " WHERE {$pk} = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getDashboardStats() {
        $stats = [
            'total_alat' => 0,
            'total_kategori' => 0,
            'sedang_dipinjam' => 0,
            'menunggu_persetujuan' => 0
        ];

        try {
            $q1 = $this->conn->query("SELECT COUNT(*) as total FROM " . $this->table_name);
            $stats['total_alat'] = (int)($q1->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

            $q2 = $this->conn->query("SELECT COUNT(*) as total FROM kategori");
            $stats['total_kategori'] = (int)($q2->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

            $q3 = $this->conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status IN ('disetujui', 'dipinjam', 'menunggu_pengembalian', 'menunggu_validasi')");
            $stats['sedang_dipinjam'] = (int)($q3->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

            $q4 = $this->conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'menunggu'");
            $stats['menunggu_persetujuan'] = (int)($q4->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (Exception $e) {}

        return $stats;
    }

    public function getTopBorrowedAlat($limit = 5) {
        try {
            $query = "SELECT a.nama_alat, a.kode, COALESCE(SUM(dp.jumlah), 0) as total_pinjam
                      FROM detail_peminjaman dp
                      JOIN alat a ON dp.id_alat = a.id_alat
                      JOIN peminjaman p ON dp.id_peminjaman = p.id_peminjaman
                      WHERE p.status IN ('disetujui', 'dipinjam', 'menunggu_pengembalian', 'menunggu_validasi', 'dikembalikan')
                      GROUP BY a.id_alat, a.nama_alat, a.kode
                      ORDER BY total_pinjam DESC
                      LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($results)) {
                return $results;
            }
        } catch (Exception $e) {}

        // Fallback jika belum ada transaksi: ambil alat teratas
        try {
            $q = $this->conn->query("SELECT nama_alat, kode, 0 as total_pinjam FROM alat ORDER BY id_alat ASC LIMIT 5");
            return $q->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getKondisiDistribution() {
        $dist = ['Baik' => 0, 'Rusak Ringan' => 0, 'Rusak Berat' => 0];
        try {
            $query = "SELECT kondisi, SUM(jumlah) as total FROM alat GROUP BY kondisi";
            $stmt = $this->conn->query($query);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $kondisi = trim($r['kondisi']);
                if (isset($dist[$kondisi])) {
                    $dist[$kondisi] = (int)$r['total'];
                } else {
                    $dist[$kondisi] = (int)$r['total'];
                }
            }
        } catch (Exception $e) {}
        return $dist;
    }
}
