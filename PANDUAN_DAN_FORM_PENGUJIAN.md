# Panduan Operasional & Dokumen Fisik Uji Coba Lapangan (UAT)
**Karya Tantri Abadi — Koperasi Simpan Pinjam Berbasis Website**

Dokumen ini dipersiapkan sebagai panduan praktis dan lembar fisik yang dibawa saat uji coba bersama pengelola dan ketua kelompok.

Sesuai sistem aktif:
- Login `/auth/login` (email + password, **tanpa CAPTCHA**)
- Role online: Admin, SPV, Kasir, Anggota (ketua kelompok)
- Petugas lapangan: **offline**
- Label UI: **Tabungan** (formal naskah: simpanan)
- Scope: simpan pinjam saja (POS/SHU nonaktif)

---

## Daftar Isi
1. Skenario walkthrough sistem
2. Lembar kuesioner UAT (siap cetak)
3. Berita acara pengujian

---

## 1. Skenario Panduan Uji Coba Sistem

Responden online: **Admin, SPV, Kasir, Anggota (Ketua Kelompok)**.  
Petugas lapangan: **offline** (tidak login); perannya disimulasikan dengan menyerahkan data/uang ke admin.

### A. Admin (`/admin`)
1. Login di `/auth/login` → masuk panel `/admin`.
2. Kelola/cek data anggota (user anggota / ketua kelompok).
3. Input pinjaman kelompok (pending): nominal, tenor, frekuensi weekly/monthly.
4. Pastikan fee tampil sesuai tier:
   - angsuran 11%, admin 5%
   - ≤ Rp2.500.000 → UTJ 22%, cair 73% (contoh 1jt → cair 730rb)
   - ≥ Rp2.600.000 → UTJ 11%, cair 84% (contoh 2,6jt → cair 2.184jt)
5. Setelah SPV approve & kasir cairkan: buka detail pinjaman → **Catat Bayar** cicilan (simulasikan uang dari petugas).
6. Cek laporan pinjaman / tabungan / arus kas + backup (opsional).

### B. SPV (`/spv`)
1. Login → `/spv`.
2. Buka daftar pinjaman pending.
3. **Setujui** satu pinjaman (catatan opsional).
4. **Tolak** satu pinjaman uji (jika disiapkan).
5. Buka laporan pinjaman / keuangan (monitoring).

### C. Kasir (`/kasir`)
1. Login → `/kasir`.
2. Catat **Tabungan** anggota (nominal valid) + cetak kuitansi bila ada.
3. Buka pinjaman berstatus approved → **Cairkan** (cek tampilan cair bersih).
4. Pastikan jadwal cicilan muncul setelah cair; kasir **tidak** menekan Catat Bayar.
5. Buka laporan tabungan / pinjaman / keuangan.

### D. Anggota — Ketua Kelompok (`/anggota`)
1. Login → `/anggota` (akun dipegang **ketua kelompok**).
2. Buka daftar pinjaman: hanya milik kelompok yang diwakilinya (`user_id` sendiri).
3. Lihat cair bersih, angsuran, sisa hutang, status.
4. Pastikan tidak bisa membuat/edit pinjaman dan tidak mengelola tabungan di panel ini.

### E. Simulasi petugas offline (bukan login)
1. Peneliti/mitra menjelaskan: petugas mencari/mendampingi nasabah & menarik cicilan di lapangan.
2. Data pengajuan diserahkan ke admin untuk diinput.
3. Uang cicilan diserahkan ke admin untuk dicatat di sistem.
4. Tidak ada akun/panel `/petugas`.

### F. Akun demo (seed)
Password default: `password`

| Role | Email contoh | Panel |
|---|---|---|
| Admin | `admin@karya-tantri-abadi.test` | `/admin` |
| SPV | `spv@karya-tantri-abadi.test` | `/spv` |
| Kasir | `kasir@karya-tantri-abadi.test` | `/kasir` |
| Anggota (ketua) | `anggota@karya-tantri-abadi.test` | `/anggota` |

Alternatif: `*@test.com`.

---

## 2. Lembar Kuesioner UAT (Siap Cetak)

**Nama Responden :** ________________________________________  
**Peran (Role)   :** [ ] Admin  [ ] SPV  [ ] Kasir  [ ] Anggota (Ketua Kelompok)  
**Tanggal Uji    :** _______________________ 2026

**Petunjuk:** centang jawaban yang sesuai setelah mencoba fitur sesuai peran.  
SS=4, S=3, TS=2, STS=1

| No | Aspek Pernyataan | STS | TS | S | SS |
| :-: | :--- | :-: | :-: | :-: | :-: |
| **A** | **Perceived Usefulness** | | | | |
| 1 | Aplikasi mempermudah pencatatan tabungan dan pinjaman kelompok. | | | | |
| 2 | Alur pinjaman admin input → SPV setujui/tolak → kasir cairkan sesuai kebutuhan operasional. | | | | |
| 3 | Pencatatan cicilan oleh admin memudahkan rekap setelah petugas lapangan menyetor uang. | | | | |
| 4 | Ketua kelompok (akun anggota) dapat memantau pinjaman kelompok sendiri dengan jelas (cair bersih, angsuran, sisa, status). | | | | |
| **B** | **Ease of Use** | | | | |
| 5 | Navigasi menu dan tombol sesuai peran mudah dipahami. | | | | |
| 6 | Proses login berjalan lancar dan aman (email + password). | | | | |
| 7 | Informasi penting sesuai peran (pinjaman / tabungan / laporan) mudah diakses. | | | | |
| **C** | **User Experience** | | | | |
| 8 | Tampilan antarmuka nyaman dan teks mudah dibaca. | | | | |
| **D** | **Satisfaction** | | | | |
| 9 | Aplikasi stabil tanpa error fatal selama uji coba. | | | | |
| 10 | Secara keseluruhan saya puas dengan sistem ini. | | | | |

*Terima kasih atas partisipasi Anda.*

**Rekap peneliti (jangan fabrikasi):**  
Total skor aktual (10–40) = ______  
Persentase kelayakan global = (total skor aktual semua responden / (N × 10 × 4)) × 100% = ______ %

Print: **1 orang = 1 form**. Minimal 4 lembar; ideal 6–10. Form Word utama: `FORM_UAT_KARYA_TANTRI_ABADI.docx`.

---

## 3. Berita Acara Pelaksanaan Pengujian

### BERITA ACARA UJI COBA LAPANGAN (UAT)
**PENGEMBANGAN SISTEM INFORMASI KARYA TANTRI ABADI**

Pada hari ini, ........................ Tanggal ...... Bulan ................... Tahun Dua Ribu Dua Puluh Enam, bertempat di Kantor Karya Tantri Abadi, telah dilaksanakan Uji Coba Lapangan (*User Acceptance Testing*) terhadap Aplikasi Karya Tantri Abadi (Koperasi Simpan Pinjam Berbasis Website).

Pengujian dilakukan oleh:

1. **Nama :** ..................................................... (Admin)
2. **Nama :** ..................................................... (SPV)
3. **Nama :** ..................................................... (Kasir)
4. **Nama :** ..................................................... (Anggota / Ketua Kelompok)

Hasil pengujian:
* Fungsionalitas inti (tabungan, pinjaman kelompok, cicilan, laporan, login multi-panel): **[ ] Berfungsi Penuh / [ ] Cukup / [ ] Perlu Perbaikan**
* Kelayakan antarmuka & keamanan dasar: **[ ] Sangat Layak / [ ] Layak / [ ] Kurang Layak**
* Jumlah responden UAT: ______ orang
* Persentase kelayakan (dari kuesioner): ______ %

Catatan / masukan:
......................................................................................................................................................
......................................................................................................................................................

Demikian berita acara ini dibuat untuk kelengkapan laporan skripsi.

**Pihak Penguji,**

1. Admin: ( ............................................ )  
2. SPV: ( ............................................ )  
3. Kasir: ( ............................................ )  
4. Anggota (Ketua Kelompok): ( ............................................ )  

**Mengetahui,**  
**Peneliti,**

**Adrian Akbar Ramadhani**  
NIM 222410102010  
Program Studi Teknologi Informasi  
Fakultas Ilmu Komputer, Universitas Jember  
