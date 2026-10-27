<?php

require_once __DIR__ . '/Database.php';

class Peminjam {
    private $conn;
    private $table_name = "peminjam";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllPeminjam($keyword = null) {
        $query = "SELECT *, id_peminjam as id FROM " . $this->table_name;
        if (!empty($keyword)) {
            $query .= " WHERE nama LIKE :keyword OR nis LIKE :keyword OR kelas LIKE :keyword OR jurusan LIKE :keyword";
        }
        $query .= " ORDER BY id_peminjam DESC";

        $stmt = $this->conn->prepare($query);
        if (!empty($keyword)) {
            $searchTerm = "%" . $keyword . "%";
            $stmt->bindParam(':keyword', $searchTerm);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPeminjamById($id) {
        $query = "SELECT *, id_peminjam as id FROM " . $this->table_name . " WHERE id_peminjam = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPeminjamByUserId($userId) {
        try {
            $query = "SELECT *, id_peminjam as id FROM " . $this->table_name . " WHERE id_user = :userId LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res) return $res;
        } catch (Exception $e) {}
        return null;
    }

    public function getPeminjamByNama($nama) {
        $query = "SELECT *, id_peminjam as id FROM " . $this->table_name . " WHERE nama = :nama ORDER BY id_peminjam DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nama', $nama);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPeminjamByNis($nis) {
        $query = "SELECT *, id_peminjam as id FROM " . $this->table_name . " WHERE nis = :nis ORDER BY id_peminjam DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nis', $nis);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createPeminjam($nama, $nis, $kelas, $jurusan, $no_telp = '', $foto_kartu_pelajar = '', $id_user = null) {
        $colFoto = 'foto_kartu_pelajar';
        try {
            $chk = $this->conn->query("SHOW COLUMNS FROM " . $this->table_name . " LIKE 'foto'");
            if ($chk && $chk->rowCount() > 0) {
                $colFoto = 'foto';
            }
        } catch (Exception $e) {}

        try {
            $query = "INSERT INTO " . $this->table_name . " (id_user, nama, nis, kelas, jurusan, no_telp, `{$colFoto}`) 
                      VALUES (:id_user, :nama, :nis, :kelas, :jurusan, :no_telp, :foto)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
            $stmt->bindParam(':nama', $nama);
            $stmt->bindParam(':nis', $nis);
            $stmt->bindParam(':kelas', $kelas);
            $stmt->bindParam(':jurusan', $jurusan);
            $stmt->bindParam(':no_telp', $no_telp);
            $stmt->bindParam(':foto', $foto_kartu_pelajar);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (Exception $e) {
            $query = "INSERT INTO " . $this->table_name . " (nama, nis, kelas, jurusan, no_telp, `{$colFoto}`) 
                      VALUES (:nama, :nis, :kelas, :jurusan, :no_telp, :foto)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nama', $nama);
            $stmt->bindParam(':nis', $nis);
            $stmt->bindParam(':kelas', $kelas);
            $stmt->bindParam(':jurusan', $jurusan);
            $stmt->bindParam(':no_telp', $no_telp);
            $stmt->bindParam(':foto', $foto_kartu_pelajar);
            $stmt->execute();
            return $this->conn->lastInsertId();
        }
    }

    public function updatePeminjam($id, $nama, $nis, $kelas, $jurusan, $no_telp = '', $foto_kartu_pelajar = null) {
        if ($foto_kartu_pelajar !== null) {
            $query = "UPDATE " . $this->table_name . " 
                      SET nama = :nama, nis = :nis, kelas = :kelas, jurusan = :jurusan, 
                          no_telp = :no_telp, foto_kartu_pelajar = :foto_kartu_pelajar 
                      WHERE id_peminjam = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':foto_kartu_pelajar', $foto_kartu_pelajar);
        } else {
            $query = "UPDATE " . $this->table_name . " 
                      SET nama = :nama, nis = :nis, kelas = :kelas, jurusan = :jurusan, no_telp = :no_telp 
                      WHERE id_peminjam = :id";
            $stmt = $this->conn->prepare($query);
        }

        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':nis', $nis);
        $stmt->bindParam(':kelas', $kelas);
        $stmt->bindParam(':jurusan', $jurusan);
        $stmt->bindParam(':no_telp', $no_telp);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deletePeminjam($id) {
        $pk = 'id_peminjam';
        try {
            $chk = $this->conn->query("SHOW COLUMNS FROM " . $this->table_name . " LIKE 'id_peminjam'");
            if (!$chk || $chk->rowCount() === 0) {
                $pk = 'id';
            }

            $stmtPinjam = $this->conn->prepare("SELECT id_peminjaman FROM peminjaman WHERE id_peminjam = :id");
            $stmtPinjam->execute([':id' => $id]);
            $pinjamIds = $stmtPinjam->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($pinjamIds)) {
                $inClause = implode(',', array_map('intval', $pinjamIds));
                $this->conn->exec("DELETE FROM pengembalian WHERE id_peminjaman IN ($inClause)");
                $this->conn->exec("DELETE FROM notifikasi WHERE id_peminjaman IN ($inClause)");
                $this->conn->exec("DELETE FROM detail_peminjaman WHERE id_peminjaman IN ($inClause)");
                $this->conn->exec("DELETE FROM peminjaman WHERE id_peminjaman IN ($inClause)");
            }

            $this->conn->exec("DELETE FROM notifikasi WHERE id_peminjam = " . (int)$id);
        } catch (Exception $e) {}

        $query = "DELETE FROM " . $this->table_name . " WHERE `{$pk}` = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
