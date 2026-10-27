<?php

require_once __DIR__ . '/Database.php';

class Kategori {
    private $conn;
    private $table_name = "kategori";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    private $pkCol = null;

    private function getPkCol() {
        if ($this->pkCol === null) {
            $this->pkCol = 'id_kategori';
            try {
                $chk = $this->conn->query("SHOW COLUMNS FROM " . $this->table_name . " LIKE 'id_kategori'");
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

    public function getAllKategori($keyword = null) {
        $pk = $this->getPkCol();
        $query = "SELECT k.*, k.{$pk} as id, 
                  (SELECT COUNT(*) FROM alat a WHERE a.id_kategori = k.{$pk}) as total_alat 
                  FROM " . $this->table_name . " k";

        if (!empty($keyword)) {
            $query .= " WHERE k.nama_kategori LIKE :keyword";
        }

        $query .= " ORDER BY k.{$pk} DESC";
        $stmt = $this->conn->prepare($query);

        if (!empty($keyword)) {
            $searchTerm = "%" . $keyword . "%";
            $stmt->bindParam(':keyword', $searchTerm);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getKategoriById($id) {
        $pk = $this->getPkCol();
        $query = "SELECT *, {$pk} as id FROM " . $this->table_name . " WHERE {$pk} = :id LIMIT 1";
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
        $pk = $this->getPkCol();
        $query = "UPDATE " . $this->table_name . " SET nama_kategori = :nama_kategori WHERE {$pk} = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nama_kategori', $nama_kategori);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteKategori($id) {
        $pk = $this->getPkCol();

        try {
            $colAlatPk = 'id_alat';
            $chkAlatPk = $this->conn->query("SHOW COLUMNS FROM alat LIKE 'id_alat'");
            if (!$chkAlatPk || $chkAlatPk->rowCount() === 0) {
                $colAlatPk = 'id';
            }

            $stmtAlat = $this->conn->prepare("SELECT `{$colAlatPk}` FROM alat WHERE id_kategori = :id");
            $stmtAlat->execute([':id' => $id]);
            $alatIds = $stmtAlat->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($alatIds)) {
                $inClause = implode(',', array_map('intval', $alatIds));
                $this->conn->exec("DELETE FROM detail_peminjaman WHERE id_alat IN ($inClause)");
                $this->conn->exec("DELETE FROM alat WHERE id_kategori = " . (int)$id);
            }
        } catch (Exception $e) {}

        $query = "DELETE FROM " . $this->table_name . " WHERE {$pk} = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
