<?php

require_once __DIR__ . '/Database.php';

class Kategori {
    private $conn;
    private $table_name = "kategori";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllKategori($keyword = null) {
        $query = "SELECT k.*, k.id_kategori as id, 
                  (SELECT COUNT(*) FROM alat a WHERE a.id_kategori = k.id_kategori) as total_alat 
                  FROM " . $this->table_name . " k";

        if (!empty($keyword)) {
            $query .= " WHERE k.nama_kategori LIKE :keyword";
        }

        $query .= " ORDER BY k.id_kategori DESC";
        $stmt = $this->conn->prepare($query);

        if (!empty($keyword)) {
            $searchTerm = "%" . $keyword . "%";
            $stmt->bindParam(':keyword', $searchTerm);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getKategoriById($id) {
        $query = "SELECT *, id_kategori as id FROM " . $this->table_name . " WHERE id_kategori = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createKategori($nama_kategori) {
        $query = "INSERT INTO " . $this->table_name . " (nama_kategori) VALUES (:nama_kategori)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nama_kategori', $nama_kategori);
        return $stmt->execute();
    }

    public function updateKategori($id, $nama_kategori) {
        $query = "UPDATE " . $this->table_name . " SET nama_kategori = :nama_kategori WHERE id_kategori = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nama_kategori', $nama_kategori);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteKategori($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_kategori = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
