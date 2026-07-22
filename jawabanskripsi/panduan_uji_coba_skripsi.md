# Panduan Uji Coba Lapangan & UAT Koperasi Karya Tantri Abadi
Dokumen ini disusun sebagai panduan praktis pelaksanaan pengujian lapangan (*Field Testing*) dan pengambilan data *User Acceptance Testing* (UAT) dalam rangka menyelesaikan penelitian skripsi di **Koperasi Karya Tantri Abadi**.

---

## 1. Skenario Pengujian Berdasarkan Peran (Aktor)

Akses aplikasi melalui peramban (browser) di smartphone atau laptop responden menggunakan alamat IP server pengujian:  
**URL Uji Coba**: `http://100.84.38.75:8000`

### A. Anggota Koperasi (Uji Mandiri Anggota)
*   **Tujuan**: Memastikan anggota dapat memantau simpanan secara transparan dan melakukan pengajuan pinjaman secara mandiri.
*   **Skenario Pengujian**:
    1.  Minta anggota login menggunakan akun uji coba (Contoh: `anggota@test.com` / `password`).
    2.  Arahkan anggota untuk memeriksa halaman **Dashboard** untuk melihat saldo Simpanan Pokok, Wajib, dan Sukarela.
    3.  Minta anggota melakukan simulasi **Pengajuan Pinjaman Mandiri**:
        *   Masuk ke menu pengajuan pinjaman.
        *   Input nominal pinjaman sebesar **Rp 1.000.000** dengan tenor **10 bulan**.
        *   Kirim formulir pengajuan.
    4.  Periksa riwayat pengajuan di bawah form untuk memastikan status pengajuan tercatat sebagai *Pending*.

### B. Bendahara (Uji Operasional Keuangan)
*   **Tujuan**: Memastikan bendahara dapat mencatat simpanan, mengelola pengajuan pinjaman, dan melayani belanja kasir toko secara efisien.
*   **Skenario Pengujian**:
    1.  Login sebagai Bendahara (Contoh: `bendahara@test.com` / `password`).
    2.  Lakukan pencatatan **Setoran Simpanan**:
        *   Pilih nama Anggota yang melakukan pengujian tadi.
        *   Input setoran Simpanan Wajib bulanan.
    3.  Lakukan **Persetujuan & Pencairan Pinjaman**:
        *   Masuk ke menu pengajuan pinjaman masuk.
        *   Cari pengajuan Rp 1.000.000 dari Anggota tadi.
        *   Ubah status menjadi disetujui, isi tanggal pencairan, dan klik simpan.
    4.  Simulasikan **Pencatatan Angsuran**:
        *   Input pembayaran angsuran pertama dari anggota tersebut.
    5.  Simulasikan transaksi **Point of Sales (POS)**:
        *   Lakukan transaksi kasir belanja toko untuk anggota, selesaikan pembayaran, dan pastikan kuitansi/struk PDF tercetak dengan benar.

### C. Kepala Yayasan (Uji Pengawasan Eksekutif)
*   **Tujuan**: Memastikan Kepala Yayasan mendapatkan visualisasi data yang akurat dan laporan keuangan yang akuntabel.
*   **Skenario Pengujian**:
    1.  Login sebagai Kepala Yayasan / Pengurus Eksekutif.
    2.  Periksa **Dashboard Laporan**:
        *   Lihat grafik tren sisa hasil usaha (SHU), cash flow kas masuk/keluar, dan rasio simpanan vs pinjaman.
    3.  Lakukan **Ekspor Laporan**:
        *   Unduh laporan simpanan bulanan dalam format **PDF**.
        *   Unduh rekapitulasi data anggota dalam format **Excel**.
    4.  Verifikasi apakah berkas laporan keuangan yang diunduh sudah rapi dan siap dilaporkan ke rapat yayasan.

### D. Admin (Uji Keamanan & Administrasi Sistem)
*   **Tujuan**: Memastikan kelancaran manajemen pengguna, audit keamanan, dan pemulihan bencana (*disaster recovery*).
*   **Skenario Pengujian**:
    1.  Login sebagai Admin (Contoh: `admin@test.com` / `password`).
    2.  Lakukan **Manajemen Akses**:
        *   Buat satu user baru dengan role *Bendahara*.
        *   Uji coba masuk menggunakan akun baru tersebut dan pastikan menu konfigurasi admin terproteksi/tidak dapat dibuka.
    3.  Periksa **Audit Trail (Log Aktivitas)**:
        *   Pastikan seluruh aksi perubahan data dan pencatatan transaksi oleh Bendahara tadi terekam dengan jelas (siapa mengubah apa, kapan, dan dari IP mana).
    4.  Lakukan **Backup Database**:
        *   Picu proses backup database koperasi, unduh file SQL backup, dan simpan sebagai cadangan.

---

## 2. Lembar Uji Fungsional (Black Box Testing Checklist)

*Cetak tabel ini untuk ditandatangani oleh perwakilan penguji/yayasan sebagai bukti validasi teknis.*

| No | Modul / Fitur | Skenario Uji (Input) | Hasil yang Diharapkan | Hasil Aktual | Status (Berhasil/Gagal) |
|---|---|---|---|---|---|
| 1 | Login Pengguna | Input user & password terdaftar | Berhasil masuk ke panel yang sesuai role | | |
| 2 | Pengajuan Pinjaman | Anggota menginput nominal & tenor | Data terkirim dan tersimpan di database | | |
| 3 | Approval Pinjaman | Bendahara menyetujui pengajuan | Status berubah menjadi aktif, saldo kasir terpotong | | |
| 4 | Pencatatan Angsuran | Bendahara menginput angsuran | Saldo pinjaman anggota berkurang, kas masuk bertambah | | |
| 5 | Transaksi Kasir POS | Selesaikan belanja & cetak struk | Struk kuitansi PDF berhasil diunduh | | |
| 6 | Visualisasi Laporan | Buka halaman financial report | Grafik laporan cash flow ter-render sempurna | | |
| 7 | Backup Database | Klik tombol backup sistem | File SQL cadangan berhasil diunduh | | |

---

## 3. Kuesioner User Acceptance Testing (UAT)

*Bagikan kuesioner ini kepada ke-4 aktor di atas setelah mencoba aplikasi. Berikan rentang skor **1 sampai 4** (skala Likert tanpa nilai tengah untuk menghindari bias netral).*

### Pilihan Jawaban:
*   **4** = Sangat Setuju (SS)
*   **3** = Setuju (S)
*   **2** = Kurang Setuju (KS)
*   **1** = Sangat Tidak Setuju (STS)

### Daftar Pertanyaan Kuesioner:

#### A. Aspek Kemudahan Penggunaan (*Ease of Use*)
1.  Antarmuka aplikasi (tata letak, tombol, dan warna) mudah dipahami oleh pemula. `[ ]`
2.  Proses login hingga transaksi kasir dapat dilakukan tanpa memerlukan petunjuk yang rumit. `[ ]`
3.  Pesan kesalahan (jika salah input data) membantu saya memperbaiki kesalahan pengisian dengan mudah. `[ ]`

#### B. Aspek Manfaat & Keefektifan (*Perceived Usefulness*)
4.  Sistem ini mempercepat pencatatan data simpanan dan pinjaman dibanding cara manual. `[ ]`
5.  Visualisasi grafik dashboard membantu pengawasan sisa hasil usaha (SHU) secara cepat. `[ ]`
6.  Fitur ekspor PDF/Excel mempermudah pelaporan bulanan ke yayasan secara signifikan. `[ ]`

#### C. Aspek Desain & Tampilan (*Interface Design*)
7.  Ikon dan tulisan pada ringkasan dashboard terlihat jelas dan profesional. `[ ]`
8.  Tampilan web koperasi rapi saat diakses baik dari laptop maupun smartphone. `[ ]`

#### D. Aspek Kepuasan Keseluruhan (*Satisfaction*)
9.  Saya merasa puas dengan kecepatan respons sistem saat berpindah halaman. `[ ]`
10. Saya setuju jika sistem ini diimplementasikan secara penuh di Koperasi Karya Tantri Abadi. `[ ]`

---

## 4. Lembar Catatan Masukan & Revisi R&D
*Gunakan lembar ini untuk mencatat umpan balik kualitatif apabila ada keluhan atau ide pengembangan lanjutan dari pengguna lapangan.*

```
Tanggal Kunjungan : ...................................
Nama Responden     : ...................................
Peran / Jabatan    : [ ] Admin   [ ] Bendahara   [ ] Anggota   [ ] Kepala Yayasan

Catatan Masukan / Masalah yang Ditemukan:
.................................................................................................
.................................................................................................
.................................................................................................
.................................................................................................

Rencana Solusi / Revisi Program:
.................................................................................................
.................................................................................................
.................................................................................................
.................................................................................................
```
