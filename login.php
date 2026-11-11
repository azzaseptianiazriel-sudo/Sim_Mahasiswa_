<?php
session_start();
require_once __DIR__ . "/koneksi.php";

if (isset($_SESSION['id_pengguna'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $error = "Username dan password wajib diisi.";
    } else {
        $stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        p.id_pengguna,
        p.username,
        p.password_hash,
        p.nama_lengkap,
        r.nama_role
     FROM pengguna p
     LEFT JOIN roles r ON p.id_role = r.id_role
     WHERE p.username = ?
     LIMIT 1"
);

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

       
$validPassword = false;

if ($user) {
    $storedPassword = (string) $user["password_hash"];

    if (
        password_verify($password, $storedPassword) ||
        hash_equals($storedPassword, $password)
    ) {
        $validPassword = true;
    }
}

if ($validPassword) {
    session_regenerate_id(true);

    $_SESSION["id_pengguna"] = $user["id_pengguna"];
    $_SESSION["username"] = $user["username"];
    $_SESSION["nama_lengkap"] = $user["nama_lengkap"];
    $_SESSION["nama_role"] = $user["nama_role"] ?? "Pengguna";

    header("Location: dashboard.php");
    exit;
} else {
    $error = "Username atau password tidak sesuai.";
}

        
if ($validPassword) {
    session_regenerate_id(true);

    $_SESSION["id_pengguna"] = $user["id_pengguna"];
    $_SESSION["username"] = $user["username"];
    $_SESSION["nama_lengkap"] = $user["nama_lengkap"];
    $_SESSION["nama_role"] = $user["nama_role"] ?? "Pengguna";

    header("Location: dashboard.php");
    exit;
} else {
    $error = "Username atau password tidak sesuai.";
}
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SIM Mahasiswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">

<div class="login-layout">
    <section class="login-side">
        <a href="index.php" class="brand login-brand">
            <span class="brand-icon">S</span>
            <span>SIM<span class="brand-light"> Mahasiswa</span></span>
        </a>

        <div class="login-side-content">
            <span class="eyebrow">ACADEMIC INFORMATION SYSTEM</span>
            <h1>Satu akses untuk informasi akademik.</h1>
            <p>
                Kelola informasi universitas, program studi,
                dan mahasiswa dalam sistem yang terintegrasi.
            </p>
            <div class="login-decoration">
                <span>UNIVERSITAS</span>
                <i></i>
                <span>FAKULTAS</span>
                <i></i>
                <span>MAHASISWA</span>
            </div>
        </div>

        <p class="side-caption">SIM Mahasiswa · Web Semantik</p>
    </section>

    <section class="login-main">
        <div class="login-card">
            <a href="index.php" class="back-link">← Kembali ke beranda</a>

            <div class="login-heading">
                <span class="login-symbol">✦</span>
                <span class="section-label">SELAMAT DATANG</span>
                <h2>Masuk ke akunmu</h2>
                <p>Masukkan informasi akun untuk melanjutkan.</p>
            </div>

            <?php if ($error !== ""): ?>
                <div class="alert-error">
                    <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Masukkan username"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary login-submit">
                    Masuk ke Dashboard <span>→</span>
                </button>
            </form>

            <p class="login-footnote">
                Gunakan akun yang terdaftar di database sistem.
            </p>
        </div>
    </section>
</div>

</body>
</html>
