<?php
session_start();

if (isset($_SESSION['id_pengguna'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIM Mahasiswa | Beranda</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="landing-page">

<header class="navbar">
    <a href="index.php" class="brand">
        <span class="brand-icon">S</span>
        <span>SIM<span class="brand-light"> Mahasiswa</span></span>
    </a>

    <nav>
        <a href="#tentang">Tentang</a>
        <a href="#fitur">Fitur</a>
        <a href="login.php" class="nav-login">Login ↗</a>
    </nav>
</header>

<main>
    <section class="hero">
        <div class="hero-content">
            <span class="eyebrow">
                SISTEM INFORMASI AKADEMIK
            </span>

            <h1>
                Mengenal Data.<br>
                Memahami <span>Makna.</span>
            </h1>

            <p class="hero-description">
                Sistem Informasi Manajemen Mahasiswa untuk
                mengelola informasi akademik secara terstruktur,
                terhubung, dan mendukung penerapan Web Semantik.
            </p>

            <div class="hero-actions">
                <a href="login.php" class="btn btn-primary">
                    Masuk ke Sistem <span>→</span>
                </a>
                <a href="#tentang" class="btn btn-secondary">
                    Pelajari lebih lanjut
                </a>
            </div>

            <div class="hero-note">
                <span class="status-dot"></span>
                Informasi akademik dalam satu sistem
            </div>
        </div>

        <div class="hero-visual">
            <div class="orbit orbit-one"></div>
            <div class="orbit orbit-two"></div>

            <div class="visual-card main-visual-card">
                <div class="visual-top">
                    <span class="mini-icon">✦</span>
                    <span>ACADEMIC DATA</span>
                    <span class="visual-dots">•••</span>
                </div>

                <div class="visual-title">
                    Informasi yang saling terhubung
                </div>

                <div class="data-node university-node">
                    <span class="node-icon">⌂</span>
                    <div>
                        <strong>Universitas</strong>
                        <small>Entitas utama</small>
                    </div>
                </div>

                <div class="node-connector"></div>

                <div class="data-branches">
                    <div class="data-node branch-node">
                        <span class="node-icon">▤</span>
                        <div>
                            <strong>Fakultas</strong>
                            <small>Organisasi</small>
                        </div>
                    </div>

                    <div class="data-node branch-node">
                        <span class="node-icon">⌘</span>
                        <div>
                            <strong>Mahasiswa</strong>
                            <small>Data akademik</small>
                        </div>
                    </div>
                </div>

                <div class="semantic-tag">
                    <span>URI</span>
                    Identitas data yang unik
                </div>
            </div>

            <div class="floating-chip chip-top">
                <span>✧</span> Data Terstruktur
            </div>

            <div class="floating-chip chip-bottom">
                <span>↗</span> Web Semantik
            </div>
        </div>
    </section>

    <section id="tentang" class="intro-section">
        <div>
            <span class="section-label">TENTANG SISTEM</span>
            <h2>Data bukan sekadar angka.</h2>
        </div>
        <p>
            Setiap informasi memiliki identitas dan hubungan.
            Dengan pendekatan Web Semantik, data universitas,
            fakultas, program studi, dan mahasiswa dapat
            direpresentasikan dengan makna yang lebih jelas.
        </p>
    </section>

    <section id="fitur" class="features-section">
        <div class="section-heading">
            <span class="section-label">FITUR UTAMA</span>
            <h2>Semua informasi, lebih terorganisasi.</h2>
        </div>

        <div class="feature-grid">
            <article class="feature-card">
                <div class="feature-icon">⌂</div>
                <h3>Data Universitas</h3>
                <p>Informasi identitas dan profil universitas.</p>
            </article>

            <article class="feature-card">
                <div class="feature-icon">▤</div>
                <h3>Data Akademik</h3>
                <p>Informasi fakultas dan program studi.</p>
            </article>

            <article class="feature-card">
                <div class="feature-icon">◎</div>
                <h3>Data Mahasiswa</h3>
                <p>Informasi mahasiswa yang terhubung dengan program studi.</p>
            </article>

            <article class="feature-card">
                <div class="feature-icon">⌘</div>
                <h3>Web Semantik</h3>
                <p>Penggunaan URI untuk mengidentifikasi entitas data.</p>
            </article>
        </div>
    </section>
</main>

<footer class="footer">
    <a href="index.php" class="brand footer-brand">
        <span class="brand-icon">S</span>
        <span>SIM<span class="brand-light"> Mahasiswa</span></span>
    </a>
    <p>© <?php echo date("Y"); ?> SIM Mahasiswa. Sistem informasi akademik.</p>
</footer>

</body>
</html>