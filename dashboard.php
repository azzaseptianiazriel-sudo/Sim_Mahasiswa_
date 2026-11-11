
<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION["id_pengguna"])) {
    header("Location: login.php");
    exit;
}

function ambilNilai($koneksi, $sql) {
    $result = mysqli_query($koneksi, $sql);

    if (!$result) {
        return 0;
    }

    $data = mysqli_fetch_row($result);
    return $data[0] ?? 0;
}

/*
|--------------------------------------------------------------------------
| STATISTIK DASHBOARD
|--------------------------------------------------------------------------
| Total mahasiswa dijumlahkan dari kolom jumlah_mahasiswa
| pada seluruh program studi aktif.
*/

$totalMahasiswa = ambilNilai(
    $koneksi,
    "SELECT COALESCE(SUM(jumlah_mahasiswa), 0)
     FROM program_studi
     WHERE aktif = 1"
);

$totalProdi = ambilNilai(
    $koneksi,
    "SELECT COUNT(*)
     FROM program_studi
     WHERE aktif = 1"
);

$totalFakultas = ambilNilai(
    $koneksi,
    "SELECT COUNT(*)
     FROM fakultas"
);

/*
|--------------------------------------------------------------------------
| PROFIL UNIVERSITAS
|--------------------------------------------------------------------------
*/

$universitas = mysqli_query(
    $koneksi,
    "SELECT Nama_Universitas, Alamat, URI
     FROM universitas
     LIMIT 1"
);

$profil = $universitas
    ? mysqli_fetch_assoc($universitas)
    : null;

/*
|--------------------------------------------------------------------------
| DAFTAR PROGRAM STUDI
|--------------------------------------------------------------------------
| Jumlah mahasiswa setiap kartu diambil dari kolom
| jumlah_mahasiswa, bukan dihitung dari tabel mahasiswa.
*/

$prodiResult = mysqli_query(
    $koneksi,
    "SELECT
        ps.Id_Prodi,
        ps.Kode_Prodi,
        ps.Nama_Prodi,
        ps.Jenjang,
        ps.URI,
        ps.jumlah_mahasiswa AS Total_Mahasiswa,
        f.Nama_Fakultas
     FROM program_studi ps
     LEFT JOIN fakultas f
        ON ps.Id_Fakultas = f.Id_Fakultas
     WHERE ps.aktif = 1
     ORDER BY ps.Nama_Prodi ASC"
);

$daftarProdi = [];

if ($prodiResult) {
    while ($row = mysqli_fetch_assoc($prodiResult)) {
        $daftarProdi[] = $row;
    }
}

/*
|--------------------------------------------------------------------------
| DATA WEB SEMANTIK / JSON-LD
|--------------------------------------------------------------------------
*/

$graph = [];

if ($profil && !empty($profil["URI"])) {
    $graph[] = [
        "@type" => "CollegeOrUniversity",
        "@id" => $profil["URI"],
        "name" => $profil["Nama_Universitas"],
        "address" => $profil["Alamat"] ?? ""
    ];
}

foreach ($daftarProdi as $p) {
    if (!empty($p["URI"])) {
        $graph[] = [
            "@type" => "EducationalOccupationalProgram",
            "@id" => $p["URI"],
            "name" => $p["Nama_Prodi"],
            "educationalProgramMode" => $p["Jenjang"],
            "provider" => [
                "@type" => "CollegeOrUniversity",
                "@id" => $profil["URI"] ?? ""
            ]
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard | SIM Mahasiswa</title>

    <link rel="stylesheet" href="style.css">

    <style>
        .btn-tambah-prodi {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 18px;
            background: #6844d6;
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            border-radius: 10px;
            border: none;
            transition: background 0.2s ease;
        }

        .btn-tambah-prodi:hover {
            background: #5132b5;
        }

        .dashboard-section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .program-card-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 16px;
        }

        .program-card-actions form {
            margin: 0;
        }

        .program-card-actions .btn-edit,
        .program-card-actions .btn-delete {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .program-card-actions .btn-edit {
            background: #eee9ff;
            color: #5132b5;
        }

        .program-card-actions .btn-delete {
            background: #ffe7eb;
            color: #b4233c;
        }
    </style>

    <script type="application/ld+json">
    <?= json_encode(
        [
            "@context" => "https://schema.org",
            "@graph" => $graph
        ],
        JSON_UNESCAPED_SLASHES |
        JSON_UNESCAPED_UNICODE |
        JSON_HEX_TAG |
        JSON_HEX_AMP |
        JSON_HEX_APOS |
        JSON_HEX_QUOT
    ) ?>
    </script>
</head>

<body class="dashboard-page">

<div class="dashboard-layout">

    <aside class="sidebar">

        <a href="dashboard.php" class="brand sidebar-brand">
            <span class="brand-icon">S</span>
            <span>SIM<span class="brand-light"> Mahasiswa</span></span>
        </a>

        <p class="sidebar-label">MENU UTAMA</p>

        <nav class="sidebar-nav">
            <a href="dashboard.php" class="sidebar-link active">
                <span>▦</span> Dashboard
            </a>

            <a href="#program-studi" class="sidebar-link">
                <span>▤</span> Program Studi
            </a>

            <a href="#profil" class="sidebar-link">
                <span>⌂</span> Profil Universitas
            </a>
        </nav>

        <div class="sidebar-bottom">

            <div class="sidebar-user">
                <div class="avatar">
                    <?= htmlspecialchars(
                        strtoupper(
                            substr(
                                $_SESSION["nama_lengkap"] ?? "U",
                                0,
                                1
                            )
                        ),
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </div>

                <div>
                    <strong>
                        <?= htmlspecialchars(
                            $_SESSION["nama_lengkap"] ?? "Pengguna",
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </strong>

                    <small>
                        <?= htmlspecialchars(
                            $_SESSION["nama_role"] ?? "Pengguna",
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </small>
                </div>
            </div>

            <a href="logout.php" class="sidebar-link logout-link">
                <span>↪</span> Keluar
            </a>

        </div>
    </aside>

    <main class="dashboard-main">

        <header class="dashboard-header">
            <div>
                <span class="section-label">OVERVIEW</span>
                <h1>Dashboard</h1>
                <p>Ringkasan informasi akademik universitas.</p>
            </div>

            <div class="date-pill">
                <span>◷</span>
                <?= date("d M Y") ?>
            </div>
        </header>

        <section class="welcome-banner">
            <div>
                <span class="welcome-label">
                    SELAMAT DATANG KEMBALI
                </span>

                <h2>
                    Halo,
                    <?= htmlspecialchars(
                        $_SESSION["nama_lengkap"] ?? "Pengguna",
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>!
                </h2>

                <p>
                    Pantau data akademik dan hubungan antarentitas
                    dalam satu tempat.
                </p>
            </div>

            <div class="welcome-decoration">✦</div>
        </section>

        <!-- STATISTIK DASHBOARD -->

        <section class="stats-grid">

            <article class="stat-card stat-primary">
                <div class="stat-top">
                    <span>Total Mahasiswa</span>
                    <span class="stat-icon">◎</span>
                </div>

                <div class="stat-number">
                    <?= number_format((float) $totalMahasiswa, 0, ",", ".") ?>
                </div>

                <p>Total mahasiswa dari seluruh prodi aktif</p>
            </article>

            <article class="stat-card">
                <div class="stat-top">
                    <span>Program Studi</span>
                    <span class="stat-icon">▤</span>
                </div>

                <div class="stat-number">
                    <?= number_format((int) $totalProdi, 0, ",", ".") ?>
                </div>

                <p>Program studi aktif</p>
            </article>

            <article class="stat-card">
                <div class="stat-top">
                    <span>Fakultas</span>
                    <span class="stat-icon">⌂</span>
                </div>

                <div class="stat-number">
                    <?= number_format((int) $totalFakultas, 0, ",", ".") ?>
                </div>

                <p>Fakultas terdata</p>
            </article>

        </section>

        <!-- DAFTAR PROGRAM STUDI -->

        <section id="program-studi" class="dashboard-section">

            <div class="section-heading dashboard-section-heading">

                <div>
                    <span class="section-label">
                        INFORMASI AKADEMIK
                    </span>
                    <h2>Program Studi</h2>
                </div>

                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">

                    <span class="count-label">
                        <?= count($daftarProdi) ?> program
                    </span>

                    <a href="program_studi.php" class="btn-tambah-prodi">
                        + Tambah Program Studi
                    </a>

                </div>
            </div>

            <div class="program-grid">

                <?php if (count($daftarProdi) > 0): ?>

                    <?php foreach ($daftarProdi as $p): ?>

                        <article class="program-card">

                            <div class="program-card-top">

                                <span class="program-code">
                                    <?= htmlspecialchars(
                                        $p["Kode_Prodi"] ?? "-",
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>
                                </span>

                                <span class="program-level">
                                    <?= htmlspecialchars(
                                        $p["Jenjang"] ?? "S1",
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>
                                </span>

                            </div>

                            <h3>
                                <?= htmlspecialchars(
                                    $p["Nama_Prodi"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </h3>

                            <p class="faculty-name">
                                <?= htmlspecialchars(
                                    $p["Nama_Fakultas"] ?? "Fakultas belum tersedia",
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </p>

                            <div class="program-card-bottom">
                                <span>Jumlah mahasiswa</span>

                                <strong>
                                    <?= number_format(
                                        (float) ($p["Total_Mahasiswa"] ?? 0),
                                        0,
                                        ",",
                                        "."
                                    ) ?>
                                </strong>
                            </div>

                            <?php if (!empty($p["URI"])): ?>

                                <div class="uri-text">
                                    <span>URI entitas</span>

                                    <code>
                                        <?= htmlspecialchars(
                                            $p["URI"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) ?>
                                    </code>
                                </div>

                            <?php endif; ?>

                            <div class="program-card-actions">

                                <a
                                    href="program_studi.php?edit=<?= (int) $p["Id_Prodi"] ?>"
                                    class="btn-edit"
                                >Edit</a>

                                <form
                                    action="program_studi.php"
                                    method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus program studi ini?');"
                                >
                                    <input
                                        type="hidden"
                                        name="hapus_id"
                                        value="<?= (int) $p["Id_Prodi"] ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="btn-delete"
                                    >
                                        Hapus
                                    </button>
                                </form>

                            </div>

                        </article>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="empty-state">
                        <span>▤</span>

                        <h3>Belum ada program studi</h3>

                        <p>
                            Tambahkan data program studi ke database
                            untuk menampilkannya di sini.
                        </p>

                        <a href="program_studi.php" class="btn-tambah-prodi">
                            + Tambah Program Studi
                        </a>
                    </div>

                <?php endif; ?>

            </div>
        </section>

        <!-- PROFIL UNIVERSITAS -->

        <section id="profil" class="university-panel">

            <div class="university-symbol">⌂</div>

            <div class="university-details">

                <span class="section-label">PROFIL INSTITUSI</span>

                <h2>
                    <?= htmlspecialchars(
                        $profil["Nama_Universitas"] ?? "Universitas belum diatur",
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </h2>

                <p>
                    <?= htmlspecialchars(
                        $profil["Alamat"] ?? "Alamat belum tersedia.",
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </p>

                <?php if (!empty($profil["URI"])): ?>

                    <p class="uri-line">
                        URI:
                        <code>
                            <?= htmlspecialchars(
                                $profil["URI"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </code>
                    </p>

                <?php endif; ?>

            </div>
        </section>

        <footer class="dashboard-footer">
            SIM Mahasiswa · Sistem Informasi Akademik berbasis Web Semantik
        </footer>

    </main>
</div>

</body>
</html>

