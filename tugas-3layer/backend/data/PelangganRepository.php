<?php
class PelangganRepository {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Fitur 2 (Tampil pelanggan) & Fitur 5 (Pencarian berdasarkan nama atau email)
    public function getAll($search = "") {
        if (!empty($search)) {
            $sql = "SELECT * FROM pelanggan WHERE nama LIKE :search OR email LIKE :search ORDER BY id ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['search' => "%$search%"]);
        } else {
            $sql = "SELECT * FROM pelanggan ORDER BY id ASC";
            $stmt = $this->conn->query($sql);
        }
        return $stmt->fetchAll();
    }

    // Ambil 1 pelanggan berdasarkan ID
    public function findById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM pelanggan WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    // Query pembantu untuk Fitur 6 (Memeriksa keunikan email)
    public function findByEmail($email, $excludeId = null) {
        $sql = "SELECT id FROM pelanggan WHERE email = :email";
        $params = ['email' => $email];

        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    // Fitur 1 (Tambah pelanggan)
    public function create($data) {
        $sql = "INSERT INTO pelanggan (nama, telepon, email, alamat) VALUES (:nama, :telepon, :email, :alamat)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'nama'    => $data['nama'],
            'telepon' => $data['telepon'],
            'email'   => $data['email'],
            'alamat'  => $data['alamat']
        ]);
    }

    // Fitur 3 (Edit pelanggan)
    public function update($id, $data) {
        $sql = "UPDATE pelanggan SET nama = :nama, telepon = :telepon, email = :email, alamat = :alamat WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'id'      => $id,
            'nama'    => $data['nama'],
            'telepon' => $data['telepon'],
            'email'   => $data['email'],
            'alamat'  => $data['alamat']
        ]);
    }

    // Fitur 4 (Hapus pelanggan)
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM pelanggan WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
