<?php

require_once __DIR__ . '/Database.php';

class Peminjaman {
    private $conn;
    private $table_name = "peminjaman";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function generateKodePeminjaman() {
        $prefix = "PMJ-" . date('Ymd') . "-";
        try {
            $query = "SELECT kode_peminjaman FROM " . $this->table_name . " 
                      WHERE kode_peminjaman LIKE :prefix 
                      ORDER BY id_peminjaman DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $p = $prefix . "%";
            $stmt->bindParam(':prefix', $p);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $lastNum = (int)substr($row['kode_peminjaman'], -4);
                $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNum = "0001";
            }
            return $prefix . $newNum;
        } catch (Exception $e) {
            return $prefix . rand(1000, 9999);
        }
    }

    public function getDetailAlatByPeminjamanId($idPeminjaman) {
        try {
            $query = "SELECT dp.*, a.nama_alat, a.kode as kode_alat, a.jumlah as stok_alat, a.kondisi as kondisi_alat, k.nama_kategori
                      FROM detail_peminjaman dp
                      JOIN alat a ON dp.id_alat = a.id_alat
                      LEFT JOIN kategori k ON a.id_kategori = k.id_kategori
                      WHERE dp.id_peminjaman = :id_peminjaman";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_peminjaman', $idPeminjaman, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    private function attachDetailsToPeminjamanList(array $peminjamanList) {
        if (empty($peminjamanList)) return [];

        $ids = array_filter(array_map('intval', array_column($peminjamanList, 'id')));
        if (empty($ids)) return $peminjamanList;

        $inClause = implode(',', array_unique($ids));
        try {
            $stmtItems = $this->conn->query("
                SELECT dp.*, a.nama_alat, a.kode as kode_alat, k.nama_kategori
                FROM detail_peminjaman dp
                JOIN alat a ON dp.id_alat = a.id_alat
                LEFT JOIN kategori k ON a.id_kategori = k.id_kategori
                WHERE dp.id_peminjaman IN ($inClause)
            ");
            $allDetails = $stmtItems ? $stmtItems->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            $allDetails = [];
        }

        $itemsByPeminjaman = [];
        foreach ($allDetails as $d) {
            $itemsByPeminjaman[$d['id_peminjaman']][] = $d;
        }

        foreach ($peminjamanList as &$r) {
            $pId = (int)$r['id'];
            $items = $itemsByPeminjaman[$pId] ?? [];
            $r['items'] = $items;
            if (!empty($items)) {
                $r['id_alat'] = $items[0]['id_alat'];
                $r['kode_alat'] = $items[0]['kode_alat'];
                $r['nama_kategori'] = $items[0]['nama_kategori'];

                $totalJml = 0;
                $namaList = [];
                foreach ($items as $it) {
                    $totalJml += (int)$it['jumlah'];
                    $namaList[] = $it['nama_alat'] . ' (' . $it['jumlah'] . ' unit)';
                }
                $r['jumlah'] = $totalJml;
                $r['total_jumlah'] = $totalJml;
                $r['nama_alat'] = implode(', ', $namaList);
                $r['ringkasan_alat'] = implode(', ', $namaList);
            } else {
                $r['id_alat'] = null;
                $r['nama_alat'] = '-';
                $r['jumlah'] = 0;
                $r['total_jumlah'] = 0;
                $r['ringkasan_alat'] = '-';
            }
        }
        unset($r);

        return $peminjamanList;
    }

    public function getAllPeminjaman($keyword = null, $statusFilter = null) {
        $query = "SELECT p.*, p.id_peminjaman as id,
                         pm.nama as nama_peminjam, pm.nis, pm.kelas, pm.jurusan, pm.no_telp, pm.foto_kartu_pelajar,
                         u.nama as nama_petugas
                  FROM " . $this->table_name . " p
                  LEFT JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam
                  LEFT JOIN user u ON p.id_user = u.id_user
                  WHERE 1=1";

        if (!empty($statusFilter)) {
            $query .= " AND p.status = :statusFilter";
        }

        if (!empty($keyword)) {
            $query .= " AND (p.kode_peminjaman LIKE :keyword 
                        OR pm.nama LIKE :keyword 
                        OR pm.nis LIKE :keyword 
                        OR p.keperluan LIKE :keyword
                        OR p.id_peminjaman IN (
                            SELECT dp_sub.id_peminjaman 
                            FROM detail_peminjaman dp_sub 
                            JOIN alat a_sub ON dp_sub.id_alat = a_sub.id_alat 
                            WHERE a_sub.nama_alat LIKE :keyword OR a_sub.kode LIKE :keyword
                        ))";
        }

        $query .= " ORDER BY p.id_peminjaman DESC";

        $stmt = $this->conn->prepare($query);

        if (!empty($statusFilter)) {
            $stmt->bindParam(':statusFilter', $statusFilter);
        }

        if (!empty($keyword)) {
            $searchTerm = "%" . $keyword . "%";
            $stmt->bindParam(':keyword', $searchTerm);
        }

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->attachDetailsToPeminjamanList($rows);
    }

    public function getPeminjamanById($id) {
        $query = "SELECT p.*, p.id_peminjaman as id,
                         pm.nama as nama_peminjam, pm.nis, pm.kelas, pm.jurusan, pm.no_telp, pm.foto_kartu_pelajar,
                         u.nama as nama_petugas
                  FROM " . $this->table_name . " p
                  LEFT JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam
                  LEFT JOIN user u ON p.id_user = u.id_user
                  WHERE p.id_peminjaman = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;

        $list = $this->attachDetailsToPeminjamanList([$row]);
        return $list[0];
    }

    public function createPeminjaman($id_peminjam, $id_user, $waktu_pinjam, $waktu_rencana_kembali, $jenis_peminjaman, $keperluan, $id_alat, $jumlah = 1, $status = 'menunggu', $items = []) {
        $kode_peminjaman = $this->generateKodePeminjaman();

        $itemList = [];
        if (!empty($items) && is_array($items)) {
            $itemList = $items;
        } elseif (is_array($id_alat)) {
            $itemList = $id_alat;
        } elseif (!empty($id_alat)) {
            $itemList = [['id_alat' => (int)$id_alat, 'jumlah' => max(1, (int)$jumlah)]];
        }

        try {
            $colInfo = $this->conn->query("SHOW COLUMNS FROM " . $this->table_name . " LIKE 'jenis_peminjaman'")->fetch();
            if ($colInfo && strpos(strtolower($colInfo['Type'] ?? ''), 'varchar(30)') !== false) {
                $this->conn->exec("ALTER TABLE " . $this->table_name . " MODIFY COLUMN `jenis_peminjaman` VARCHAR(100) NOT NULL DEFAULT 'Praktek Lab'");
            }
        } catch (Exception $e) {}

        if (mb_strlen($jenis_peminjaman) > 100) {
            $jenis_peminjaman = mb_substr($jenis_peminjaman, 0, 100);
        }

        $this->conn->beginTransaction();
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      (kode_peminjaman, id_peminjam, id_user, waktu_pinjam, waktu_rencana_kembali, jenis_peminjaman, keperluan, status) 
                      VALUES (:kode, :id_peminjam, :id_user, :waktu_pinjam, :waktu_rencana, :jenis, :keperluan, :status)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':kode', $kode_peminjaman);
            $stmt->bindParam(':id_peminjam', $id_peminjam, PDO::PARAM_INT);
            if (!empty($id_user) && (int)$id_user > 0) {
                $stmt->bindValue(':id_user', (int)$id_user, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':id_user', null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(':waktu_pinjam', $waktu_pinjam);
            $stmt->bindParam(':waktu_rencana', $waktu_rencana_kembali);
            $stmt->bindParam(':jenis', $jenis_peminjaman);
            $stmt->bindParam(':keperluan', $keperluan);
            $stmt->bindParam(':status', $status);
            $stmt->execute();

            $peminjamanId = $this->conn->lastInsertId();

            foreach ($itemList as $it) {
                $itAlat = (int)($it['id_alat'] ?? 0);
                $itJml  = max(1, (int)($it['jumlah'] ?? 1));
                if ($itAlat <= 0) continue;

                $queryDetail = "INSERT INTO detail_peminjaman (id_peminjaman, id_alat, jumlah) 
                                VALUES (:id_peminjaman, :id_alat, :jumlah)";
                $stmtDetail = $this->conn->prepare($queryDetail);
                $stmtDetail->bindParam(':id_peminjaman', $peminjamanId, PDO::PARAM_INT);
                $stmtDetail->bindParam(':id_alat', $itAlat, PDO::PARAM_INT);
                $stmtDetail->bindParam(':jumlah', $itJml, PDO::PARAM_INT);
                $stmtDetail->execute();

                if (in_array(strtolower($status), ['disetujui', 'dipinjam'])) {
                    $queryStock = "UPDATE alat SET jumlah = GREATEST(0, jumlah - :jumlah) WHERE id_alat = :id_alat";
                    $stmtStock = $this->conn->prepare($queryStock);
                    $stmtStock->bindParam(':jumlah', $itJml, PDO::PARAM_INT);
                    $stmtStock->bindParam(':id_alat', $itAlat, PDO::PARAM_INT);
                    $stmtStock->execute();
                }
            }

            $this->conn->commit();
            return $peminjamanId;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function updatePeminjaman($id, $id_peminjam, $waktu_pinjam, $waktu_rencana_kembali, $jenis_peminjaman, $keperluan, $status, $id_alat = null, $jumlah = null, $items = []) {
        if (mb_strlen($jenis_peminjaman) > 100) {
            $jenis_peminjaman = mb_substr($jenis_peminjaman, 0, 100);
        }

        $borrowedStatuses = ['disetujui', 'dipinjam', 'menunggu_pengembalian', 'menunggu_validasi'];
        $current = $this->getPeminjamanById($id);
        $oldStatus = strtolower($current['status'] ?? 'menunggu');
        $newStatus = strtolower($status);

        $this->conn->beginTransaction();
        try {
            if (in_array($oldStatus, $borrowedStatuses)) {
                $oldItems = $this->getDetailAlatByPeminjamanId($id);
                foreach ($oldItems as $od) {
                    $st = $this->conn->prepare("UPDATE alat SET jumlah = jumlah + :jml WHERE id_alat = :id_alat");
                    $st->bindParam(':jml', $od['jumlah'], PDO::PARAM_INT);
                    $st->bindParam(':id_alat', $od['id_alat'], PDO::PARAM_INT);
                    $st->execute();
                }
            }

            $query = "UPDATE " . $this->table_name . " 
                      SET id_peminjam = :id_peminjam, waktu_pinjam = :waktu_pinjam, 
                          waktu_rencana_kembali = :waktu_rencana, jenis_peminjaman = :jenis, 
                          keperluan = :keperluan, status = :status 
                      WHERE id_peminjaman = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_peminjam', $id_peminjam, PDO::PARAM_INT);
            $stmt->bindParam(':waktu_pinjam', $waktu_pinjam);
            $stmt->bindParam(':waktu_rencana', $waktu_rencana_kembali);
            $stmt->bindParam(':jenis', $jenis_peminjaman);
            $stmt->bindParam(':keperluan', $keperluan);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if (!empty($items) && is_array($items)) {
                $del = $this->conn->prepare("DELETE FROM detail_peminjaman WHERE id_peminjaman = :id");
                $del->bindParam(':id', $id, PDO::PARAM_INT);
                $del->execute();

                foreach ($items as $it) {
                    $itAlat = (int)($it['id_alat'] ?? 0);
                    $itJml  = max(1, (int)($it['jumlah'] ?? 1));
                    if ($itAlat <= 0) continue;
                    $ins = $this->conn->prepare("INSERT INTO detail_peminjaman (id_peminjaman, id_alat, jumlah) VALUES (:id, :id_alat, :jml)");
                    $ins->bindParam(':id', $id, PDO::PARAM_INT);
                    $ins->bindParam(':id_alat', $itAlat, PDO::PARAM_INT);
                    $ins->bindParam(':jml', $itJml, PDO::PARAM_INT);
                    $ins->execute();
                }
            } elseif ($id_alat !== null && $jumlah !== null) {
                $chk = $this->conn->prepare("SELECT COUNT(*) FROM detail_peminjaman WHERE id_peminjaman = :id");
                $chk->bindParam(':id', $id, PDO::PARAM_INT);
                $chk->execute();
                if ($chk->fetchColumn() > 0) {
                    $queryDetail = "UPDATE detail_peminjaman 
                                    SET id_alat = :id_alat, jumlah = :jumlah 
                                    WHERE id_peminjaman = :id LIMIT 1";
                } else {
                    $queryDetail = "INSERT INTO detail_peminjaman (id_peminjaman, id_alat, jumlah) VALUES (:id, :id_alat, :jumlah)";
                }
                $stmtDetail = $this->conn->prepare($queryDetail);
                $stmtDetail->bindParam(':id_alat', $id_alat, PDO::PARAM_INT);
                $stmtDetail->bindParam(':jumlah', $jumlah, PDO::PARAM_INT);
                $stmtDetail->bindParam(':id', $id, PDO::PARAM_INT);
                $stmtDetail->execute();
            }

            if (in_array($newStatus, $borrowedStatuses)) {
                $newItems = $this->getDetailAlatByPeminjamanId($id);
                foreach ($newItems as $nd) {
                    $st = $this->conn->prepare("UPDATE alat SET jumlah = GREATEST(0, jumlah - :jml) WHERE id_alat = :id_alat");
                    $st->bindParam(':jml', $nd['jumlah'], PDO::PARAM_INT);
                    $st->bindParam(':id_alat', $nd['id_alat'], PDO::PARAM_INT);
                    $st->execute();
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function updateStatus($id, $status, $id_user = null) {
        $current = $this->getPeminjamanById($id);
        if (!$current) return false;

        $this->conn->beginTransaction();
        try {
            $oldStatus = strtolower($current['status']);
            $newStatus = strtolower($status);

            $query = "UPDATE " . $this->table_name . " SET status = :status";
            if ($id_user !== null) {
                $query .= ", id_user = :id_user";
            }
            $query .= " WHERE id_peminjaman = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            if ($id_user !== null) {
                $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $borrowedStatuses = ['disetujui', 'dipinjam', 'menunggu_pengembalian', 'menunggu_validasi'];
            $items = $this->getDetailAlatByPeminjamanId($id);

            if (!in_array($oldStatus, $borrowedStatuses) && in_array($newStatus, $borrowedStatuses)) {
                foreach ($items as $it) {
                    $st = $this->conn->prepare("UPDATE alat SET jumlah = GREATEST(0, jumlah - :jml) WHERE id_alat = :id_alat");
                    $st->bindParam(':jml', $it['jumlah'], PDO::PARAM_INT);
                    $st->bindParam(':id_alat', $it['id_alat'], PDO::PARAM_INT);
                    $st->execute();
                }
            } elseif (in_array($oldStatus, $borrowedStatuses) && in_array($newStatus, ['ditolak', 'dikembalikan', 'dibatalkan'])) {
                foreach ($items as $it) {
                    $st = $this->conn->prepare("UPDATE alat SET jumlah = jumlah + :jml WHERE id_alat = :id_alat");
                    $st->bindParam(':jml', $it['jumlah'], PDO::PARAM_INT);
                    $st->bindParam(':id_alat', $it['id_alat'], PDO::PARAM_INT);
                    $st->execute();
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function deletePeminjaman($id) {
        $current = $this->getPeminjamanById($id);
        if (!$current) return false;

        $this->conn->beginTransaction();
        try {
            if (in_array(strtolower($current['status']), ['disetujui', 'dipinjam', 'menunggu_pengembalian', 'menunggu_validasi'])) {
                $items = $this->getDetailAlatByPeminjamanId($id);
                foreach ($items as $it) {
                    $st = $this->conn->prepare("UPDATE alat SET jumlah = jumlah + :jml WHERE id_alat = :id_alat");
                    $st->bindParam(':jml', $it['jumlah'], PDO::PARAM_INT);
                    $st->bindParam(':id_alat', $it['id_alat'], PDO::PARAM_INT);
                    $st->execute();
                }
            }

            try {
                $delPengembalian = $this->conn->prepare("DELETE FROM pengembalian WHERE id_peminjaman = :id");
                $delPengembalian->bindParam(':id', $id, PDO::PARAM_INT);
                $delPengembalian->execute();

                $delNotif = $this->conn->prepare("DELETE FROM notifikasi WHERE id_peminjaman = :id");
                $delNotif->bindParam(':id', $id, PDO::PARAM_INT);
                $delNotif->execute();
            } catch (Exception $e) {}

            $delDetail = $this->conn->prepare("DELETE FROM detail_peminjaman WHERE id_peminjaman = :id");
            $delDetail->bindParam(':id', $id, PDO::PARAM_INT);
            $delDetail->execute();

            $del = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id_peminjaman = :id");
            $del->bindParam(':id', $id, PDO::PARAM_INT);
            $del->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function getActivePeminjaman() {
        $query = "SELECT p.*, p.id_peminjaman as id,
                         pm.nama as nama_peminjam, pm.nis, pm.kelas, pm.jurusan, pm.no_telp,
                         u.nama as nama_petugas
                  FROM " . $this->table_name . " p
                  LEFT JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam
                  LEFT JOIN user u ON p.id_user = u.id_user
                  WHERE p.status IN ('disetujui', 'dipinjam', 'menunggu_pengembalian', 'menunggu_validasi')
                  ORDER BY p.id_peminjaman DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->attachDetailsToPeminjamanList($rows);
    }

    public function getMenungguPengembalian($keyword = null) {
        $query = "SELECT p.*, p.id_peminjaman as id,
                         pm.nama as nama_peminjam, pm.nis, pm.kelas, pm.jurusan, pm.no_telp, pm.foto_kartu_pelajar,
                         u.nama as nama_petugas
                  FROM " . $this->table_name . " p
                  JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam
                  LEFT JOIN user u ON p.id_user = u.id_user
                  WHERE p.status IN ('menunggu_pengembalian', 'menunggu_validasi')";

        if (!empty($keyword)) {
            $query .= " AND (p.kode_peminjaman LIKE :keyword 
                        OR pm.nama LIKE :keyword 
                        OR pm.nis LIKE :keyword 
                        OR p.id_peminjaman IN (
                            SELECT dp_sub.id_peminjaman 
                            FROM detail_peminjaman dp_sub 
                            JOIN alat a_sub ON dp_sub.id_alat = a_sub.id_alat 
                            WHERE a_sub.nama_alat LIKE :keyword OR a_sub.kode LIKE :keyword
                        ))";
        }

        $query .= " ORDER BY p.id_peminjaman DESC";
        $stmt = $this->conn->prepare($query);

        if (!empty($keyword)) {
            $searchTerm = "%" . $keyword . "%";
            $stmt->bindParam(':keyword', $searchTerm);
        }

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->attachDetailsToPeminjamanList($rows);
    }

    public function ajukanPengembalianByPeminjam($id, $idPeminjam) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'menunggu_validasi' 
                  WHERE id_peminjaman = :id AND id_peminjam = :idPeminjam AND status IN ('disetujui', 'dipinjam')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':idPeminjam', $idPeminjam, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getPeminjamanByPeminjamId($idPeminjam, $keyword = null, $statusFilter = null) {
        $query = "SELECT p.*, p.id_peminjaman as id,
                         pm.nama as nama_peminjam, pm.nis, pm.kelas, pm.jurusan,
                         u.nama as nama_petugas
                  FROM " . $this->table_name . " p
                  JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam
                  LEFT JOIN user u ON p.id_user = u.id_user
                  WHERE p.id_peminjam = :idPeminjam";

        if (!empty($statusFilter)) {
            $query .= " AND p.status = :statusFilter";
        }

        if (!empty($keyword)) {
            $query .= " AND (p.kode_peminjaman LIKE :keyword 
                        OR p.keperluan LIKE :keyword
                        OR p.id_peminjaman IN (
                            SELECT dp_sub.id_peminjaman 
                            FROM detail_peminjaman dp_sub 
                            JOIN alat a_sub ON dp_sub.id_alat = a_sub.id_alat 
                            WHERE a_sub.nama_alat LIKE :keyword OR a_sub.kode LIKE :keyword
                        ))";
        }

        $query .= " ORDER BY p.id_peminjaman DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idPeminjam', $idPeminjam, PDO::PARAM_INT);

        if (!empty($statusFilter)) {
            $stmt->bindParam(':statusFilter', $statusFilter);
        }

        if (!empty($keyword)) {
            $searchTerm = "%" . $keyword . "%";
            $stmt->bindParam(':keyword', $searchTerm);
        }

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->attachDetailsToPeminjamanList($rows);
    }

    public function countPeminjamPeminjamanByStatus($idPeminjam, $status = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE id_peminjam = :idPeminjam";
        if (!empty($status)) {
            if (is_array($status)) {
                $in = "'" . implode("','", array_map('addslashes', $status)) . "'";
                $query .= " AND status IN ($in)";
            } else {
                $query .= " AND status = :status";
            }
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idPeminjam', $idPeminjam, PDO::PARAM_INT);
        if (!empty($status) && !is_array($status)) {
            $stmt->bindParam(':status', $status);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public function batalkanPeminjamanByPeminjam($id, $idPeminjam) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'dibatalkan' 
                  WHERE id_peminjaman = :id AND id_peminjam = :idPeminjam AND status = 'menunggu'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':idPeminjam', $idPeminjam, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function countLatePeminjaman() {
        try {
            $currentTime = date('H:i:s');
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                       WHERE status IN ('disetujui', 'dipinjam', 'menunggu_pengembalian', 'menunggu_validasi') 
                       AND waktu_rencana_kembali < :currentTime";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':currentTime', $currentTime);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($res['total'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getExecutivePeminjaman($startDate = null, $endDate = null, $kelas = null, $idAlat = null, $keyword = null) {
        $query = "SELECT p.*, p.id_peminjaman as id,
                         pm.nama as nama_peminjam, pm.nis, pm.kelas, pm.jurusan, pm.no_telp,
                         u.nama as nama_petugas
                  FROM " . $this->table_name . " p
                  JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam
                  LEFT JOIN user u ON p.id_user = u.id_user
                  WHERE 1=1";

        if (!empty($kelas)) {
            $query .= " AND pm.kelas = :kelas";
        }
        if (!empty($idAlat)) {
            $query .= " AND p.id_peminjaman IN (SELECT dp_sub.id_peminjaman FROM detail_peminjaman dp_sub WHERE dp_sub.id_alat = :idAlat)";
        }
        if (!empty($keyword)) {
            $query .= " AND (p.kode_peminjaman LIKE :keyword OR pm.nama LIKE :keyword OR p.id_peminjaman IN (SELECT dp_sub.id_peminjaman FROM detail_peminjaman dp_sub JOIN alat a_sub ON dp_sub.id_alat = a_sub.id_alat WHERE a_sub.nama_alat LIKE :keyword))";
        }

        $query .= " ORDER BY p.id_peminjaman DESC";
        $stmt = $this->conn->prepare($query);

        if (!empty($kelas)) {
            $stmt->bindParam(':kelas', $kelas);
        }
        if (!empty($idAlat)) {
            $stmt->bindParam(':idAlat', $idAlat, PDO::PARAM_INT);
        }
        if (!empty($keyword)) {
            $searchTerm = "%" . $keyword . "%";
            $stmt->bindParam(':keyword', $searchTerm);
        }

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->attachDetailsToPeminjamanList($rows);
    }
}
