# Panduan Operasional & Dokumen Fisik Uji Coba Lapangan (UAT)
**Koperasi Simpan Pinjam Karya Tantri Abadi**

Dokumen ini dipersiapkan sebagai panduan praktis dan lembar fisik yang dibawa saat uji coba bersama pengelola dan anggota.

---

## Daftar Isi
1. Skenario walkthrough sistem
2. Lembar kuesioner UAT (siap cetak)
3. Berita acara pengujian

---

## 1. Skenario Panduan Uji Coba Sistem

Responden online: **Admin, SPV, Kasir, Anggota**.  
Petugas lapangan: **offline** (tidak login); perannya disimulasikan dengan menyerahkan data/uang ke admin.

### A. Admin
1. Login di `/auth/login` → masuk panel `/admin`.
2. Kelola/cek data anggota (user anggota).
3. Input pinjaman kelompok (pending): nominal, tenor, frekuensi.
4. Pastikan fee tampil sesuai tier (angsuran 11%, admin 5%; UTJ 22% & cair 73% jika ≤2,5jt; UTJ 11% & cair 84% jika ≥2,6jt).
5. Setelah SPV approve & kasir cairkan: buka detail pinjaman → **Catat Bayar** cicilan (simulasikan uang dari petugas).
6. Cek laporan pinjaman/tabungan/keuangan + backup (opsional).

### B. SPV
1. Login → `/spv`.
2. Buka daftar pinjaman pending.
3. **Setujui** satu pinjaman (isi catatan opsional).
4. **Tolak** satu pinjaman uji (jika disiapkan).
5. Buka laporan pinjaman/keuangan (read monitoring).

### C. Kasir
1. Login → `/kasir`.
2. Catat **Tabungan** anggota (nominal valid) + cetak kuitansi bila ada.
3. Buka pinjaman berstatus approved → **Cairkan**.
4. Pastikan jadwal cicilan muncul; kasir **tidak** menekan Catat Bayar.
5. Buka laporan tabungan/pinjaman/keuangan.

### D. Anggota
1. Login → `/anggota`.
2. Buka daftar pinjaman: hanya milik sendiri.
3. Lihat cair bersih, angsuran, sisa hutang, status.
4. Pastikan tidak bisa membuat/edit pinjaman atau mengelola tabungan.

### E. Simulasi petugas offline (bukan login)
1. Peneliti/mitra menjelaskan: petugas mencari nasabah & menarik cicilan di lapangan.
2. Data pengajuan diserahkan ke admin untuk diinput.
3. Uang cicilan diserahkan ke admin untuk dicatat di sistem.

---

## 2. Lembar Kuesioner UAT (Siap Cetak)

**Nama Responden :** ________________________________________  
**Peran (Role)   :** [ ] Admin  [ ] SPV  [ ] Kasir  [ ] Anggota  
**Tanggal Uji    :** _______________________ 2026

**Petunjuk:** centang jawaban yang sesuai.  
SS=4, S=3, TS=2, STS=1

| No | Aspek Pernyataan | STS | TS | S | SS |
| :-: | :--- | :-: | :-: | :-: | :-: |
| **A** | **Perceived Usefulness** | | | | |
| 1 | Aplikasi mempermudah pencatatan tabungan dan pinjaman. | | | | |
| 2 | Alur pinjaman admin → SPV → kasir sesuai kebutuhan. | | | | |
| 3 | Pencatatan cicilan oleh admin memudahkan rekap setoran petugas. | | | | |
| 4 | Anggota dapat memantau pinjaman sendiri dengan jelas. | | | | |
| **B** | **Ease of Use** | | | | |
| 5 | Navigasi menu dan tombol mudah dipahami. | | | | |
| 6 | Proses login berjalan lancar dan aman. | | | | |
| 7 | Akses/unduh laporan mudah dilakukan. | | | | |
| **C** | **User Experience** | | | | |
| 8 | Tampilan antarmuka nyaman dan teks mudah dibaca. | | | | |
| **D** | **Satisfaction** | | | | |
| 9 | Aplikasi stabil tanpa error fatal selama uji coba. | | | | |
| 10 | Secara keseluruhan saya puas dengan sistem ini. | | | | |

*Terima kasih atas partisipasi Anda.*

---

## 3. Berita Acara Pelaksanaan Pengujian

### BERITA ACARA UJI COBA LAPANGAN (UAT)
**PENGEMBANGAN SISTEM INFORMASI KARYA TANTRI ABADI**

Pada hari ini, ........................ Tanggal ...... Bulan ................... Tahun Dua Ribu Dua Puluh Enam, bertempat di Kantor Karya Tantri Abadi, telah dilaksanakan Uji Coba Lapangan (*User Acceptance Testing*) terhadap Aplikasi Karya Tantri Abadi (Koperasi Simpan Pinjam Berbasis Website).

Pengujian dilakukan oleh:

1. **Nama :** ..................................................... (Admin)
2. **Nama :** ..................................................... (SPV)
3. **Nama :** ..................................................... (Kasir)
4. **Nama :** ..................................................... (Anggota)

Hasil pengujian:
* Fungsionalitas inti (tabungan, pinjaman, cicilan, laporan, login multi-panel): **[ ] Berfungsi Penuh / [ ] Cukup / [ ] Perlu Perbaikan**
* Kelayakan antarmuka & keamanan dasar: **[ ] Sangat Layak / [ ] Layak / [ ] Kurang Layak**

Catatan / masukan:
......................................................................................................................................................
......................................................................................................................................................

Demikian berita acara ini dibuat untuk kelengkapan laporan skripsi.

**Pihak Penguji,**

1. Admin: ( ............................................ )  
2. SPV: ( ............................................ )  
3. Kasir: ( ............................................ )  
4. Anggota: ( ............................................ )  

**Mengetahui,**  
**Peneliti,**

**Adrian Akbar Ramadhani**  
NIM 222410102010  
Program Studi Teknologi Informasi  
Fakultas Ilmu Komputer, Universitas Jember  
