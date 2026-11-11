# SIM Mahasiswa — versi penyempurnaan

Aplikasi PHP + MySQL untuk ringkasan data akademik dan pengenalan Web Semantik.

## Menjalankan secara lokal
1. Pasang XAMPP/Laragon atau server PHP dengan ekstensi `mysqli`.
2. Salin folder `simp_mahasiswa` ke direktori web server (mis. `htdocs`).
3. Buat database bernama `simp_mahasiswa`.
4. Impor file `dump_infinityfree (1).sql` melalui phpMyAdmin atau MySQL.
5. Sesuaikan kredensial database pada `koneksi.php` jika berbeda dari konfigurasi lokal.
6. Buka `http://localhost/simp_mahasiswa/`.

## Catatan penting
- Login hanya menerima password yang disimpan sebagai hash kompatibel dengan `password_hash()`.
- Data contoh dan akun contoh di dump SQL ditujukan untuk pengembangan; ganti kredensial sebelum deployment.
- Jangan gunakan akun contoh di server publik. Gunakan HTTPS dan konfigurasi kredensial database melalui environment variables.
