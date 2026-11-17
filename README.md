# Frontend Self-Assessment Telehealth (UTS)

Frontend pasien untuk API Self-Assessment Telehealth, dibuat dengan PHP native dan Bootstrap 5.

- Nama: Maulana Zakki Prasuli
- NIM: C2C023168
- Kelas: E

## Fitur
- Beranda (profil pasien, statistik, pilihan formulir)
- Petunjuk dan persetujuan
- Pengisian bertahap dengan pertanyaan bercabang, validasi per kolom, dan simpan draf
- Periksa jawaban dan kirim
- Riwayat, hasil assessment, tracking status, klarifikasi, tindak lanjut
- Kuesioner GAD-7 (bonus)
- Status UTS (bukti fitur)

## Cara menjalankan
1. Pasang XAMPP (PHP 8+, ekstensi cURL aktif).
2. Letakkan folder project di `C:\xampp\htdocs\telehealth_uts`.
3. Salin `config.example.php` menjadi `config.php`, lalu isi token pasien dan identitas mahasiswa.
4. Start Apache di XAMPP, lalu buka `http://localhost/telehealth_uts/`.

## Catatan
Hasil self-assessment bukan diagnosis.