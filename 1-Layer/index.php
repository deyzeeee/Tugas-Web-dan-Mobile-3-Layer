<?php
/*
 * APLIKASI CRUD PELANGGAN - NON-LAYERED ARCHITECTURE
 * Seluruh komponen berada dalam SATU file:
 *   1. Koneksi database
 *   2. Proses bisnis (validasi + query SQL)
 *   3. Tampilan (HTML)
 * Tidak ada pemisahan layer (presentation / business / data access).
 */

// ===== 1. KONEKSI DATABASE (langsung di file aplikasi) =====
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli("localhost", "root", "", "db_pelanggan");
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Koneksi database gagal: " . htmlspecialchars($e->getMessage()));
}

$aksi   = $_GET['aksi'] ?? 'list';
$pesan  = $_GET['pesan'] ?? '';
$error  = '';
$data   = ['id' => '', 'nama' => '', 'email' => '', 'telepon' => '', 'alamat' => ''];

// ===== 2. PROSES BISNIS & QUERY (CREATE, UPDATE, DELETE) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act     = $_POST['aksi'] ?? '';
    $id      = (int)($_POST['id'] ?? 0);
    $nama    = trim($_POST['nama'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $alamat  = trim($_POST['alamat'] ?? '');

    if ($act === 'hapus') {
        $stmt = $conn->prepare("DELETE FROM pelanggan WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: index.php?pesan=" . urlencode("Data berhasil dihapus."));
        exit;
    }

    if ($act === 'tambah' || $act === 'ubah') {
        // Validasi (logika bisnis)
        $digitTelepon = preg_replace('/\D/', '', $telepon);
        if ($nama === '' || $email === '' || $telepon === '' || $alamat === '') {
            $error = "Semua field wajib diisi.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Format email tidak valid.";
        } elseif (!preg_match('/^\+?[0-9\s\-]+$/', $telepon) || strlen($digitTelepon) < 8 || strlen($digitTelepon) > 15) {
            $error = "Telepon tidak valid (8-15 digit, boleh diawali + dan memuat spasi atau tanda -).";
        }

        if ($error === '') {
            try {
                if ($act === 'tambah') {
                    $stmt = $conn->prepare("INSERT INTO pelanggan (nama, email, telepon, alamat) VALUES (?,?,?,?)");
                    $stmt->bind_param("ssss", $nama, $email, $telepon, $alamat);
                    $stmt->execute();
                    header("Location: index.php?pesan=" . urlencode("Data berhasil ditambahkan."));
                } else {
                    $stmt = $conn->prepare("UPDATE pelanggan SET nama=?, email=?, telepon=?, alamat=? WHERE id=?");
                    $stmt->bind_param("ssssi", $nama, $email, $telepon, $alamat, $id);
                    $stmt->execute();
                    header("Location: index.php?pesan=" . urlencode("Data berhasil diperbarui."));
                }
                exit;
            } catch (mysqli_sql_exception $e) {
                $error = ($e->getCode() == 1062) ? "Email sudah terdaftar." : "Kesalahan database: " . $e->getMessage();
            }
        }
        // Jika error, kembalikan input ke form
        $data = ['id' => $id, 'nama' => $nama, 'email' => $email, 'telepon' => $telepon, 'alamat' => $alamat];
        $aksi = ($act === 'tambah') ? 'tambah' : 'edit';
    }
}

// Ambil data untuk form edit (READ satu baris)
if ($aksi === 'edit' && $error === '') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $conn->prepare("SELECT * FROM pelanggan WHERE id = ?");
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
    $stmt = $conn->prepare("SELECT * FROM pelanggan WHERE nama LIKE ? OR email LIKE ? OR telepon LIKE ? OR alamat LIKE ? ORDER BY id DESC");
    $stmt->bind_param("ssss", $like, $like, $like, $like);
    $stmt->execute();
    $hasil = $stmt->get_result();
} else {
    $hasil = $conn->query("SELECT * FROM pelanggan ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>CRUD Pelanggan - Non-Layered</title>
<style>
  body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; padding:24px; color:#222; }
  .box { max-width:900px; margin:auto; background:#fff; padding:24px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.08); }
  h1 { margin-top:0; font-size:22px; }
  table { width:100%; border-collapse:collapse; margin-top:16px; }
  th, td { padding:10px; border-bottom:1px solid #e3e6ea; text-align:left; font-size:14px; vertical-align:top; }
  th { background:#f0f3f7; }
  .btn { display:inline-block; padding:7px 14px; border:0; border-radius:4px; cursor:pointer; text-decoration:none; font-size:13px; color:#fff; background:#2563eb; }
  .btn.edit { background:#d97706; } .btn.hapus { background:#dc2626; } .btn.abu { background:#6b7280; }
  .alert { padding:10px 14px; border-radius:4px; margin:12px 0; }
  .ok { background:#dcfce7; color:#166534; } .err { background:#fee2e2; color:#991b1b; }
  label { display:block; margin-top:12px; font-weight:bold; font-size:14px; }
  input[type=text], input[type=email], textarea { width:100%; padding:9px; border:1px solid #cbd5e1; border-radius:4px; box-sizing:border-box; font-family:inherit; font-size:14px; }
  .bar { display:flex; gap:8px; justify-content:space-between; flex-wrap:wrap; }
  form.inline { display:inline; }
</style>
</head>
<body>
<div class="box">
  <h1>Data Pelanggan <small style="font-weight:normal;color:#6b7280">(Non-Layered Architecture)</small></h1>

  <?php if ($pesan): ?><div class="alert ok"><?= htmlspecialchars($pesan) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert err"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <?php if ($aksi === 'tambah' || $aksi === 'edit'): ?>
    <!-- ===== 3. TAMPILAN: FORM TAMBAH / EDIT ===== -->
    <h2><?= $aksi === 'tambah' ? 'Tambah Pelanggan' : 'Ubah Pelanggan' ?></h2>
    <form method="post" action="index.php">
      <input type="hidden" name="aksi" value="<?= $aksi === 'tambah' ? 'tambah' : 'ubah' ?>">
      <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">

      <label>Nama</label>
      <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required>
      <label>Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>" required>
      <label>Telepon</label>
      <input type="text" name="telepon" value="<?= htmlspecialchars($data['telepon']) ?>" placeholder="contoh: 081234567890" required>
      <label>Alamat</label>
      <textarea name="alamat" rows="3" required><?= htmlspecialchars($data['alamat']) ?></textarea>

    <p>
        <button type="submit" class="btn">Simpan</button>
        <a href="index.php" class="btn abu">Batal</a>
    </p>
    </form>

    <?php else: ?>
    <!-- ===== 3. TAMPILAN: DAFTAR DATA ===== -->
    <div class="bar">
        <form method="get" action="index.php">
        <input type="text" name="cari" placeholder="Cari nama / email / telepon / alamat" value="<?= htmlspecialchars($cari) ?>" style="width:280px;display:inline-block">
        <button class="btn" type="submit">Cari</button>
        <?php if ($cari !== ''): ?><a href="index.php" class="btn abu">Reset</a><?php endif; ?>
        </form>
        <a href="index.php?aksi=tambah" class="btn">+ Tambah Data</a>
    </div>

    <table>
        <tr><th>No</th><th>Nama</th><th>Email</th><th>Telepon</th><th>Alamat</th><th>Aksi</th></tr>
        <?php $no = 1; while ($r = $hasil->fetch_assoc()): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($r['nama']) ?></td>
            <td><?= htmlspecialchars($r['email']) ?></td>
            <td><?= htmlspecialchars($r['telepon']) ?></td>
            <td><?= nl2br(htmlspecialchars($r['alamat'])) ?></td>
            <td style="white-space:nowrap">
            <a class="btn edit" href="index.php?aksi=edit&id=<?= (int)$r['id'] ?>">Edit</a>
            <form class="inline" method="post" action="index.php" onsubmit="return confirm('Hapus data ini?')">
                <input type="hidden" name="aksi" value="hapus">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button class="btn hapus" type="submit">Hapus</button>
            </form>
            </td>
        </tr>
        <?php endwhile; ?>
        <?php if ($no === 1): ?><tr><td colspan="6" style="text-align:center;color:#6b7280">Belum ada data.</td></tr><?php endif; ?>
    </table>
    <?php endif; ?>
</div>
</body>
</html>