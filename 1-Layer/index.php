<?php
/*
 * APLIKASI CRUD MAHASISWA - NON-LAYERED ARCHITECTURE
 * Seluruh komponen berada dalam SATU file:
 *   1. Koneksi database
 *   2. Proses bisnis (validasi + query SQL)
 *   3. Tampilan (HTML)
 * Tidak ada pemisahan layer (presentation / business / data access).
 */

// ===== 1. KONEKSI DATABASE (langsung di file aplikasi) =====
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli("localhost", "root", "", "db_akademik");
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Koneksi database gagal: " . htmlspecialchars($e->getMessage()));
}

// Fungsi bantu: 2 digit pertama NIM => tahun angkatan (22 => 2022). Mengembalikan 0 jika tidak valid.
function angkatanDariNim($nim) {
    if (preg_match('/^(\d{2})/', $nim, $m)) {
        return 2000 + (int)$m[1];
    }
    return 0;
}

$aksi   = $_GET['aksi'] ?? 'list';
$pesan  = $_GET['pesan'] ?? '';
$error  = '';
$data   = ['id' => '', 'nim' => '', 'nama' => '', 'email' => '', 'jurusan' => '', 'angkatan' => date('Y')];

// ===== 2. PROSES BISNIS & QUERY (CREATE, UPDATE, DELETE) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act      = $_POST['aksi'] ?? '';
    $id       = (int)($_POST['id'] ?? 0);
    $nim      = trim($_POST['nim'] ?? '');
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $jurusan  = trim($_POST['jurusan'] ?? '');
    // Angkatan diturunkan otomatis dari 2 digit pertama NIM (22xxxxx => 2022)
    $angkatan = angkatanDariNim($nim);

    if ($act === 'hapus') {
        $stmt = $conn->prepare("DELETE FROM mahasiswa WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: index.php?pesan=" . urlencode("Data berhasil dihapus."));
        exit;
    }

    if ($act === 'tambah' || $act === 'ubah') {
        // Validasi (logika bisnis)
        if ($nim === '' || $nama === '' || $email === '' || $jurusan === '') {
            $error = "Semua field wajib diisi.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Format email tidak valid.";
        } elseif ($angkatan === 0) {
            $error = "NIM harus diawali 2 digit angka tahun angkatan (contoh: 22 untuk 2022).";
        } elseif ($angkatan > (int)date('Y')) {
            $error = "Angkatan dari NIM ($angkatan) melebihi tahun sekarang.";
        }

        if ($error === '') {
            try {
                if ($act === 'tambah') {
                    $stmt = $conn->prepare("INSERT INTO mahasiswa (nim, nama, email, jurusan, angkatan) VALUES (?,?,?,?,?)");
                    $stmt->bind_param("ssssi", $nim, $nama, $email, $jurusan, $angkatan);
                    $stmt->execute();
                    header("Location: index.php?pesan=" . urlencode("Data berhasil ditambahkan."));
                } else {
                    $stmt = $conn->prepare("UPDATE mahasiswa SET nim=?, nama=?, email=?, jurusan=?, angkatan=? WHERE id=?");
                    $stmt->bind_param("ssssii", $nim, $nama, $email, $jurusan, $angkatan, $id);
                    $stmt->execute();
                    header("Location: index.php?pesan=" . urlencode("Data berhasil diperbarui."));
                }
                exit;
            } catch (mysqli_sql_exception $e) {
                $error = ($e->getCode() == 1062) ? "NIM sudah terdaftar." : "Kesalahan database: " . $e->getMessage();
            }
        }
        // Jika error, kembalikan input ke form
        $data = ['id' => $id, 'nim' => $nim, 'nama' => $nama, 'email' => $email, 'jurusan' => $jurusan, 'angkatan' => $angkatan];
        $aksi = ($act === 'tambah') ? 'tambah' : 'edit';
    }
}

// Ambil data untuk form edit (READ satu baris)
if ($aksi === 'edit' && $error === '') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) {
        header("Location: index.php?pesan=" . urlencode("Data tidak ditemukan."));
        exit;
    }
    $data = $row;
}

// Ambil daftar data (READ semua + pencarian)
$cari = trim($_GET['cari'] ?? '');
if ($cari !== '') {
    $like = "%$cari%";
    $stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE nim LIKE ? OR nama LIKE ? OR jurusan LIKE ? ORDER BY id DESC");
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $hasil = $stmt->get_result();
} else {
    $hasil = $conn->query("SELECT * FROM mahasiswa ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>CRUD Mahasiswa - Non-Layered</title>
<style>
  body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; padding:24px; color:#222; }
  .box { max-width:900px; margin:auto; background:#fff; padding:24px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.08); }
  h1 { margin-top:0; font-size:22px; }
  table { width:100%; border-collapse:collapse; margin-top:16px; }
  th, td { padding:10px; border-bottom:1px solid #e3e6ea; text-align:left; font-size:14px; }
  th { background:#f0f3f7; }
  .btn { display:inline-block; padding:7px 14px; border:0; border-radius:4px; cursor:pointer; text-decoration:none; font-size:13px; color:#fff; background:#2563eb; }
  .btn.edit { background:#d97706; } .btn.hapus { background:#dc2626; } .btn.abu { background:#6b7280; }
  .alert { padding:10px 14px; border-radius:4px; margin:12px 0; }
  .ok { background:#dcfce7; color:#166534; } .err { background:#fee2e2; color:#991b1b; }
  label { display:block; margin-top:12px; font-weight:bold; font-size:14px; }
  input[type=text], input[type=email], input[type=number] { width:100%; padding:9px; border:1px solid #cbd5e1; border-radius:4px; box-sizing:border-box; }
  .bar { display:flex; gap:8px; justify-content:space-between; flex-wrap:wrap; }
  form.inline { display:inline; }
</style>
</head>
<body>
<div class="box">
  <h1>Data Mahasiswa </h1>

  <?php if ($pesan): ?><div class="alert ok"><?= htmlspecialchars($pesan) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert err"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <?php if ($aksi === 'tambah' || $aksi === 'edit'): ?>
    <!-- ===== 3. TAMPILAN: FORM TAMBAH / EDIT ===== -->
    <h2><?= $aksi === 'tambah' ? 'Tambah Mahasiswa' : 'Ubah Mahasiswa' ?></h2>
    <form method="post" action="index.php">
      <input type="hidden" name="aksi" value="<?= $aksi === 'tambah' ? 'tambah' : 'ubah' ?>">
      <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">

      <label>NIM</label>
      <input type="text" name="nim" value="<?= htmlspecialchars($data['nim']) ?>" required>
      <label>Nama</label>
      <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required>
      <label>Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>" required>
      <label>Jurusan</label>
      <input type="text" name="jurusan" value="<?= htmlspecialchars($data['jurusan']) ?>" required>

      <p>
        <button type="submit" class="btn">Simpan</button>
        <a href="index.php" class="btn abu">Batal</a>
      </p>
    </form>

  <?php else: ?>
    <!-- ===== 3. TAMPILAN: DAFTAR DATA ===== -->
    <div class="bar">
      <form method="get" action="index.php">
        <input type="text" name="cari" placeholder="Cari NIM / nama / jurusan" value="<?= htmlspecialchars($cari) ?>" style="width:240px;display:inline-block">
        <button class="btn" type="submit">Cari</button>
        <?php if ($cari !== ''): ?><a href="index.php" class="btn abu">Reset</a><?php endif; ?>
      </form>
      <a href="index.php?aksi=tambah" class="btn">+ Tambah Data</a>
    </div>

    <table>
      <tr><th>No</th><th>NIM</th><th>Nama</th><th>Email</th><th>Jurusan</th><th>Angkatan</th><th>Aksi</th></tr>
      <?php $no = 1; while ($r = $hasil->fetch_assoc()): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($r['nim']) ?></td>
          <td><?= htmlspecialchars($r['nama']) ?></td>
          <td><?= htmlspecialchars($r['email']) ?></td>
          <td><?= htmlspecialchars($r['jurusan']) ?></td>
          <td><?= angkatanDariNim($r['nim']) ?: htmlspecialchars($r['angkatan']) ?></td>
          <td>
            <a class="btn edit" href="index.php?aksi=edit&id=<?= (int)$r['id'] ?>">Edit</a>
            <form class="inline" method="post" action="index.php" onsubmit="return confirm('Hapus data ini?')">
              <input type="hidden" name="aksi" value="hapus">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn hapus" type="submit">Hapus</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
      <?php if ($no === 1): ?><tr><td colspan="7" style="text-align:center;color:#6b7280">Belum ada data.</td></tr><?php endif; ?>
    </table>
  <?php endif; ?>
</div>
</body>
</html>