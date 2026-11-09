<?php
class PelangganService {
    private $repo;

    public function __construct(PelangganRepository $repo) {
        $this->repo = $repo;
    }

    // Fitur 6: Validasi kelengkapan dan keunikan email
    private function validate($data, $excludeId = null) {
        $nama    = trim($data['nama'] ?? '');
        $telepon = trim($data['telepon'] ?? '');
        $email   = trim($data['email'] ?? '');
        $alamat  = trim($data['alamat'] ?? '');

        if (empty($nama) || empty($telepon) || empty($email) || empty($alamat)) {
            return "Seluruh data (nama, telepon, email, alamat) wajib diisi.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Format email tidak valid.";
        }

        if ($this->repo->findByEmail($email, $excludeId)) {
            return "Email sudah terdaftar. Gunakan email lain.";
        }

        return null; // Lolos validasi
    }

    public function getCustomers($search = "") {
        $data = $this->repo->getAll($search);
        return ["status" => "success", "code" => 200, "data" => $data];
    }

    public function createCustomer($data) {
        $error = $this->validate($data);
        if ($error) {
            return ["status" => "error", "code" => 422, "message" => $error];
        }

        $this->repo->create($data);
        return ["status" => "success", "code" => 201, "message" => "Pelanggan berhasil ditambahkan."];
    }

    public function updateCustomer($id, $data) {
        if (empty($id) || !$this->repo->findById($id)) {
            return ["status" => "error", "code" => 404, "message" => "Data pelanggan tidak ditemukan."];
        }

        $error = $this->validate($data, $id);
        if ($error) {
            return ["status" => "error", "code" => 422, "message" => $error];
        }

        $this->repo->update($id, $data);
        return ["status" => "success", "code" => 200, "message" => "Data pelanggan berhasil diperbarui."];
    }

    public function deleteCustomer($id) {
        if (empty($id) || !$this->repo->findById($id)) {
            return ["status" => "error", "code" => 404, "message" => "Data pelanggan tidak ditemukan."];
        }

        $this->repo->delete($id);
        return ["status" => "success", "code" => 200, "message" => "Pelanggan berhasil dihapus."];
    }
}
