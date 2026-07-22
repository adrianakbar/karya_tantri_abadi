# Panduan Operasional & Dokumen Fisik Uji Coba Lapangan (UAT)
**Koperasi Simpan Pinjam Karya Tantri Abadi**

Dokumen ini dipersiapkan sebagai panduan praktis dan dokumen fisik yang **wajib Anda bawa/cetak** untuk pelaksanaan uji coba (*testing*) langsung bersama pengurus, yayasan, dan anggota koperasi.

---

## Daftar Isi
1. [Skenario Panduan Uji Coba Sistem (Walkthrough Script)](#1-skenario-panduan-uji-coba-sistem-walkthrough-script)
2. [Lembar Kuesioner UAT Fisik (Siap Cetak/Print)](#2-lembar-kuesioner-uat-fisik-siap-cetakprint)
3. [Berita Acara Pelaksanaan Pengujian (Sign-Off Sheet)](#3-berita-acara-pelaksanaan-pengujian-sign-off-sheet)

---

## 1. Skenario Panduan Uji Coba Sistem (Walkthrough Script)
Gunakan panduan langkah-demi-langkah ini untuk menginstruksikan para responden (Admin, Bendahara, Anggota, Kepala Yayasan) saat mereka mencoba aplikasi secara langsung.

### A. Skenario untuk Admin (Administrator Sistem)
*Mintalah Admin untuk melakukan langkah berikut:*
1. **Login:** Buka halaman login, centang reCAPTCHA, masukkan email/password admin, klik Login.
2. **Manajemen Pengguna:** Masuk ke menu **Pengguna** atau **User Management**, coba tambahkan pengguna baru dengan peran *Bendahara* (isi data fiktif baru), lalu klik Simpan.
3. **Audit Trail (Log Aktivitas):** Buka menu **Audit Log** atau **Log Aktivitas**, periksa apakah aktivitas login dan pembuatan pengguna baru tadi tercatat lengkap dengan waktu dan alamat IP.
4. **Backup Database:** Masuk ke menu **Pengaturan/Sistem** -> **Backup**, klik tombol **Backup Database** untuk mengunduh berkas cadangan SQL sistem koperasi.

### B. Skenario untuk Bendahara (Petugas Operasional)
*Mintalah Bendahara untuk melakukan langkah berikut:*
1. **Login:** Buka halaman login, centang reCAPTCHA, masukkan email/password bendahara, klik Login.
2. **Registrasi Anggota Baru:** Masuk ke menu **Anggota**, klik **Tambah Anggota**, isi data fiktif baru, lalu klik Simpan.
3. **Transaksi Simpanan:**
    * Buka menu **Simpanan** $\rightarrow$ **Transaksi Simpanan**.
    * Klik **Buat Transaksi**, pilih Anggota fiktif tadi, pilih jenis **Simpanan Wajib**, isi nominal Rp50.000, lalu klik Simpan.
    * Klik tombol **Cetak Kuitansi (PDF)** untuk memastikan printer kuitansi dapat mencetak.
4. **Proses Pinjaman:**
    * Buka menu **Pinjaman** $\rightarrow$ **Pengajuan Pinjaman**.
    * Coba setujui pengajuan pinjaman anggota fiktif (jika ada pengajuan), klik **Cairkan Dana**.
    * Periksa apakah daftar angsuran bulanan otomatis ter-generate di tab angsuran.
5. **Transaksi Toko (POS):**
    * Buka menu **Toko/POS** $\rightarrow$ **Kasir**.
    * Pilih beberapa produk, tentukan jumlah barang.
    * Pilih Metode Pembayaran: coba transaksi menggunakan **Tunai**, dan coba transaksi kedua menggunakan **Kredit Anggota (Potong Saldo)**.
    * Klik Bayar dan pastikan struk belanja PDF dapat dirender.

### C. Skenario untuk Anggota Koperasi
*Mintalah perwakilan Anggota untuk melakukan langkah berikut:*
1. **Login:** Masuk menggunakan akun Anggota yang telah didaftarkan.
2. **Cek Saldo:** Buka halaman Dashboard Anggota, periksa apakah saldo simpanan pokok, wajib, dan sukarela yang tampil sudah sesuai.
3. **Pengajuan Pinjaman Mandiri:**
    * Buka menu **Pengajuan Pinjaman**.
    * Klik buat pengajuan, pilih Jenis Pinjaman, masukkan jumlah pinjaman dan tenor bulan, klik Simpan.
4. **Cek Riwayat Belanja:** Masuk ke menu **Riwayat Belanja Toko** untuk melihat struk dari barang-barang yang dibeli di toko koperasi.

### D. Skenario untuk Kepala Yayasan (Manajemen)
*Mintalah Kepala Yayasan untuk melihat laporan pengawasan:*
1. **Login:** Masuk menggunakan akun Kepala Yayasan.
2. **Melihat Laporan Keuangan:**
    * Buka menu **Laporan Finansial**.
    * Periksa grafik perkembangan kas, Laporan Arus Kas, dan Neraca Rugi Laba.
    * Coba lakukan ekspor Laporan Bulanan ke format **Excel (.xlsx)** atau **PDF**.

---

## 2. Lembar Kuesioner UAT Fisik (Siap Cetak/Print)
*Cetak halaman ini sejumlah responden yang akan menguji aplikasi untuk diisi secara manual.*

**Nama Responden :** ________________________________________  
**Peran (Role)   :** [ ] Admin / [ ] Bendahara / [ ] Anggota / [ ] Kepala Yayasan  
**Tanggal Uji    :** _______________________ 2026

**Petunjuk Pengisian:**  
Berikan tanda centang ($\checkmark$) pada kolom pilihan respon yang paling sesuai menurut penilaian Anda:
* **SS** : Sangat Setuju (Skor 4)
* **S**  : Setuju (Skor 3)
* **TS** : Tidak Setuju (Skor 2)
* **STS**: Sangat Tidak Setuju (Skor 1)

| No | Aspek Pernyataan Sistem Informasi Koperasi | STS (1) | TS (2) | S (3) | SS (4) |
| :-: | :--- | :---: | :---: | :---: | :---: |
| **A** | **Perceived Usefulness (Kemanfaatan)** | | | | |
| 1 | Aplikasi ini mempermudah proses pencatatan simpanan dan pinjaman anggota. | | | | |
| 2 | Fitur kasir retail toko (POS) mempercepat proses pencatatan belanja harian. | | | | |
| 3 | Perhitungan Sisa Hasil Usaha (SHU) menjadi lebih cepat, adil, dan transparan. | | | | |
| **B** | **Ease of Use (Kemudahan Penggunaan)** | | | | |
| 4 | Navigasi menu dan tombol-tombol pada aplikasi mudah dipahami. | | | | |
| 5 | Proses masuk (Login) dengan verifikasi reCAPTCHA berjalan lancar dan aman. | | | | |
| 6 | Pengunduhan laporan keuangan dalam bentuk PDF/Excel mudah dioperasikan. | | | | |
| **C** | **User Experience (Desain Antarmuka)** | | | | |
| 7 | Tampilan warna antarmuka aplikasi menarik dan teks mudah dibaca. | | | | |
| 8 | Informasi saldo simpanan dan pinjaman ter-update secara cepat (real-time). | | | | |
| **D** | **Satisfaction (Kepuasan Pengguna)** | | | | |
| 9 | Selama pengujian, aplikasi berjalan stabil tanpa adanya kendala/error fatal. | | | | |
| 10 | Secara keseluruhan, saya puas dengan kinerja sistem informasi koperasi ini. | | | | |

*Terima kasih atas partisipasi Anda dalam menyukseskan penelitian ini.*

---

## 3. Berita Acara Pelaksanaan Pengujian (Sign-Off Sheet)
*Dokumen ini merupakan bukti hukum formal bahwa sistem telah diuji coba secara langsung di koperasi objek penelitian skripsi.*

### BERITA ACARA UJI COBA LAPANGAN (UAT)
**PENGEMBANGAN SISTEM INFORMASI KARYA TANTRI ABADI**

Pada hari ini, ........................ Tanggal ...... Bulan ................... Tahun Dua Ribu Dua Puluh Enam, bertempat di Kantor Karya Tantri Abadi, telah dilaksanakan kegiatan Uji Coba Lapangan (*User Acceptance Testing*) terhadap Aplikasi Karya Tantri Abadi Simpan Pinjam dan POS Retail Berbasis Website.

Pengujian dilakukan oleh pihak-pihak di bawah ini sebagai perwakilan pengguna sistem:

1. **Nama :** ..................................................... (Perwakilan Admin Sistem)
2. **Nama :** ..................................................... (Perwakilan Pengurus / Bendahara)
3. **Nama :** ..................................................... (Perwakilan Anggota Koperasi)
4. **Nama :** ..................................................... (Perwakilan Yayasan / Kepala Yayasan)

Dengan hasil pengujian sebagai berikut:
* Fungsionalitas Sistem (Simpanan, Pinjaman, POS, SHU, Laporan, Keamanan): **[ ] Berfungsi Penuh / [ ] Cukup Berfungsi / [ ] Perlu Perbaikan**
* Kelayakan Antarmuka dan Keamanan Data: **[ ] Sangat Layak / [ ] Layak / [ ] Kurang Layak**

Catatan / Masukan Tambahan:
......................................................................................................................................................
......................................................................................................................................................
......................................................................................................................................................

Demikian Berita Acara ini dibuat dengan sebenar-benarnya untuk digunakan sebagai kelengkapan Laporan Skripsi/Tugas Akhir.

**Pihak Penguji (Responden),**

1. Admin: ( ............................................ )  
2. Bendahara: ( ............................................ )  
3. Anggota: ( ............................................ )  
4. Kepala Yayasan: ( ............................................ )  

**Mengetahui,**  
**Peneliti (Mahasiswa),**  


**Adrian Akbar Ramadhani**  
NIM 222410102010  
Program Studi Teknologi Informasi  
Fakultas Ilmu Komputer, Universitas Jember  
