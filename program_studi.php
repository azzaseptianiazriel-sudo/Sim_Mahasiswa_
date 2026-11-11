<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
require_once __DIR__ . "/koneksi.php";

if (!isset($_SESSION["id_pengguna"])) {
    header("Location: login.php");
    exit;
}

mysqli_set_charset($koneksi, "utf8mb4");

function e($v) {
    return htmlspecialchars((string)($v ?? ""), ENT_QUOTES, "UTF-8");
}

function redirectPesan($pesan) {
    $_SESSION["pesan_prodi"] = $pesan;
    header("Location: program_studi.php");
    exit;
}

if (empty($_SESSION["csrf_prodi"])) {
    $_SESSION["csrf_prodi"] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION["csrf_prodi"];
$error = "";
$pesan = $_SESSION["pesan_prodi"] ?? "";
unset($_SESSION["pesan_prodi"]);

$jenjangList = ["D3", "D4", "S1", "S2", "S3"];

$edit = [
    "id_prodi" => "",
    "id_fakultas" => "",
    "kode_prodi" => "",
    "nama_prodi" => "",
    "jenjang" => "S1",
    "akreditasi" => "",
    "kaprodi" => "",
    "jumlah_mahasiswa" => 0,
    "uri" => "",
    "aktif" => 1
];

$isEdit = false;

/* =========================
   PROSES TAMBAH, EDIT, HAPUS
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!hash_equals($csrf, $_POST["csrf"] ?? "")) {
        exit("Permintaan tidak valid. Silakan muat ulang halaman.");
    }

    $aksi = $_POST["aksi"] ?? "";

    /* HAPUS DATA */
    if ($aksi === "hapus") {
        $id = filter_var(
            $_POST["id_prodi"] ?? "",
            FILTER_VALIDATE_INT
        );

        if (!$id || $id < 1) {
            redirectPesan("ID program studi tidak valid.");
        }

        $stmt = mysqli_prepare(
            $koneksi,
            "DELETE FROM program_studi WHERE id_prodi = ?"
        );

        if (!$stmt) {
            redirectPesan("Gagal menyiapkan penghapusan data.");
        }

        mysqli_stmt_bind_param($stmt, "i", $id);
        $ok = mysqli_stmt_execute($stmt);
        $jumlahTerhapus = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        redirectPesan(
            $ok && $jumlahTerhapus > 0
                ? "Program studi berhasil dihapus."
                : "Data gagal dihapus atau tidak ditemukan."
        );
    }

    /* TAMBAH ATAU EDIT DATA */
    if ($aksi === "simpan" || $aksi === "edit") {
        $id = (int)($_POST["id_prodi"] ?? 0);
        $idFakultas = (int)($_POST["id_fakultas"] ?? 0);
        $kode = trim($_POST["kode_prodi"] ?? "");
        $nama = trim($_POST["nama_prodi"] ?? "");
        $jenjang = $_POST["jenjang"] ?? "";
        $akreditasi = trim($_POST["akreditasi"] ?? "");
        $kaprodi = trim($_POST["kaprodi"] ?? "");

        $jumlah = filter_var(
            $_POST["jumlah_mahasiswa"] ?? "",
            FILTER_VALIDATE_INT
        );

        $uri = trim($_POST["uri"] ?? "");
        $aktif = filter_var(
            $_POST["aktif"] ?? "",
            FILTER_VALIDATE_INT
        );

        $edit = [
            "id_prodi" => $id,
            "id_fakultas" => $idFakultas,
            "kode_prodi" => $kode,
            "nama_prodi" => $nama,
            "jenjang" => $jenjang,
            "akreditasi" => $akreditasi,
            "kaprodi" => $kaprodi,
            "jumlah_mahasiswa" => $jumlah === false ? "" : $jumlah,
            "uri" => $uri,
            "aktif" => $aktif === false ? 1 : $aktif
        ];

        $isEdit = ($aksi === "edit");

        if (
            ($isEdit && $id < 1) ||
            $idFakultas < 1 ||
            $kode === "" || strlen($kode) > 30 ||
            $nama === "" || strlen($nama) > 200 ||
            !in_array($jenjang, $jenjangList, true) ||
            strlen($akreditasi) > 50 ||
            strlen($kaprodi) > 150 ||
            $jumlah === false || $jumlah < 0 ||
            $uri === "" || strlen($uri) > 255 ||
            !filter_var($uri, FILTER_VALIDATE_URL) ||
            !in_array($aktif, [0, 1], true)
        ) {
            $error = "Periksa kembali semua isian formulir.";
        } else {
            /* Pastikan fakultas benar-benar ada */
            $cek = mysqli_prepare(
                $koneksi,
                "SELECT id_fakultas
                 FROM fakultas
                 WHERE id_fakultas = ?"
            );

            if (!$cek) {
                $error = "Gagal memeriksa data fakultas.";
            } else {
                mysqli_stmt_bind_param($cek, "i", $idFakultas);
                mysqli_stmt_execute($cek);
                $hasilCek = mysqli_stmt_get_result($cek);
                $adaFakultas = mysqli_num_rows($hasilCek) > 0;
                mysqli_stmt_close($cek);

                if (!$adaFakultas) {
                    $error = "Fakultas tidak ditemukan.";
                }
            }

            if ($error === "") {
                $akreditasiDB = $akreditasi === "" ? null : $akreditasi;
                $kaprodiDB = $kaprodi === "" ? null : $kaprodi;

                if ($aksi === "simpan") {
                    $sql = "INSERT INTO program_studi
                        (id_fakultas, kode_prodi, nama_prodi,
                         jenjang, akreditasi, kaprodi,
                         jumlah_mahasiswa, uri, aktif)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $stmt = mysqli_prepare($koneksi, $sql);

                    if (!$stmt) {
                        $error = "Gagal menyiapkan data baru.";
                    } else {
                        $tipe = substr("isssssisii", 0, 9);

                        mysqli_stmt_bind_param(
                            $stmt,
                            $tipe,
                            $idFakultas,
                            $kode,
                            $nama,
                            $jenjang,
                            $akreditasiDB,
                            $kaprodiDB,
                            $jumlah,
                            $uri,
                            $aktif
                        );
                    }
                } else {
                    $sql = "UPDATE program_studi SET
                        id_fakultas = ?,
                        kode_prodi = ?,
                        nama_prodi = ?,
                        jenjang = ?,
                        akreditasi = ?,
                        kaprodi = ?,
                        jumlah_mahasiswa = ?,
                        uri = ?,
                        aktif = ?
                        WHERE id_prodi = ?";

                    $stmt = mysqli_prepare($koneksi, $sql);

                    if (!$stmt) {
                        $error = "Gagal menyiapkan perubahan data.";
                    } else {
                        mysqli_stmt_bind_param(
                            $stmt,
                            "isssssisii",
                            $idFakultas,
                            $kode,
                            $nama,
                            $jenjang,
                            $akreditasiDB,
                            $kaprodiDB,
                            $jumlah,
                            $uri,
                            $aktif,
                            $id
                        );
                    }
                }

                if ($error === "" && isset($stmt) && $stmt) {
                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);

                        redirectPesan(
                            $aksi === "simpan"
                                ? "Program studi berhasil ditambahkan."
                                : "Program studi berhasil diperbarui."
                        );
                    } else {
                        $error = "Gagal menyimpan data: "
                            . mysqli_stmt_error($stmt);
                        mysqli_stmt_close($stmt);
                    }
                }
            }
        }
    }
}

/* =========================
   AMBIL DATA UNTUK FORM EDIT
========================= */

if (
    $_SERVER["REQUEST_METHOD"] !== "POST" &&
    isset($_GET["edit"])
) {
    $idEdit = filter_var(
        $_GET["edit"],
        FILTER_VALIDATE_INT
    );

    if ($idEdit && $idEdit > 0) {
        $stmtEdit = mysqli_prepare(
            $koneksi,
            "SELECT id_prodi, id_fakultas, kode_prodi,
                    nama_prodi, jenjang, akreditasi, kaprodi,
                    jumlah_mahasiswa, uri, aktif
             FROM program_studi
             WHERE id_prodi = ?"
        );

        if ($stmtEdit) {
            mysqli_stmt_bind_param($stmtEdit, "i", $idEdit);
            mysqli_stmt_execute($stmtEdit);
            $hasilEdit = mysqli_stmt_get_result($stmtEdit);
            $dataEdit = mysqli_fetch_assoc($hasilEdit);
            mysqli_stmt_close($stmtEdit);

            if ($dataEdit) {
                $edit = $dataEdit;
                $isEdit = true;
            } else {
                $pesan = "Program studi tidak ditemukan.";
            }
        }
    }
}

/* =========================
   AMBIL DAFTAR FAKULTAS
========================= */

$fakultas = mysqli_query(
    $koneksi,
    "SELECT id_fakultas, nama_fakultas
     FROM fakultas
     ORDER BY nama_fakultas ASC"
);

if (!$fakultas) {
    die("Gagal mengambil data fakultas: " . mysqli_error($koneksi));
}

/* =========================
   AMBIL DAFTAR PROGRAM STUDI
========================= */

$data = mysqli_query(
    $koneksi,
    "SELECT ps.id_prodi, ps.id_fakultas, ps.kode_prodi,
            ps.nama_prodi, ps.jenjang, ps.jumlah_mahasiswa,
            ps.aktif, f.nama_fakultas
     FROM program_studi ps
     LEFT JOIN fakultas f
       ON ps.id_fakultas = f.id_fakultas
     ORDER BY ps.nama_prodi ASC"
);

if (!$data) {
    die("Gagal mengambil data program studi: " . mysqli_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Program Studi - SIM Mahasiswa</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: #f3f6fb;
    color: #19365c;
    font-family: Arial, sans-serif;
}

header {
    background: #18365e;
    color: white;
    padding: 20px 5%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

header a {
    color: white;
    text-decoration: none;
}

main {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 18px;
}

.panel {
    background: white;
    border-radius: 12px;
    padding: 22px;
    margin-bottom: 22px;
    box-shadow: 0 3px 12px rgba(20, 45, 80, 0.05);
}

.grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 15px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 6px;
}

input, select {
    width: 100%;
    padding: 11px;
    border: 1px solid #ccd7e5;
    border-radius: 7px;
    background: white;
}

button, .btn {
    display: inline-block;
    padding: 10px 14px;
    border: 0;
    border-radius: 7px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
}

.primary {
    background: #245de8;
    color: white;
}

.secondary {
    background: #e7edf6;
    color: #19365c;
}

.danger {
    background: #ffe2e2;
    color: #a21c1c;
}

.notice {
    background: #dff7e7;
    color: #155c32;
    padding: 12px;
    border-radius: 7px;
}

.error {
    background: #ffe2e2;
    color: #a21c1c;
    padding: 12px;
    border-radius: 7px;
    overflow-wrap: anywhere;
}

.table-wrap {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 800px;
}

th, td {
    padding: 11px;
    text-align: left;
    border-bottom: 1px solid #e1e8f0;
}

th {
    background: #eef3fa;
}

.actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    align-items: center;
}

.actions form {
    margin: 0;
}

.small {
    color: #71819a;
    font-size: 13px;
}

@media (max-width: 650px) {
    .grid {
        grid-template-columns: 1fr;
    }

    main {
        margin-top: 20px;
    }

    .panel {
        padding: 16px;
    }
}
</style>
</head>

<body>
<header>
    <div>
        <strong>SIM Mahasiswa</strong><br>
        Data Program Studi
    </div>
    <a href="dashboard.php">Kembali ke Dashboard</a>
</header>

<main>
    <h1>Data Program Studi</h1>
    <p class="small">Tambah, lihat, edit, dan hapus program studi.</p>

    <?php if ($pesan !== ""): ?>
        <p class="notice"><?= e($pesan) ?></p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>

    <section class="panel">
        <h2><?= $isEdit ? "Edit Program Studi" : "Tambah Program Studi" ?></h2>

        <form method="post" action="program_studi.php">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="aksi" value="<?= $isEdit ? "edit" : "simpan" ?>">
            <input type="hidden" name="id_prodi" value="<?= e($edit["id_prodi"]) ?>">

            <div class="grid">
                <div>
                    <label>Fakultas *</label>
                    <select name="id_fakultas" required>
                        <option value="">-- Pilih Fakultas --</option>
                        <?php while ($f = mysqli_fetch_assoc($fakultas)): ?>
                            <option
                                value="<?= e($f["id_fakultas"]) ?>"
                                <?= (string)$edit["id_fakultas"] === (string)$f["id_fakultas"] ? "selected" : "" ?>>
                                <?= e($f["nama_fakultas"]) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label>Kode Program Studi *</label>
                    <input
                        name="kode_prodi"
                        maxlength="30"
                        required
                        value="<?= e($edit["kode_prodi"]) ?>">
                </div>

                <div>
                    <label>Nama Program Studi *</label>
                    <input
                        name="nama_prodi"
                        maxlength="200"
                        required
                        value="<?= e($edit["nama_prodi"]) ?>">
                </div>

                <div>
                    <label>Jenjang *</label>
                    <select name="jenjang" required>
                        <?php foreach ($jenjangList as $j): ?>
                            <option
                                value="<?= e($j) ?>"
                                <?= $edit["jenjang"] === $j ? "selected" : "" ?>>
                                <?= e($j) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Akreditasi</label>
                    <input
                        name="akreditasi"
                        maxlength="50"
                        value="<?= e($edit["akreditasi"]) ?>">
                </div>

                <div>
                    <label>Ketua Program Studi</label>
                    <input
                        name="kaprodi"
                        maxlength="150"
                        value="<?= e($edit["kaprodi"]) ?>">
                </div>

                <div>
                    <label>Jumlah Mahasiswa *</label>
                    <input
                        type="number"
                        min="0"
                        name="jumlah_mahasiswa"
                        required
                        value="<?= e($edit["jumlah_mahasiswa"]) ?>">
                </div>

                <div>
                    <label>URI Program Studi *</label>
                    <input
                        type="url"
                        name="uri"
                        maxlength="255"
                        required
                        placeholder="https://contoh.ac.id/prodi/ti"
                        value="<?= e($edit["uri"]) ?>">
                </div>

                <div>
                    <label>Status *</label>
                    <select name="aktif" required>
                        <option
                            value="1"
                            <?= (int)$edit["aktif"] === 1 ? "selected" : "" ?>>
                            Aktif
                        </option>
                        <option
                            value="0"
                            <?= (int)$edit["aktif"] === 0 ? "selected" : "" ?>>
                            Tidak aktif
                        </option>
                    </select>
                </div>
            </div>

            <br>

            <button class="primary" type="submit">
                <?= $isEdit ? "Simpan Perubahan" : "Tambah Data" ?>
            </button>

            <?php if ($isEdit): ?>
                <a class="btn secondary" href="program_studi.php">Batal</a>
            <?php endif; ?>
        </form>
    </section>

    <section class="panel">
        <h2>Daftar Program Studi</h2>
        <p class="small">Jumlah data: <?= mysqli_num_rows($data) ?></p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Kode</th>
                        <th>Nama Program Studi</th>
                        <th>Fakultas</th>
                        <th>Jenjang</th>
                        <th>Mahasiswa</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (mysqli_num_rows($data) === 0): ?>
                        <tr>
                            <td colspan="8">Belum ada data program studi.</td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; ?>
                        <?php while ($p = mysqli_fetch_assoc($data)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= e($p["kode_prodi"]) ?></td>
                                <td><?= e($p["nama_prodi"]) ?></td>
                                <td><?= e($p["nama_fakultas"] ?? "-") ?></td>
                                <td><?= e($p["jenjang"]) ?></td>
                                <td><?= e($p["jumlah_mahasiswa"]) ?></td>
                                <td>
                                    <?= (int)$p["aktif"] === 1 ? "Aktif" : "Tidak aktif" ?>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a
                                            class="btn secondary"
                                            href="program_studi.php?edit=<?= (int)$p["id_prodi"] ?>">
                                            Edit
                                        </a>

                                        <form
                                            method="post"
                                            action="program_studi.php"
                                            onsubmit="return confirm('Yakin ingin menghapus data program studi ini?')">

                                            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input
                                                type="hidden"
                                                name="id_prodi"
                                                value="<?= (int)$p["id_prodi"] ?>">

                                            <button class="danger" type="submit">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>

ini kan codingannya