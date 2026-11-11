<?php
require_once __DIR__ . "/koneksi.php";

if (isset($koneksi) && $koneksi instanceof mysqli) {
    if ($koneksi->connect_errno === 0) {
        echo "Koneksi database berhasil!";
    } else {
        echo "Koneksi database gagal.";
    }
} else {
    echo "Variabel koneksi tidak ditemukan.";
}
?>
