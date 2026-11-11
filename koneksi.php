<?php
mysqli_report(MYSQLI_REPORT_OFF);

$host = "sql304.infinityfree.com";
$username = "if0_43206651";
$password = "Azza3030";
$database = "if0_43206651_azzaaa";

$koneksi = mysqli_connect(
    $host,
    $username,
    $password,
    $database,
    3306
);

if (!$koneksi) {
    error_log("Koneksi database gagal: " . mysqli_connect_error());
    http_response_code(500);
    exit("Koneksi database gagal. Periksa konfigurasi database.");
}

mysqli_set_charset($koneksi, "utf8mb4");
?>