<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kebijakan Privasi — Asahi</title>
<style>
  body{font-family:system-ui,-apple-system,sans-serif;background:#FFF9F1;color:#2F3352;margin:0;line-height:1.65}
  main{max-width:720px;margin:0 auto;padding:28px 20px 60px}
  h1{color:#FF7A5C;margin:0 0 4px} h2{font-size:19px;margin-top:28px}
  .muted{color:#7C819B;font-size:14px} a{color:#FF7A5C}
  li{margin:4px 0}
</style>
</head>
<body>
<main>
  <p><a href="/">← Kembali ke Asahi</a></p>
  <h1>Kebijakan Privasi</h1>
  <p class="muted">Berlaku sejak {{ now()->translatedFormat('j F Y') }}. <!-- TODO: tinjau bersama penasihat hukum sebelum rilis publik --></p>

  <p>Asahi adalah aplikasi belajar untuk anak TK sampai kelas 5 SD yang dipakai bersama orang tua. Akun dibuat dan dikelola oleh orang tua atau wali; anak tidak membuat akun sendiri.</p>

  <h2>Data yang kami simpan</h2>
  <ul>
    <li><b>Dari akun Google orang tua:</b> nama, alamat email, dan foto profil. Kami tidak menerima kata sandi Google Anda.</li>
    <li><b>Profil anak yang Anda isi:</b> nama panggilan dan kelas. Kami tidak meminta nama lengkap, tanggal lahir, foto, atau sekolah anak.</li>
    <li><b>Hasil belajar:</b> topik yang dimainkan, jumlah benar/salah, bintang, skor, dan stempel dari orang tua (Buku Kenangan).</li>
    <li><b>Isi Ruang Orang Tua:</b> panggilan (mis. "Bunda"), pesan semangat, dan janji kecil.</li>
  </ul>

  <h2>Untuk apa data dipakai</h2>
  <ul>
    <li>Menampilkan progres dan Buku Kenangan anak di perangkat mana pun Anda masuk.</li>
    <li>Menyesuaikan sapaan dan pesan di dalam aplikasi.</li>
  </ul>
  <p>Kami <b>tidak</b> menjual data, <b>tidak</b> menampilkan iklan, dan <b>tidak</b> membagikan data anak kepada pihak ketiga, kecuali penyedia hosting yang menyimpan data atas nama kami.</p>

  <h2>Persetujuan orang tua</h2>
  <p>Sebelum profil anak dibuat, orang tua/wali menyatakan persetujuan atas penyimpanan data di atas, sesuai Undang-Undang No. 27 Tahun 2022 tentang Pelindungan Data Pribadi.</p>

  <h2>Menghapus data</h2>
  <ul>
    <li>Hapus profil anak beserta seluruh Buku Kenangannya kapan saja dari menu <i>Ruang Orang Tua → Kelola anak</i>.</li>
    <li>Untuk menghapus seluruh akun, hubungi kami di email di bawah. Kami memprosesnya paling lambat 14 hari.</li>
  </ul>

  <h2>Kontak</h2>
  <p>Email: <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a></p>
</main>
</body>
</html>
